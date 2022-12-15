<?php


namespace App\services;


use App\repositories\repo_member_online_per_hour;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_member_online_data
{
    use trait_service_base;

    private $repo_member_online_per_hour;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_member_online_per_hour $repo_member_online_per_hour,
        repo_user $repo_user
    )
    {
        $this->repo_member_online_per_hour    = $repo_member_online_per_hour;
        $this->repo_user                    = $repo_user;
        $this->timezone                     = get_admin_timezone();
    }

    /**
     * 生成每小时数据
     * @param string $from_date
     * @return int|mixed
     */
    public function generate_per_hour_data($from_date='')
    {
        $from_date = empty($from_date) ? '2019/01/01' : $from_date;
        $from_time = empty($from_date) ? '' :
            date_convert_timestamp("{$from_date} 00:00:00", $this->timezone);

        $status = 1;
        try
        {
            //获取渠道会员每天每个小时在线数据
            $sub_query = DB::table('users_login_log AS a')
                ->leftjoin('users AS b', 'a.uid', '=', 'b.id')
                ->select(
                    DB::raw("DATE_FORMAT(FROM_UNIXTIME(pt_a.login_time, '%Y/%m/%d %H:00'), '%Y/%m/%d') AS date"),
                    DB::raw("DATE_FORMAT(FROM_UNIXTIME(pt_a.login_time, '%Y/%m/%d %H:00'), '%H:00') AS hour"),
                    'b.agent_id',
                    DB::raw("COUNT(DISTINCT pt_a.uid) AS count"),
                    )
                ->where('a.login_time', '>=', (int)$from_time)
                ->groupBy('date', 'hour', 'agent_id')
                ->orderBy('date', 'asc');

            $query = DB::table(DB::raw("({$sub_query->toSql()}) AS sub"))
                ->select(
                    DB::raw('date'),
                    DB::raw('agent_id'),
                    DB::raw('SUM(IF(HOUR(hour) = 0, count, 0)) AS h0'),
                    DB::raw('SUM(IF(HOUR(hour) = 1, count, 0)) AS h1'),
                    DB::raw('SUM(IF(HOUR(hour) = 2, count, 0)) AS h2'),
                    DB::raw('SUM(IF(HOUR(hour) = 3, count, 0)) AS h3'),
                    DB::raw('SUM(IF(HOUR(hour) = 4, count, 0)) AS h4'),
                    DB::raw('SUM(IF(HOUR(hour) = 5, count, 0)) AS h5'),
                    DB::raw('SUM(IF(HOUR(hour) = 6, count, 0)) AS h6'),
                    DB::raw('SUM(IF(HOUR(hour) = 7, count, 0)) AS h7'),
                    DB::raw('SUM(IF(HOUR(hour) = 8, count, 0)) AS h8'),
                    DB::raw('SUM(IF(HOUR(hour) = 9, count, 0)) AS h9'),
                    DB::raw('SUM(IF(HOUR(hour) = 10, count, 0)) AS h10'),
                    DB::raw('SUM(IF(HOUR(hour) = 11, count, 0)) AS h11'),
                    DB::raw('SUM(IF(HOUR(hour) = 12, count, 0)) AS h12'),
                    DB::raw('SUM(IF(HOUR(hour) = 13, count, 0)) AS h13'),
                    DB::raw('SUM(IF(HOUR(hour) = 14, count, 0)) AS h14'),
                    DB::raw('SUM(IF(HOUR(hour) = 15, count, 0)) AS h15'),
                    DB::raw('SUM(IF(HOUR(hour) = 16, count, 0)) AS h16'),
                    DB::raw('SUM(IF(HOUR(hour) = 17, count, 0)) AS h17'),
                    DB::raw('SUM(IF(HOUR(hour) = 18, count, 0)) AS h18'),
                    DB::raw('SUM(IF(HOUR(hour) = 19, count, 0)) AS h19'),
                    DB::raw('SUM(IF(HOUR(hour) = 20, count, 0)) AS h20'),
                    DB::raw('SUM(IF(HOUR(hour) = 21, count, 0)) AS h21'),
                    DB::raw('SUM(IF(HOUR(hour) = 22, count, 0)) AS h22'),
                    DB::raw('SUM(IF(HOUR(hour) = 23, count, 0)) AS h23'),
                )
                ->groupBy('date', 'agent_id');
            //合并绑定参数
            $query->mergeBindings($sub_query);
            $member_online_data = $query->get()->toArray();
            $member_online_data = json_decode(json_encode($member_online_data),true); //stdClass转数组

            $count_map = [];
            foreach ($member_online_data as $item)
            {
                $key = "{$item['date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'h0'   => $item['h0'],
                        'h1'   => $item['h1'],
                        'h2'   => $item['h2'],
                        'h3'   => $item['h3'],
                        'h4'   => $item['h4'],
                        'h5'   => $item['h5'],
                        'h6'   => $item['h6'],
                        'h7'   => $item['h7'],
                        'h8'   => $item['h8'],
                        'h9'   => $item['h9'],
                        'h10'   => $item['h10'],
                        'h11'   => $item['h11'],
                        'h12'   => $item['h12'],
                        'h13'   => $item['h13'],
                        'h14'   => $item['h14'],
                        'h15'   => $item['h15'],
                        'h16'   => $item['h16'],
                        'h17'   => $item['h17'],
                        'h18'   => $item['h18'],
                        'h19'   => $item['h19'],
                        'h20'   => $item['h20'],
                        'h21'   => $item['h21'],
                        'h22'   => $item['h22'],
                        'h23'   => $item['h23'],
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'h0'   => $item['h0'],
                        'h1'   => $item['h1'],
                        'h2'   => $item['h2'],
                        'h3'   => $item['h3'],
                        'h4'   => $item['h4'],
                        'h5'   => $item['h5'],
                        'h6'   => $item['h6'],
                        'h7'   => $item['h7'],
                        'h8'   => $item['h8'],
                        'h9'   => $item['h9'],
                        'h10'   => $item['h10'],
                        'h11'   => $item['h11'],
                        'h12'   => $item['h12'],
                        'h13'   => $item['h13'],
                        'h14'   => $item['h14'],
                        'h15'   => $item['h15'],
                        'h16'   => $item['h16'],
                        'h17'   => $item['h17'],
                        'h18'   => $item['h18'],
                        'h19'   => $item['h19'],
                        'h20'   => $item['h20'],
                        'h21'   => $item['h21'],
                        'h22'   => $item['h22'],
                        'h23'   => $item['h23'],
                    ]);
                }
            }

            $count_fields = ['h0', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'h9', 'h10', 'h11',
                'h12', 'h13', 'h14', 'h15', 'h16', 'h17', 'h18', 'h19', 'h20', 'h21', 'h22', 'h23']; //统计字段
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
            $this->repo_member_online_per_hour->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['create_time', 'h0', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'h7', 'h8', 'h9', 'h10', 'h11',
                    'h12', 'h13', 'h14', 'h15', 'h16', 'h17', 'h18', 'h19', 'h20', 'h21', 'h22', 'h23'],
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
