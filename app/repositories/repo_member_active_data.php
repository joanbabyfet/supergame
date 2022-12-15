<?php


namespace App\repositories;


use App\Models\mod_member_active_data;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;

class repo_member_active_data
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_member_active_data $mod_member_active_data)
    {
        $this->model = $mod_member_active_data;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['date', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $agent_id   = !empty($conds['agent_id']) ? $conds['agent_id']:'';
        $date_start = !empty($conds['date_start']) ? $conds['date_start'] : ''; //开始时间
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : ''; //结束时间

        $where = []; //筛选
        $date_start and $where[] = ['date', '>=', $date_start];
        $date_end and $where[] = ['date', '<=', $date_end];
        $agent_id and $where[] = ['agent_id', '=', $agent_id];

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
