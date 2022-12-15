<?php


namespace App\repositories;


use App\Models\mod_permission;
use App\traits\trait_repo_base;

class repo_permission
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_permission $mod_permission)
    {
        $this->model = $mod_permission;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size      = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by       = !empty($conds['order_by']) ? $conds['order_by'] : ['created_at', 'desc']; //默认添加时间正序
        $group_by       = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $display_name   = !empty($conds['display_name']) ? $conds['display_name']:'';
        $guard_name     = !empty($conds['guard_name']) ? $conds['guard_name']:'';
        $module_id          = !empty($conds['module_id']) ? $conds['module_id']:'';

        $where = []; //筛选
        $display_name and $where[] = ['display_name', 'like', "%{$display_name}%"];
        $guard_name and $where[] = ['guard_name', '=', $guard_name];
        is_numeric($module_id) and $where[] = ['module_id', '=', $module_id];

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
}
