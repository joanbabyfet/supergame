<?php


namespace App\repositories;


use App\Models\mod_admin_user_oplog;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\Session;

class repo_admin_user_oplog
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_admin_user_oplog $mod_admin_user_oplog)
    {
        $this->model = $mod_admin_user_oplog;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['op_time', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $username   = !empty($conds['username']) ? $conds['username']:'';
        $date_start   = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';
        $module_id   = !empty($conds['module_id']) ? $conds['module_id']:'';
        $uid         = !empty($conds['uid']) ? $conds['uid']:'';

        $where = []; //筛选
        $username and $where[] = ['username', 'like', "%{$username}%"];
        $date_start and $where[] = ['op_time', '>=', (int)$date_start]; //开始时间
        $date_end and $where[] = ['op_time', '<=', (int)$date_end]; //结束时间
        $module_id and $where[] = ['module_id', '=', (int)$module_id];
        $uid and $where[] = ['uid', '=', $uid];

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
    public function add_log($msg, $module = 0)
    {
        $guard = config('global.admin.guard');
        $op_ip = request()->ip();

        $data['uid'] = auth($guard)->user()->getAuthIdentifier();
        $data['username'] = auth($guard)->user()->username;
        $data['session_id'] = Session::getId(); //web场景使用
        $data['msg'] = addslashes($msg); //替字符增加反斜線 ' => \'
        $data['module_id'] = $module;
        $data['op_time'] = time();
        $data['op_ip'] = $op_ip;
        $data['op_country'] = ip2country($op_ip);
        $data['op_url'] = request()->path(); //获取地址不含参数 example
        //$data['op_url'] = request()->getRequestUri(); //获取地址含参数 /example?key=value
        $this->insert($data);
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
