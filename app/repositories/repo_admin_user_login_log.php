<?php


namespace App\repositories;


use App\Models\mod_admin_user_login_log;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\Session;

class repo_admin_user_login_log
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_admin_user_login_log $mod_admin_user_login_log)
    {
        $this->model = $mod_admin_user_login_log;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['login_time', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $username   = !empty($conds['username']) ? $conds['username']:'';
        $date_start   = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';

        $where = []; //筛选
        $username and $where[] = ['username', 'like', "%{$username}%"];
        $date_start and $where[] = ['login_time', '>=', (int)$date_start]; //开始时间
        $date_end and $where[] = ['login_time', '<=', (int)$date_end]; //结束时间

        $rows = $this->lists([
            'fields'    => $conds['fields'] ?? null,
            'where'     => $where,
            'page'      => $conds['page'] ?? null,
            'page_size' => $page_size,
            'order_by'  => $order_by,
            'group_by'  => $group_by,
            'count'     => $conds['count'] ?? null, //是否显示总条数
            'limit'     => $conds['limit'] ?? null,
            'field'     => $conds['field'] ?? null,
            'append'    => $conds['append'] ?? null, //展示扩充字段(默认展示) []=不展示
            'lock'      => $conds['lock'] ?? null, //排他鎖
            'share'     => $conds['share'] ?? null, //共享鎖
            'load'      => $conds['load'] ?? null, //加载外表
            'index'     => $conds['index'] ?? null,
        ])->toArray();
        return $rows;
    }

    /**
     * 添加
     * @param array $data
     * @return int|mixed
     */
    public function save(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'uid'           => '', //登录失败时uid为空
            'username'      => 'required',
            'agent'         => '',
            'status'        => 'required',
            'cli_hash'      => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $login_ip = request()->ip();
            $data_filter['session_id'] = Session::getId(); //web场景使用
            $data_filter['agent'] = request()->userAgent();
            $data_filter['login_time'] = time();
            $data_filter['login_ip'] = $login_ip;
            $data_filter['login_country'] = ip2country($login_ip);
            $data_filter['cli_hash'] = md5($data_filter['username'].'-'.$login_ip);
            $this->insert($data_filter);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
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
     * 删除
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function del(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'           => 'required',
        ], $data);

        $status = 1;
        try
        {
            $id = $data_filter['id'];
            unset($data_filter['id']);

            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }
            $this->delete(['_id' => $id]);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }

        return $status;
    }
}
