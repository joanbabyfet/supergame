<?php


namespace App\services;


use App\repositories\repo_member_active_data;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class serv_member_active_data
{
    use trait_service_base;

    private $repo_member_active_data;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_member_active_data $repo_member_active_data,
        repo_user $repo_user
    )
    {
        $this->repo_member_active_data    = $repo_member_active_data;
        $this->repo_user                    = $repo_user;
        $this->timezone                     = get_admin_timezone();
    }

    /**
     * 生成数据
     * @param string $from_date 筛选某日开始日期 例 2022/07/03
     * @return int|mixed
     */
    public function generate_data($from_date='')
    {
        $from_date = empty($from_date) ? '2019/01/01' : $from_date;
        $from_time = empty($from_date) ? '' :
            date_convert_timestamp("{$from_date} 00:00:00", $this->timezone);

        $status = 1;
        try
        {
            //根据用户id与登入时间分组(去重), 只关注日期不关注时间
            $sub_query1 = DB::table('users_login_log')
            ->select(
                'uid',
                DB::raw("FROM_UNIXTIME(login_time, '%Y/%m/%d') as login_time"),
                )
            ->where('login_time', '>=', (int)$from_time)
            ->groupBy('uid', DB::raw("FROM_UNIXTIME(login_time, '%Y/%m/%d')"));

            //自联
            $sub_query = DB::table(DB::raw("({$sub_query1->toSql()}) AS pt_t1"))
                ->select(
                    't1.uid',
                    't1.login_time as date',
                    't2.login_time as date2',
                    )
                ->mergeBindings($sub_query1)
                ->leftJoin(DB::raw("({$sub_query1->toSql()}) AS pt_t2"), function($join)
                {
                    $join->on('t1.uid', '=', 't2.uid')
                        ->on('t2.login_time', '>=', 't1.login_time');
                })
                ->mergeBindings($sub_query1);

            $query = DB::table(DB::raw("({$sub_query->toSql()}) AS pt_a"))
                ->select(
                    DB::raw('pt_a.date'),
                    DB::raw('pt_b.agent_id'),
                    DB::raw('COUNT(DISTINCT pt_a.uid) as member_active_count'),
                    DB::raw('COUNT(DISTINCT if(DATEDIFF(pt_a.date2, pt_a.date) = 1, uid, NULL)) as d1'),
                    DB::raw('COUNT(DISTINCT if(DATEDIFF(pt_a.date2, pt_a.date) BETWEEN 1 AND 3, uid, NULL)) as d3'),
                    DB::raw('COUNT(DISTINCT if(DATEDIFF(pt_a.date2, pt_a.date) BETWEEN 1 AND 7, uid, NULL)) as d7'),
                    DB::raw('COUNT(DISTINCT if(DATEDIFF(pt_a.date2, pt_a.date) BETWEEN 1 AND 14, uid, NULL)) as d14'),
                    DB::raw('COUNT(DISTINCT if(DATEDIFF(pt_a.date2, pt_a.date) BETWEEN 1 AND 30, uid, NULL)) as d30'),
                    )
                ->join('users AS b', 'a.uid', '=', 'b.id')
                ->groupBy('b.agent_id', 'a.date')
                ->mergeBindings($sub_query);

            $member_active_data = $query->get()->toArray();
            $member_active_data = json_decode(json_encode($member_active_data),true); //stdClass转数组

            $count_map = [];
            foreach ($member_active_data as $item)
            {
                $key = "{$item['date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'member_active_count'   => $item['member_active_count'],
                        'd1'                    => $item['d1'],
                        'd3'                    => $item['d3'],
                        'd7'                    => $item['d7'],
                        'd14'                   => $item['d14'],
                        'd30'                   => $item['d30'],
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'member_active_count'   => $item['member_active_count'],
                        'd1'                    => $item['d1'],
                        'd3'                    => $item['d3'],
                        'd7'                    => $item['d7'],
                        'd14'                   => $item['d14'],
                        'd30'                   => $item['d30'],
                    ]);
                }
            }

            $count_fields = ['member_active_count', 'd1', 'd3', 'd7', 'd14', 'd30']; //统计字段
            $data = [];
            foreach ($count_map as $k => $v)
            {
                $key = explode('_', $k); //字符串转数组

                $data_item = [
                    'date'          => $key[0],
                    'agent_id'      => $key[1],
                    'timezone'      => $this->timezone,
                    'create_time'   => time()
                ];

                foreach ($count_fields as $field) //匹配字段, 不在统计字段里则过滤
                {
                    $data_item[$field] = empty($count_map[$k][$field]) ? 0 : $count_map[$k][$field];
                }
                $data[] = $data_item;
            }
            //添加或更新数据
            $this->repo_member_active_data->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['timezone', 'create_time', 'member_active_count', 'd1', 'd3', 'd7', 'd14', 'd30'],
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
                'data'    => $from_date,
            ]);
        }
        return $status;
    }
}
