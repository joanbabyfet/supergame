<?php


namespace App\services;

use App\Jobs\job_send_mail;
use App\Models\mod_user;
use App\repositories\repo_model_has_roles;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

/**
 * 系统邮件
 * Class serv_sys_mail
 * @package App\services
 */
class serv_sys_mail
{
    use trait_service_base;

    //邮件发送对象
    const OBJECT_TYPE_ALL       = 1;
    const OBJECT_TYPE_PERSONAL  = 2;
    const OBJECT_TYPE_LEVEL     = 3;
    const OBJECT_TYPE_REG_TIME  = 4;
    public static $object_type  = [
        1   =>  '所有用户',
        2   =>  '个人',
        3   =>  '会员等级',
        4   =>  '注册时间'
    ];

    private $serv_array;
    private $repo_model_has_roles;
    private $repo_user;
    private $serv_send_mail;

    public function __construct(
        serv_array $serv_array,
        repo_model_has_roles $repo_model_has_roles,
        repo_user $repo_user,
        serv_send_mail $serv_send_mail
    )
    {
        $this->serv_array               = $serv_array;
        $this->repo_model_has_roles     = $repo_model_has_roles;
        $this->repo_user                = $repo_user;
        $this->serv_send_mail           = $serv_send_mail;
    }

    /**
     * 发送邮件 (依据发送对象)
     * @param array $data
     * @return int|mixed
     */
    public function send(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'object_type'   => 'required',
            'object_ids'    => '',
            'subject'       => 'required',
            'view'          => 'required',
            'view_data'     => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $view       = $data_filter['view'];
            $subject    = $data_filter['subject'];
            $view_data  = empty($data_filter['view_data']) ? [] : $data_filter['view_data'];

            $where = [];
            $where[] = ['status', '=',  1]; //启用
            switch ($data_filter['object_type'])
            {
                case self::OBJECT_TYPE_ALL:
                    break;
                case self::OBJECT_TYPE_PERSONAL:
                    $uids = explode(',', $data_filter['object_ids']);
                    $uids = empty($uids) || empty($data_filter['object_ids']) ?
                        [-1] : $uids;
                    //用户id
                    $where[] = ['id', 'in', $uids];
                    break;
                case self::OBJECT_TYPE_LEVEL:
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
                case self::OBJECT_TYPE_REG_TIME: //日期格式 2022/06/13,2022/06/22
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

            $page_no = 1;
            do
            {
                //获取收件人信箱,姓名
                $rows = $this->repo_user->lists([
                    'fields'    => [
                        'id', 'username', 'email', DB::raw('`realname` As name'),
                    ],
                    'page'      =>  $page_no,
                    'page_size' =>  500,
                    'where'     =>  $where,
                ])->toArray();
                if (empty($rows))
                {
                    break;
                }

                $to = [];
                foreach($rows as $k => $v)
                {
                    //收件人
                    $to_plus = [
                        'name'      => $v['name'],
                        'email'     => $v['email'],
                    ];
                    $to[$v['id']] = $to_plus;
                }

                //推送任务到队列中发送
                $params = [
                    'to'        => $to, //多收件人
                    'subject'   => $subject,
                    'view'      => $view,
                    'view_data' => $view_data,
                ];
                $job = new job_send_mail($params);
                dispatch($job);

                $page_no++;
            }
            while (!empty($rows));

            if (empty($rows) && $page_no === 1)
            {
                $this->exception('发送对象不存在', -3);
            }
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
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
     * 发送邮件 (队列调用这里)
     * @param array $data
     * @return bool
     */
    public function _send_mail(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'to'            => 'required',
            'subject'       => 'required',
            'view'          => 'required',
            'view_data'     => '',
        ], $data);

        $status = 1;
        try
        {
            //发送邮件
            $this->serv_send_mail->send_mail($data_filter['to'],
                $data_filter['subject'], $data_filter['view'], $data_filter['view_data']);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
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
