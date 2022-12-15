<?php


namespace App\repositories;


use App\Models\mod_menu;
use App\traits\trait_repo_base;

class repo_menu
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size       = 20; //每页展示几笔
    public $cache_key_admin = "admin_menu";
    public $cache_key_agent = "agent_menu";

    public function __construct(mod_menu $mod_menu)
    {
        $this->model = $mod_menu;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['create_time', 'asc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $guard_name = !empty($conds['guard_name']) ? $conds['guard_name']:'';
        $name       = !empty($conds['name']) ? $conds['name']:'';
        $status     = $conds['status'] ?? null;

        $where = []; //筛选
        $where[] = ['delete_time', '=', 0];
        $guard_name and $where[] = ['guard_name', '=', $guard_name];
        $name and $where[] = ['name', '=', $name];
        is_numeric($status) and $where[] = ['status', '=', $status];

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
     * 添加或修改
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function save(array $data, &$ret_data = [])
    {
        $do             = isset($data['do']) ? $data['do'] : '';
        //参数过滤
        $data_filter = data_filter([
            'do'                => 'required',
            'id'                => $do == 'edit' ? 'required' : '',
            'parent_id'         => '',
            'name'              => 'required',
            'type'              => 'required',
            'guard_name'        => 'required',
            'url'               => '',
            'icon'              => '',
            'perms'             => '', //路由别名
            'sort'              => '',
            'is_show'           => '',
            'status'            => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            $parent_id = empty($data_filter['parent_id']) ? 0 : $data_filter['parent_id'];
            unset($data_filter['do'], $data_filter['id']);

            //通过上级id建立等级
            $level = self::get_level($parent_id);

            if($do == 'add')
            {
                $data_filter['level'] = $level;
                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $id = $this->insert($data_filter);
                $ret_data['id'] = $id;
            }
            elseif($do == 'edit')
            {
                $data_filter['level'] = $level;
                $data_filter['update_time'] = time();
                $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->update($data_filter, ['id' => $id]);
            }

            //遍历更新所有节点层级(level)字段,通过id建立所有子分类id
            $rows = $this->get_list([
                'index'    => 'id',
                'order_by' => ['create_time', 'asc']
            ]);
            $data_item = [];
            foreach($rows as $v)
            {
                $data_item[] = [
                    'level'     => ($v['parent_id'] == 0) ? 0 : $rows[$v['parent_id']]['level'] + 1,
                    'id'        => $v['id'],
                ];
            }
            //批量更新
            $this->insertOrUpdate($data_item,
                ['id'],
                ['level']
            );
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
            'id'            => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            unset($data_filter['id']);

            $this->delete(['id' => $id]); //直接干掉, 不做软删除
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
     * 获取等级
     * @param int $pid
     * @return int|mixed
     */
    public function get_level($pid = 0)
    {
        $level = 0;
        if(!empty($pid))
        {
            $level = $this->get_field_value([
                'fields' => ['level'],
                'where' => [
                    ['id', '=', $pid]
                ]
            ]);
            $level += 1;
        }
        return $level;
    }
}
