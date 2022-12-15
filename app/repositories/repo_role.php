<?php


namespace App\repositories;


use App\Models\mod_role;
use App\traits\trait_repo_base;
use Spatie\Permission\Models\Role;

class repo_role
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_role $mod_role)
    {
        $this->model = $mod_role;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['created_at', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $name      = !empty($conds['name']) ? $conds['name'] : '';
        $guard_name = !empty($conds['guard_name']) ? $conds['guard_name']:'';

        $where = []; //筛选
        $name and $where[] = ['name', 'like', "%{$name}%"];
        $guard_name and $where[] = ['guard_name', '=', $guard_name];

        $rows = $this->lists([
            'fields'        => $conds['fields'] ?? null,
            'where'         => $where,
            'page'          => $conds['page'] ?? null,
            'page_size'     => $page_size,
            'order_by'      => $order_by,
            'group_by'      => $group_by,
            'count'         => $conds['count'] ?? null, //是否显示总条数
            'limit'         => $conds['limit'] ?? null,
            'field'         => $conds['field'] ?? null,
            'append'        => $conds['append'] ?? null, //展示扩充字段(默认展示) []=不展示
            'lock'          => $conds['lock'] ?? null, //排他鎖
            'share'         => $conds['share'] ?? null, //共享鎖
            'load'          => $conds['load'] ?? null, //加载外表
            'index'         => $conds['index'] ?? null,
            'with_count'    => $conds['with_count'] ?? null,
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
            'do'            => 'required',
            'id'            => $do == 'edit' ? 'required' : '',
            'name'          => 'required',
            'guard_name'    => 'required',
            'desc'          => '',
            'permissions'   => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            $guard  = $data_filter['guard_name'];
            $permissions = empty($data_filter['permissions']) ? [] : $data_filter['permissions'];
            unset($data_filter['do'], $data_filter['id'], $data_filter['permissions']);

            if($do == 'add')
            {
                $row = $this->find(['where' => [['name', '=', $data_filter['name']]]]);
                if($row)
                {
                    $this->exception('该名称已经存在', -2);
                }
                $data_filter['created_at'] = date('Y-m-d H:i:s');
                $id = $this->insert($data_filter);

                $role = Role::findById($id, $guard);
                $role->givePermissionTo($permissions); //添加组权限
            }
            elseif($do == 'edit')
            {
                //检测名称是否被使用
                $row = $this->find(['where' => [
                    ['name', '=', $data_filter['name']],
                    ['guard_name', '=', $guard],
                    ['id', '!=', $id],
                ]]);
                if($row)
                {
                    $this->exception('该名称已经存在', -3);
                }

                $data_filter['updated_at'] = date('Y-m-d H:i:s');
                $this->update($data_filter, ['id' => $id]);

                if($id != config('global.role_super_admin')) //超级管理员不做同步
                {
                    $role = Role::findById($id, $guard);
                    $role->syncPermissions($permissions); //同步组权限
                }
            }
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

            if(in_array($id, [config('global.role_super_admin'), config('global.role_general_agent'),
                config('global.role_general_member')]))
            {
                $role_name = ($id == config('global.role_super_admin')) ? '超级管理员' :
                    (($id == config('global.role_general_agent')) ? '普通代理' : '普通会员');
                self::exception("{$role_name}无法删除", -2);
            }

            $this->delete(['id' => $id]);
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
