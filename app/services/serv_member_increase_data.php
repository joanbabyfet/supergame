<?php


namespace App\services;


use App\repositories\repo_member_increase_data;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_member_increase_data
{
    use trait_service_base;

    private $repo_member_increase_data;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_member_increase_data $repo_member_increase_data,
        repo_user $repo_user
    )
    {
        $this->repo_member_increase_data    = $repo_member_increase_data;
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
            //获取会员增长数据
            $member_count_data = $this->repo_user->lists([
                'fields'    => [
                    DB::raw('count(*) AS member_increase_count'),
                    'agent_id',
                    DB::raw("DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(`create_time`, '%Y/%m/%d %H:00'), '+8:00', '+7:00'), '%Y/%m/%d') As date"),
                ],
                'where'     => [
                    ['delete_time', '=', 0],
                    ['create_time', '>=', (int)$from_time]
                ],
                'group_by'  => ['date', 'agent_id'], //依注册时间,来源做分组
                'order_by'  => ['date', 'asc'],
            ])->toArray();

            $count_map = [];
            $pre_member_counts = [];
            foreach($member_count_data as $item)
            {
                //查找最近一条统计
                if (!isset($pre_member_counts[$item['agent_id']]))
                {
                    $pre_member_counts[$item['agent_id']] = (int)$this->repo_member_increase_data->get_field_value([
                        'fields'    => ['member_count'],
                        'where'     => [
                            ['date', '<', $item['date']],
                            ['agent_id', '=', $item['agent_id']],
                        ],
                        'order_by' => ['date', 'desc'],
                    ]);
                }
                $key = "{$item['date']}_{$item['agent_id']}";
                $member_count = $item['member_increase_count'] + $pre_member_counts[$item['agent_id']];
                $count_map[$key] = [
                    'member_increase_count' => $item['member_increase_count'],
                    'member_count'          => $member_count
                ];
                $pre_member_counts[$item['agent_id']] = $member_count;
            }

            $count_fields = ['member_count', 'member_increase_count']; //统计字段
            $data = [];
            foreach ($count_map as $k => $row)
            {
                $key = explode('_', $k); //字符串转数组
                $data_item = [
                    'date'          => $key[0],
                    'agent_id'      => $key[1],
                    'timezone'      => $this->timezone,
                    'create_time'   => time()
                ];

                foreach ($count_fields as $field) //匹配字段
                {
                    $data_item[$field] = empty($count_map[$k][$field]) ? 0 : $count_map[$k][$field];
                }
                $data[] = $data_item;
            }
            //添加或更新数据
            $this->repo_member_increase_data->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['timezone', 'create_time', 'member_count', 'member_increase_count'],
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
