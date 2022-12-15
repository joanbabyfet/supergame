<?php


namespace App\repositories;


use App\Models\mod_game_round;
use App\traits\trait_repo_base;

class repo_game_round
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_game_round $mod_game_round)
    {
        $this->model = $mod_game_round;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['settle_time', 'desc']; //默认结算时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $table_id   = !empty($conds['table_id']) ? $conds['table_id'] : '';
        $round_id   = !empty($conds['round_id']) ? $conds['round_id'] : '';
        $date_start   = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';

        $where = []; //筛选
        $round_id and $where[] = ['round_id', 'like', "%{$round_id}%"];
        $table_id and $where[] = ['table_id', '=', $table_id];
        $date_start and $where[] = ['settle_time', '>=', (int)$date_start]; //结算开始时间
        $date_end and $where[] = ['settle_time', '<=', (int)$date_end]; //结算结束时间

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
