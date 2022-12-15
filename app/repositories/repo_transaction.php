<?php


namespace App\repositories;


use App\Models\mod_agent;
use App\Models\mod_transaction;
use App\Models\mod_user;
use App\traits\trait_repo_base;

class repo_transaction
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_transaction $mod_transaction)
    {
        $this->model = $mod_transaction;
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
        $type      = !empty($conds['type']) ? $conds['type'] : '';
        $uid      = !empty($conds['uid']) ? $conds['uid'] : '';
        $user_type      = !empty($conds['user_type']) ? $conds['user_type'] : '';

        $where = []; //筛选
        $type and $where[] = ['type', '=', $type];
        $uid and $where[] = ['payable_id', '=', $uid];
        $user_type == 1 and $where[] = ['payable_type', '=', get_class(new mod_user())];
        $user_type == 2 and $where[] = ['payable_type', '=', get_class(new mod_agent())];

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
