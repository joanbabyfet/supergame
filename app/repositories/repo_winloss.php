<?php


namespace App\repositories;


use App\Models\mod_winloss;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;

class repo_winloss
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_winloss $mod_winloss)
    {
        $this->model = $mod_winloss;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['settle_time', 'desc']; //默认下注时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $agent_id   = !empty($conds['agent_id']) ? $conds['agent_id'] : '';
        $date_start = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : '';
        $uid        = !empty($conds['uid']) ? $conds['uid'] : [];
        $table_id   = !empty($conds['table_id']) ? $conds['table_id'] : '';
        $round_id   = !empty($conds['round_id']) ? $conds['round_id'] : '';

        $where = []; //筛选
        $where[] = ['uid', '!=', config('global.sys_gz_uid')]; //默認不展示系統公庄
        $agent_id and $where[] = ['agent_id', '=', $agent_id];
        $uid and $where[] = ['uid', 'in', $uid];
        $table_id and $where[] = ['table_id', '=', $table_id];
        $round_id and $where[] = ['round_id', '=', $round_id];
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
            'next_page' => $conds['next_page'] ?? null, //是否有下一页
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
     * 根据用户id获取所有下注过桌子id
     * @param $uid
     * @return array
     */
    public function get_table_ids_by_uid($uid)
    {
        $rows = $this->get_list([
            'fields'    => ['table_id'],
            'uid'       => [$uid]
        ]);
        $table_ids = sql_in($rows, 'table_id');
        return $table_ids;
    }

    /**
     * 获取桌子总输赢列表
     * @param array $conds
     * @return array
     */
    public function get_table_winloss_list(array $conds)
    {
        $uid        = !empty($conds['uid']) ? $conds['uid'] : '';
        $table_id   = !empty($conds['table_id']) ? $conds['table_id'] : [];

        $where = []; //筛选
        $uid and $where[] = ['uid', '=', $uid];
        $table_id and $where[] = ['table_id', 'in', $table_id];

        $rows = $this->lists([
            'index'     => 'table_id',
            'fields'    => [
                'table_id',
                DB::raw('COUNT(DISTINCT uid) AS member_count'), //累计参与人数, 去重
                DB::raw('SUM(winloss_amount) AS total_winloss_amount'),
                DB::raw('SUM(table_owner_commission) AS total_table_owner_commission'), //桌主抽水
                DB::raw('SUM(platform_commission) AS total_platform_commission'), //平台抽水
                DB::raw('SUM(gz_amount) AS total_gz_amount'), //公庄输赢
            ],
            'where'     => $where,
            'group_by'  => ['table_id'],
        ])->toArray();
        return $rows;
    }
}
