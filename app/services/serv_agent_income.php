<?php


namespace App\services;


use App\Models\mod_agent_income;
use App\Models\mod_order_transfer;
use App\repositories\repo_agent_income;
use App\repositories\repo_user;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

class serv_agent_income
{
    use trait_service_base;

    private $repo_agent_income;
    private $repo_user;
    private $timezone;

    public function __construct(
        repo_agent_income $repo_agent_income,
        repo_user $repo_user
    )
    {
        $this->repo_agent_income    = $repo_agent_income;
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
            //获取渠道平台抽水, 只关注日期不关注时间
            $agent_income = DB::table('winloss')
                ->select(
                    DB::raw("FROM_UNIXTIME(settle_time, '%Y/%m/%d') AS date"),
                    'agent_id',
                    DB::raw("SUM(platform_commission) AS commission"), //平台抽水
                    DB::raw("SUM(gz_amount) AS gz_amount"), //公庄输赢
                    DB::raw("SUM(gz_amount + platform_commission) AS platform_income"), //游戏总损益
                    )
                ->where('settle_time', '>=', (int)$from_time)
                ->groupBy('date', 'agent_id')
                ->orderBy('date', 'asc')
                ->get()->toArray();
            $agent_income = json_decode(json_encode($agent_income),true); //stdClass转数组

            //获取渠道存款与提款总额
            $order_transfer = DB::table('order_transfer')
                ->select(
                    DB::raw("FROM_UNIXTIME(pay_time, '%Y/%m/%d') AS date"),
                    'agent_id',
                    DB::raw("SUM(IF(type = 1, amount, 0)) AS deposit_amount"), //存款总额
                    DB::raw("SUM(IF(type = 2, amount, 0)) AS withdraw_amount"), //提款总额
                    DB::raw("SUM(IF(type = 1, amount, 0)) - SUM(IF(type = 2, amount, 0)) AS net_amount"), //存提净额
                )
                ->where('pay_time', '>=', (int)$from_time)
                ->where('pay_status', '=', mod_order_transfer::PAY_STATUS_SUCCESS)
                ->whereIn('origin', [1, 2]) //订单来源 1=玩家下单 2=后台下单
                ->groupBy('date', 'agent_id')
                ->orderBy('date', 'asc')
                ->get()->toArray();
            $order_transfer = json_decode(json_encode($order_transfer),true); //stdClass转数组

            $count_map = [];
            foreach ($agent_income as $item)
            {
                $key = "{$item['date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'commission'        => $item['commission'],
                        'gz_amount'         => $item['gz_amount'],
                        'platform_income'   => $item['platform_income'],
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'commission'        => $item['commission'],
                        'gz_amount'         => $item['gz_amount'],
                        'platform_income'   => $item['platform_income'],
                    ]);
                }
            }

            foreach ($order_transfer as $item)
            {
                $key = "{$item['date']}_{$item['agent_id']}";

                if (!isset($count_map[$key]))
                {
                    $count_map[$key] = [
                        'deposit_amount'    => $item['deposit_amount'], //存款总额
                        'withdraw_amount'   => $item['withdraw_amount'], //提款总额
                        'net_amount'        => $item['net_amount'], //存提净额
                    ];
                }
                else
                {
                    $count_map[$key] = array_merge($count_map[$key], [
                        'deposit_amount'    => $item['deposit_amount'],
                        'withdraw_amount'   => $item['withdraw_amount'],
                        'net_amount'        => $item['net_amount'],
                    ]);
                }
            }

            $count_fields = ['gz_amount', 'commission', 'platform_income', 'deposit_amount', 'withdraw_amount', 'net_amount']; //统计字段
            $data = [];
            foreach ($count_map as $k => $v)
            {
                $key = explode('_', $k); //字符串转数组

                $data_item = [
                    'date'              => $key[0],
                    'agent_id'          => $key[1],
                    'timezone'          => $this->timezone,
                    'create_time'       => time(),
                ];

                foreach ($count_fields as $field) //匹配字段, 不在统计字段里则过滤
                {
                    $data_item[$field] = empty($count_map[$k][$field]) ? '0.00' : $count_map[$k][$field];
                }
                $data[] = $data_item;
            }
            //添加或更新数据
            $this->repo_agent_income->insertOrUpdate($data,
                ['date', 'agent_id'],
                ['timezone', 'create_time', 'gz_amount', 'commission', 'platform_income', 'deposit_amount', 'withdraw_amount', 'net_amount'],
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
