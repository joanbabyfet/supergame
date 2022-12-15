<?php


namespace App\services;


use App\repositories\repo_agent_balance_data;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_agent_balance_data
{
    use trait_service_base;

    private $repo_agent_balance_data;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_agent_balance_data $repo_agent_balance_data,
        repo_user $repo_user
    )
    {
        $this->repo_agent_balance_data    = $repo_agent_balance_data;
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
            //获取渠道额度统计数据, 只关注日期不关注时间
            $agent_balance_data = DB::table('order_transfer', 'a')
                ->leftJoin('agents AS b', 'a.agent_id', '=', 'b.id')
                ->select(
                    DB::raw("FROM_UNIXTIME(pt_a.pay_time, '%Y/%m/%d') AS date"),
                    'a.agent_id',
                    DB::raw("SUM(IF(pt_a.type = 1, pt_a.amount, 0)) AS deposit_amount"),
                    DB::raw("SUM(IF(pt_a.type = 2, pt_a.amount, 0)) AS withdraw_amount"),
                    'b.agent_balance',
                    DB::raw("pt_b.agent_balance - SUM(IF(pt_a.type = 2, pt_a.amount, 0)) AS remain_balance"),
                    )
                ->where('a.pay_time', '>=', (int)$from_time)
                ->groupBy('date', 'agent_id')
                ->orderBy('date', 'asc')
                ->get()->toArray();
            $agent_balance_data = json_decode(json_encode($agent_balance_data),true); //stdClass转数组

            $count_map = [];
            foreach ($agent_balance_data as $item)
            {
                $key = "{$item['date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'deposit_amount'    => $item['deposit_amount'],
                        'withdraw_amount'   => $item['withdraw_amount'],
                        'agent_balance'     => $item['agent_balance'],
                        'remain_balance'    => $item['remain_balance'],
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'deposit_amount'    => $item['deposit_amount'],
                        'withdraw_amount'   => $item['withdraw_amount'],
                        'agent_balance'     => $item['agent_balance'],
                        'remain_balance'    => $item['remain_balance'],
                    ]);
                }
            }

            $count_fields = ['deposit_amount', 'withdraw_amount', 'agent_balance', 'remain_balance']; //统计字段
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
            $this->repo_agent_balance_data->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['timezone', 'create_time', 'deposit_amount', 'withdraw_amount', 'agent_balance', 'remain_balance'],
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
