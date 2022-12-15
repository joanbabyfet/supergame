<?php


namespace App\repositories;


use App\Models\mod_game_table;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;

class repo_game_table
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔

    public function __construct(mod_game_table $mod_game_table)
    {
        $this->model = $mod_game_table;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size      = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by       = !empty($conds['order_by']) ? $conds['order_by'] : ['create_time', 'desc']; //默认添加时间正序
        $group_by       = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $agent_id       = !empty($conds['agent_id']) ? $conds['agent_id'] : '';
        $date_start     = !empty($conds['date_start']) ? $conds['date_start'] : '';
        $date_end       = !empty($conds['date_end']) ? $conds['date_end'] : '';
        $id             = !empty($conds['id']) ? $conds['id'] : [];
        $uid            = !empty($conds['uid']) ? $conds['uid'] : '';
        $room_id        = !empty($conds['room_id']) ? $conds['room_id'] : '';
        $is_closed      = $conds['is_closed'] ?? null;
        $type           = !empty($conds['type']) ? $conds['type'] : '';

        $where = []; //筛选
        $agent_id and $where[] = ['agent_id', '=', $agent_id];
        $id and $where[] = ['id', 'in', $id];
        $uid and $where[] = ['uid', '=', $uid];
        $room_id and $where[] = ['room_id', '=', $room_id];
        $type and $where[] = ['type', '=', $type];
        $date_start and $where[] = ['create_time', '>=', (int)$date_start]; //开始时间
        $date_end and $where[] = ['create_time', '<=', (int)$date_end]; //结束时间

        //自定义筛选
        $where_raw = [];
        $time = time();
        if(is_numeric($is_closed)  && $is_closed == 1)
        {
            $where_raw = ['(end_time <= ? OR (`end_time` >= ? AND delete_time != 0))', [$time, $time]];
        }
        if(is_numeric($is_closed)  && $is_closed == 0)
        {
            $where_raw = ['end_time >= ? AND `status` >= ? AND delete_time = 0', [$time, mod_game_table::ENABLE]];
        }
        $rows = $this->lists([
            'fields'    => $conds['fields'] ?? null,
            'where'     => $where,
            'where_raw' => $where_raw,
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

            $data_filter['delete_time'] = time();
            $data_filter['delete_user'] = defined('AUTH_UID') ? AUTH_UID : '';
            $this->update($data_filter, ['id' => $id]);
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
