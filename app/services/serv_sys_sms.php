<?php


namespace App\services;


use App\Jobs\job_send_sms;
use App\Models\mod_sys_sms;
use App\Models\mod_user;
use App\repositories\repo_model_has_roles;
use App\repositories\repo_sys_sms;
use App\repositories\repo_sys_sms_log;
use App\repositories\repo_user;
use App\services\sms\cxt_sms;
use App\services\sms\fty_sms;
use App\services\sms\Strategy\st_messagebird;
use App\services\sms\Strategy\st_smsmkt;
use App\traits\trait_service_base;

/**
 * 短信公共方法
 * Class serv_sys_sms
 * @package App\services
 */
class serv_sys_sms
{
    use trait_service_base;

    private $serv_array;
    private $repo_model_has_roles;
    private $repo_user;
    private $repo_sys_sms;
    private $repo_sys_sms_log;
    private $serv_send_sms;

    public function __construct(
        serv_array $serv_array,
        repo_model_has_roles $repo_model_has_roles,
        repo_user $repo_user,
        repo_sys_sms $repo_sys_sms,
        repo_sys_sms_log $repo_sys_sms_log,
        serv_send_sms $serv_send_sms
    )
    {
        $this->serv_array               = $serv_array;
        $this->repo_model_has_roles     = $repo_model_has_roles;
        $this->repo_user                = $repo_user;
        $this->repo_sys_sms             = $repo_sys_sms;
        $this->repo_sys_sms_log         = $repo_sys_sms_log;
        $this->serv_send_sms            = $serv_send_sms;
    }

    /**
     * 發送短信 (依据发送对象)
     * @param array $data
     * @return int|mixed
     */
    public function send(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'object_type'   => 'required',
            'object_ids'    => '',
            'name'          => 'required', //短信名称
            'content'       => 'required',
            'content_en'    => 'required',
            'send_uid'      => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $data_filter['object_ids'] = empty($data_filter['object_ids']) ? '' : $data_filter['object_ids'];
            $content        = $data_filter['content'];
            $content_en     = $data_filter['content_en'];
            $send_uid       = $data_filter['send_uid'] ?? '';

            $where = [];
            $where[] = ['status', '=',  1]; //启用
            switch ($data_filter['object_type'])
            {
                case mod_sys_sms::OBJECT_TYPE_ALL:
                    break;
                case mod_sys_sms::OBJECT_TYPE_PERSONAL:
                    $uids = explode(',', $data_filter['object_ids']);
                    $uids = empty($uids) || empty($data_filter['object_ids']) ?
                        [-1] : $uids;
                    //用户id
                    $where[] = ['id', 'in', $uids];
                    break;
                case mod_sys_sms::OBJECT_TYPE_LEVEL:
                    $level_ids = explode(',', $data_filter['object_ids']);
                    $level_ids = empty($level_ids) || empty($data_filter['object_ids']) ?
                        [] : $data_filter['object_ids'];

                    //获取该用户组有哪些用户
                    $uids = [];
                    if(!empty($level_ids))
                    {
                        $users = $this->repo_model_has_roles->get_list([
                            'role_id'       => $level_ids,
                            'model_type'    => get_class(new mod_user())
                        ]);
                        $uids = $this->serv_array->sql_in($users, 'model_id');
                    }
                    $uids = empty($uids) ? [-1] : $uids;
                    //会员等级id
                    $where[] = ['id', 'in', $uids];
                    break;
                case mod_sys_sms::OBJECT_TYPE_REG_TIME:
                    if (!empty($data_filter['object_ids']) && strpos($data_filter['object_ids'], ',') !== false)
                    {
                        list($start_date, $end_date) = explode(',', $data_filter['object_ids']);
                        $start_time = empty($start_date) ? '' : date_convert_timestamp("{$start_date} 00:00:00", get_admin_timezone());
                        $end_time   = empty($end_date) ? '' : date_convert_timestamp("{$end_date} 23:59:59", get_admin_timezone());
                    }
                    if (!empty($start_time) && !empty($end_time) && $start_time < $end_time)
                    {
                        $where[] = ['create_time', '>=', $start_time];
                        $where[] = ['create_time', '<=', $end_time];
                    }
                    break;
                default:
                    $this->exception('发送对象類型错误', -2);
            }

            $add_data = array_merge($data_filter, ['send_time' => time()]);
            //写入短信营销表
            $send_id = $this->repo_sys_sms->save($add_data);
            if (empty($send_id))
            {
                $this->exception('插入发送记录失败', -3);
            }

            $page_no = 1;
            do
            {
                //获取用户id,手机号,设置语言
                $send_users = $this->repo_user->lists([
                    'fields'    => [
                        'id', 'phone_code', 'phone', 'language'
                    ],
                    'page'      =>  $page_no,
                    'page_size' =>  500,
                    'where'     =>  $where,
                ])->toArray();
                if (empty($send_users))
                {
                    break;
                }

                //推送任务到队列中发送
                $params = [
                    'content'       => $content,
                    'content_en'    => $content_en,
                    'send_users'    => $send_users,
                    'send_uid'      => $send_uid,
                ];
                $job = new job_send_sms($params);
                dispatch($job);

                $page_no++;
            }
            while (!empty($rows));

            if (empty($rows) && $page_no === 1)
            {
                $this->exception('发送对象不存在', -4);
            }
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }

        return $status;
    }

    /**
     * 發送短信 (队列调用这里)
     * @param array $data
     * @return bool
     */
    public function _send_sms(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'content'       => 'required',
            'content_en'    => 'required',
            'send_users'    => 'required',
            'send_uid'      => '',
        ], $data);

        $status = 1;
        try
        {
            $send_users             = $data_filter['send_users'];
            $log_data = [];
            foreach($send_users as $send_user)
            {
                if (empty($send_user['phone'])) continue; //该用户没手机号则不发送

                $lang = in_array($send_user['language'], ['zh-tw', 'en']) ? $send_user['language'] : 'zh-tw';
                $content_field = get_lang_field('content', $lang);
                $app_name  = config('app.name');
                $msg = "【{$app_name}】{$data_filter[$content_field]}";
                //手机号
                $send_user['phone'] = str_replace(' ', '', $send_user['phone']);
                $send_user['phone'] = $send_user['phone_code'].trim($send_user['phone']);

                //发送短信
                //$status = $this->serv_send_sms->send_msg($send_user['phone'], $msg);
                //通过环境类
                //$cxt_sms = new cxt_sms(new st_messagebird());
                //$status = $cxt_sms->send_msg($send_user['phone'], $msg);
                //通过工厂类(统一管理)
                $sms_strategy = fty_sms::strategy('messagebird');
                $status = (new cxt_sms($sms_strategy))->send_msg($send_user['phone'], $msg);

                $log_data[] = [
                    'uid'       => $send_user['id'], //接收人id
                    'phone'     => $send_user['phone'],//接收人手机号
                    'content'   => $msg,
                    'send_uid'  => $data_filter['send_uid'], //发送人id
                    'send_time' => time(), //发送时间
                    'status'    => (int)$status //发送状态 1=成功 0=失败
                ];
                //避免太過頻繁的查詢
                usleep(10000);  //让进程挂起一段时间,避免cpu跑100%(单位微秒 1秒=1000000)
            }

            if (!empty($log_data)) //批量写入短信营销日志
            {
                $this->repo_sys_sms_log->save($log_data);
            }
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'args'    => func_get_args()
            ]);
        }
        return $status;
    }
}
