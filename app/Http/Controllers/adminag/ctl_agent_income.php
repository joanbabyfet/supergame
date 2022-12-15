<?php

namespace App\Http\Controllers\adminag;

use App\Models\mod_order_transfer;
use App\repositories\repo_agent_income;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_agent_income extends Controller
{
    private $repo_agent_income;
    private $serv_util;

    public function __construct(
        repo_agent_income $repo_agent_income,
        serv_util $serv_util
    )
    {
        parent::__construct();
        $this->repo_agent_income = $repo_agent_income;
        $this->serv_util         = $serv_util;
    }

    /**
     * 获取代理數入记录
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page_size  = $request->input('page_size', $this->repo_agent_income->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;
        $divide     = $request->input('divide', 0); //平台分成比例, 例 3%

        $conds = [
            'field'     => DB::raw('DISTINCT date'),
            'fields'    => [
                'date',
                'agent_id',
                DB::raw('SUM(gz_amount) AS gz_amount'),
                DB::raw('SUM(commission) AS commission'),
                DB::raw('SUM(platform_income) AS platform_income'),
                DB::raw('SUM(deposit_amount) AS deposit_amount'),
                DB::raw('SUM(withdraw_amount) AS withdraw_amount'),
                DB::raw('SUM(net_amount) AS net_amount'),
            ],
            'agent_id'      => $this->pid,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'group_by'      => ['date', 'agent_id'],
            'order_by'      => ['date', 'desc'],
            'load'          => ['agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_agent_income->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size); //总页数

        //检测是否有给平台分成比例
        if(!empty($divide))
        {
            foreach($rows['lists'] as $k => $v)
            {
                $divide_percent = round($divide / 100, 2);
                $row_plus = [
                    'platform_divide_income' => money(round($v['platform_income'] * $divide_percent, 2), '') ?? '0.00', //平台分成总额, 四舍五入
                    'platform_net_income' => money(round($v['platform_income'] * (1 - $divide_percent), 2), '') ?? '0.00'  //游戏净损益, 四舍五入
                ];
                $rows['lists'][$k] = array_merge($v, $row_plus);
            }
        }

        if(get_action() == 'export')
        {
            $titles = [
                'date'                  => '日期',
                'agent_maps.realname'   => '渠道',
                'commission'            => '抽水',
                'gz_amount'             => '公庄损益',
                'platform_income'       => '游戏总损益',
                'deposit_amount'        => '存款总额',
                'withdraw_amount'       => '提款总额',
                'net_amount'            => '存提净额',
            ];

            if(!empty($divide)) {
                $titles = array_merge($titles, [
                    'platform_divide_income'    => '平台分成总额',
                    'platform_net_income'       => '游戏净损益',
                ]);
            }

            $status = $this->serv_util->export_data([
                'page_no'       => $page,
                'rows'          => $rows['lists'],
                'file'          => $request->input('file', ''),
                'fields'        => $request->input('fields', []), //列表所有字段
                'titles'        => $titles, //輸出字段
                'total_page'    => $total_page,
            ], $ret_data);
            if($status < 0)
            {
                return res_error($this->serv_util->get_err_msg($status), $status);
            }
            return res_success($ret_data);
        }

        return res_success($rows);
    }

    /**
     * 导出excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export(Request $request)
    {
        return $this->index($request);
    }

    /**
     * 获取今日數入統計
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_statistics(Request $request)
    {
        $today                  = date('Y/m/d'); //获取今天日期
        $date_start             = date_convert_timestamp("{$today} 00:00:00", get_admin_timezone());
        $date_end               = date_convert_timestamp("{$today} 23:59:59", get_admin_timezone());
        $today_commission       = 0; //今日游戏总抽水
        $today_gz_amount        = 0; //今日公庄总输赢
        $today_platform_income  = 0; //合计总收入
        $today_deposit_amount   = 0; //存款总额
        $today_withdraw_amount  = 0; //提款总额
        $today_net_amount       = 0; //存提净额

        //获取渠道平台抽水, 只关注日期不关注时间
        $agent_income = DB::table('winloss')
            ->select(
                DB::raw("FROM_UNIXTIME(settle_time, '%Y/%m/%d') AS date"),
                'agent_id',
                DB::raw("SUM(platform_commission) AS commission"), //平台抽水
                DB::raw("SUM(gz_amount) AS gz_amount"), //公庄输赢
                DB::raw("SUM(gz_amount + platform_commission) AS platform_income"), //游戏总损益
            )
            ->where('settle_time', '>=', (int)$date_start)
            ->where('settle_time', '<=', (int)$date_end)
            ->where('agent_id', '=', $this->pid)
            ->groupBy('date', 'agent_id')
            ->get()->toArray();
        $agent_income = json_decode(json_encode($agent_income),true); //stdClass转数组

        foreach($agent_income as $item)
        {
            $today_commission += $item['commission'];
            $today_gz_amount += $item['gz_amount'];
            $today_platform_income += $item['platform_income'];
        }

        //获取渠道存款与提款总额
        $order_transfer = DB::table('order_transfer')
            ->select(
                DB::raw("FROM_UNIXTIME(pay_time, '%Y/%m/%d') AS date"),
                'agent_id',
                DB::raw("SUM(IF(type = 1, amount, 0)) AS deposit_amount"), //存款总额
                DB::raw("SUM(IF(type = 2, amount, 0)) AS withdraw_amount"), //提款总额
                DB::raw("SUM(IF(type = 1, amount, 0)) - SUM(IF(type = 2, amount, 0)) AS net_amount"), //存提净额
            )
            ->where('pay_time', '>=', (int)$date_start)
            ->where('pay_time', '<=', (int)$date_end)
            ->where('pay_status', '=', mod_order_transfer::PAY_STATUS_SUCCESS)
            ->whereIn('origin', [1, 2]) //订单来源 1=玩家下单 2=后台下单
            ->where('agent_id', '=', $this->pid)
            ->groupBy('date', 'agent_id')
            ->get()->toArray();
        $order_transfer = json_decode(json_encode($order_transfer),true); //stdClass转数组

        foreach($order_transfer as $v)
        {
            $today_deposit_amount += $v['deposit_amount'];
            $today_withdraw_amount += $v['withdraw_amount'];
            $today_net_amount += $v['net_amount'];
        }

        $data = [
            'today_commission' => money($today_commission), //金额统一返回字符串
            'today_gz_amount' => money($today_gz_amount),
            'today_platform_income' => money($today_platform_income),
            'today_deposit_amount' => money($today_deposit_amount),
            'today_withdraw_amount' => money($today_withdraw_amount),
            'today_net_amount' => money($today_net_amount),
        ];
        return res_success($data);
    }
}
