<?php


namespace App\services;


use App\repositories\repo_member_balance_data;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_member_balance_data
{
    use trait_service_base;

    private $repo_member_balance_data;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_member_balance_data $repo_member_balance_data,
        repo_user $repo_user
    )
    {
        $this->repo_member_balance_data    = $repo_member_balance_data;
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
            //获取用户额度记录, 只关注日期不关注时间
            $user_balance_data = DB::table('order_transfer')
                ->select(
                    DB::raw("FROM_UNIXTIME(pay_time, '%Y/%m/%d') AS date"),
                    'uid',
                    'agent_id',
                    DB::raw("SUM(IF(type = 1, amount, 0)) AS deposit_amount"),
                    DB::raw("SUM(IF(type = 2, amount, 0)) AS withdraw_amount"),
                    )
                ->where('pay_time', '>=', (int)$from_time)
                ->groupBy('date', 'uid', 'agent_id')
                ->orderBy('date', 'asc')
                ->get()->toArray();
            $user_balance_data = json_decode(json_encode($user_balance_data),true); //stdClass转数组

            $count_map = [];
            foreach ($user_balance_data as $item)
            {
                $key = "{$item['date']}_{$item['uid']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'deposit_amount'        => $item['deposit_amount'],
                        'withdraw_amount'       => $item['withdraw_amount'],
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'deposit_amount'        => $item['deposit_amount'],
                        'withdraw_amount'       => $item['withdraw_amount'],
                    ]);
                }
            }

            $count_fields = ['deposit_amount', 'withdraw_amount']; //统计字段
            $data = [];
            foreach ($count_map as $k => $v)
            {
                $key = explode('_', $k); //字符串转数组

                $data_item = [
                    'date'          => $key[0],
                    'uid'      => $key[1],
                    'agent_id'      => $key[2],
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
            $this->repo_member_balance_data->insertOrUpdate($data,
                ['date', 'uid'],
                ['agent_id', 'timezone', 'create_time', 'deposit_amount', 'withdraw_amount'],
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
