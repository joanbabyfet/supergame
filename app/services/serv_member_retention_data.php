<?php


namespace App\services;


use App\repositories\repo_member_retention_data;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_member_retention_data
{
    use trait_service_base;

    private $repo_member_retention_data;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_member_retention_data $repo_member_retention_data,
        repo_user $repo_user
    )
    {
        $this->repo_member_retention_data    = $repo_member_retention_data;
        $this->repo_user                    = $repo_user;
        $this->timezone                     = get_admin_timezone();
    }

    /**
     * 生成数据
     * @param string $from_date
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
            //获取玩家登入日志 (去重)
            $sub_query1 = DB::table('users_login_log')
                ->select(
                    DB::raw("DISTINCT FROM_UNIXTIME(login_time, '%Y/%m/%d') as login_date"),
                    'uid',
                    'username'
                );

            //获取玩家注册日期数据
            $sub_query2 = DB::table('users')
                ->select(
                    'id',
                    'agent_id',
                    DB::raw("FROM_UNIXTIME(create_time, '%Y/%m/%d') as create_date")
                )
                ->where('create_time', '>=', (int)$from_time);

            $sub_query = DB::table(DB::raw("({$sub_query1->toSql()}) AS pt_t1"))
                ->select(
                    't2.id as uid',
                    't2.agent_id',
                    't2.create_date',
                    't1.login_date',
                    DB::raw("DATEDIFF(pt_t1.login_date, pt_t2.create_date) as day_diff"),
                )
                ->rightJoin(DB::raw("({$sub_query2->toSql()}) AS pt_t2"), 't1.uid', '=', 't2.id')
                ->mergeBindings($sub_query2);

            $query = DB::table(DB::raw("({$sub_query->toSql()}) AS c"))
                ->select(
                    'create_date',
                    'agent_id',
                    DB::raw("COUNT(DISTINCT uid) as member_register_count"),
                    DB::raw("COUNT(DISTINCT uid, (CASE WHEN (day_diff = 1) THEN uid END)) as d1"),
                    DB::raw("COUNT(DISTINCT uid, (CASE WHEN (day_diff BETWEEN 1 AND 3) THEN uid END)) as d3"),
                    DB::raw("COUNT(DISTINCT uid, (CASE WHEN (day_diff BETWEEN 1 AND 7) THEN uid END)) as d7"),
                    DB::raw("COUNT(DISTINCT uid, (CASE WHEN (day_diff BETWEEN 1 AND 14) THEN uid END)) as d14"),
                    DB::raw("COUNT(DISTINCT uid, (CASE WHEN (day_diff BETWEEN 1 AND 30) THEN uid END)) as d30"),
                    )
                ->groupBy('create_date', 'agent_id')
                ->mergeBindings($sub_query);

            $member_retention_data = $query->get()->toArray();
            $member_retention_data = json_decode(json_encode($member_retention_data),true); //stdClass转数组

            $count_map = [];
            foreach ($member_retention_data as $item)
            {
                $key = "{$item['create_date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'member_register_count'   => $item['member_register_count'],
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
                        'member_register_count'   => $item['member_register_count'],
                        'd1'                    => $item['d1'],
                        'd3'                    => $item['d3'],
                        'd7'                    => $item['d7'],
                        'd14'                   => $item['d14'],
                        'd30'                   => $item['d30'],
                    ]);
                }
            }

            $count_fields = ['member_register_count', 'd1', 'd3', 'd7', 'd14', 'd30']; //统计字段
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
            $this->repo_member_retention_data->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['timezone', 'create_time', 'member_register_count', 'd1', 'd3', 'd7', 'd14', 'd30'],
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
