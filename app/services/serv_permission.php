<?php


namespace App\services;


use App\repositories\repo_permission;
use App\repositories\repo_module;
use App\traits\trait_service_base;

class serv_permission
{
    use trait_service_base;

    private $repo_module;
    private $repo_permission;

    public function __construct(
        repo_module $repo_module,
        repo_permission $repo_permission
    )
    {
        $this->repo_module    = $repo_module;
        $this->repo_permission          = $repo_permission;
    }

    /**
     * 获取权限树形
     * @param array $data
     * @return array
     */
    public function get_tree(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'guard'             => 'required',
            'order_by'          => '',
            'is_auth'           => '', //是否验证权限,默认0, 0=不验证
        ], $data);

        $default_guard = get_default_guard(); //默认守卫
        $guard = empty($data_filter['guard']) ? $default_guard : $data_filter['guard'];
        $order_by = empty($data_filter['order_by']) ? ['created_at', 'asc'] : $data_filter['order_by'];
        $is_auth = empty($data_filter['is_auth']) ? 0 : $data_filter['is_auth'];

        //获取权限组/模块(module)
        $tree = $this->repo_module->get_list([
            'fields'    => [
                'id',
                'name AS display_name' //统一返回display_name字段
            ],
            'index'     => 'id',
            'order_by'  =>  ['create_time', 'asc'],
        ]);

        //插入到数组开头
        $first_item[0] = ['id' => 0, 'name' => '未分類'];
        $tree = $first_item + $tree;

        //获取权限列表
        $rows = $this->repo_permission->get_list([
            'fields'        => ['id', 'name', 'guard_name', 'display_name', 'module_id'],
            'guard_name'    =>  $guard,
            'order_by'      =>  $order_by,
        ]);
        //设置人自己拥有的权限，如自己都没有的权限当然不能给别人设置
        $purviews = get_purviews([
            'guard' => config('global.admin.guard'), //固定为admin
            'field' => 'id'
        ]);
        foreach ($rows as $item)
        {
            if (isset($tree[$item['module_id']]))
            {
                //匹配当前用户权限,超级管理员全部展示
                if(in_array('*', $purviews) ||
                    (!$is_auth || in_array($item['id'], $purviews)))
                {
                    $tree[$item['module_id']]['children'][] = $item;
                }
            }
        }
        //遍历,若子项为空,则干掉整个节点,并重置数组键名从0开始
        $tree = array_values(array_filter($tree, function($item) {
            return !empty($item['children']);
        }));
        return $tree;
    }
}
