<?php

namespace App\Http\Controllers\admin;

use App\Models\mod_order_transfer;
use App\repositories\repo_admin_user_oplog;
use App\repositories\repo_agent;
use App\repositories\repo_agent_balance_data;
use App\repositories\repo_member_active_data;
use App\repositories\repo_member_balance_data;
use App\repositories\repo_member_increase_data;
use App\repositories\repo_member_online_per_hour;
use App\repositories\repo_member_retention_data;
use App\repositories\repo_order_transfer;
use App\repositories\repo_user;
use App\services\serv_agent_balance_data;
use App\services\serv_member_active_data;
use App\services\serv_member_increase_data;
use App\services\serv_member_online_data;
use App\services\serv_member_retention_data;
use App\services\serv_util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ctl_report extends Controller
{
    private $repo_member_active_data;
    private $repo_member_increase_data;
    private $repo_member_retention_data;
    private $repo_member_online_per_hour;
    private $repo_admin_user_oplog;
    private $repo_agent_balance_data;
    private $repo_member_balance_data;
    private $repo_order_transfer;
    private $repo_user;
    private $repo_agent;
    private $serv_member_retention_data;
    private $serv_member_active_data;
    private $serv_member_increase_data;
    private $serv_member_online_data;
    private $serv_agent_balance_data;
    private $serv_util;
    private $today;

    public function __construct(
        repo_member_active_data $repo_member_active_data,
        repo_member_increase_data $repo_member_increase_data,
        repo_member_retention_data $repo_member_retention_data,
        repo_member_online_per_hour $repo_member_online_per_hour,
        repo_admin_user_oplog $repo_admin_user_oplog,
        repo_agent_balance_data $repo_agent_balance_data,
        repo_member_balance_data $repo_member_balance_data,
        repo_order_transfer $repo_order_transfer,
        repo_user $repo_user,
        repo_agent $repo_agent,
        serv_member_retention_data $serv_member_retention_data,
        serv_member_active_data $serv_member_active_data,
        serv_member_increase_data $serv_member_increase_data,
        serv_member_online_data $serv_member_online_data,
        serv_agent_balance_data $serv_agent_balance_data,
        serv_util $serv_util
    )
    {
        parent::__construct();
        $this->repo_member_active_data = $repo_member_active_data;
        $this->repo_member_increase_data = $repo_member_increase_data;
        $this->repo_member_retention_data = $repo_member_retention_data;
        $this->repo_member_online_per_hour = $repo_member_online_per_hour;
        $this->repo_admin_user_oplog = $repo_admin_user_oplog;
        $this->repo_agent_balance_data = $repo_agent_balance_data;
        $this->repo_member_balance_data = $repo_member_balance_data;
        $this->repo_order_transfer = $repo_order_transfer;
        $this->repo_user = $repo_user;
        $this->repo_agent = $repo_agent;
        $this->serv_member_retention_data = $serv_member_retention_data;
        $this->serv_member_active_data = $serv_member_active_data;
        $this->serv_member_increase_data = $serv_member_increase_data;
        $this->serv_member_online_data = $serv_member_online_data;
        $this->serv_agent_balance_data = $serv_agent_balance_data;
        $this->serv_util = $serv_util;
        $this->today = date('Y/m/d');
    }

    /**
     * 获取用户活跃数据列表, 目前改成实时统计
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function member_active_list(Request $request)
    {
        $agent_id   = $request->input('agent_id', '');
        $page_size  = get_action() == 'export_member_active' ? 100 :
            $request->input('page_size', $this->repo_member_active_data->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;

        //更新昨天起数据
        if ($page == 1)
        {
            $this->serv_member_active_data->generate_data(date('Y/m/d', strtotime('-30 day')));
        }

        $conds = [
            'agent_id'      => $agent_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'order_by'      => ['date', 'desc'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_member_active_data->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size);

        //获取代理信息
        $agents = $this->repo_agent->lists([
            'fields'    => ['id', 'realname'],
            'index'     => 'id',
            'where'     => [
                ['id', '=', sql_in($rows['lists'], 'agent_id')],
        ]])->toArray();

        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $row_plus = [
                'realname' => $agents[$v['agent_id']]['realname'] ?? '',
            ];
            $rows['lists'][$k] = array_merge($v, $row_plus);
        }

        if(get_action() == 'export_member_active')
        {
            $titles = [
                'realname'              => '渠道',
                'member_active_count'   => '总登录用户',
                'd1'                    => '次日',
                'd7'                    => '7日',
                'd30'                   => '30日',
            ];

            $status = $this->serv_util->export_data([
                'page_no'   => $page,
                'rows'      => $rows['lists'],
                'file'      => $request->input('file', ''),
                'fields'    => $request->input('fields', []), //列表所有字段
                'titles'    => $titles, //輸出字段
                'total_page' => $total_page,
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
     * 导出用户活跃数据excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export_member_active(Request $request)
    {
        return $this->member_active_list($request);
    }

    /**
     * 获取用户留存数据列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function member_retention_list(Request $request)
    {
        $agent_id       = $request->input('agent_id', '');
        $page_size  = get_action() == 'export_member_retention' ? 100 :
            $request->input('page_size', $this->repo_member_retention_data->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;

        //更新昨天起数据
        if ($page == 1)
        {
            $this->serv_member_retention_data->generate_data(date('Y/m/d', strtotime('-30 day')));
        }

        $conds = [
            'agent_id'      => $agent_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'order_by'      => ['date', 'desc'],
            'load'          => ['agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_member_retention_data->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size);

        if(get_action() == 'export_member_retention')
        {
            $titles = [
                'agent_maps.realname'   => '渠道',
                'member_register_count' => '总注册用户',
                'd1'                    => '次日',
                'd7'                    => '7日',
                'd30'                   => '30日',
            ];

            $status = $this->serv_util->export_data([
                'page_no'   => $page,
                'rows'      => $rows['lists'],
                'file'      => $request->input('file', ''),
                'fields'    => $request->input('fields', []), //列表所有字段
                'titles'    => $titles, //輸出字段
                'total_page' => $total_page,
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
     * 导出用户留存数据excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export_member_retention(Request $request)
    {
        return $this->member_retention_list($request);
    }

    /**
     * 获取用户增长数据列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function member_increase_list(Request $request)
    {
        $agent_id       = $request->input('agent_id', '');
        $page_size  = $request->input('page_size', $this->repo_member_increase_data->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;

        //更新今天数据
        if ($page == 1)
        {
            $this->serv_member_increase_data->generate_data($this->today);
        }

        $conds = [
            'fields'    => [
                'date',
                'agent_id',
                DB::raw('SUM(member_count) AS member_count'),
                DB::raw('SUM(member_increase_count) AS member_increase_count'),
            ],
            'agent_id'      => $agent_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'group_by'      => ['date', 'agent_id'],
            'order_by'      => ['date', 'desc'],
            'load'          => ['agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_member_increase_data->get_list($conds);
        return res_success($rows);
    }

    /**
     * 获取用户在线数据列表
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function member_online_list(Request $request)
    {
        $agent_id       = $request->input('agent_id', '');
        $page_size  = get_action() == 'export_member_online' ? 100 :
            $request->input('page_size', $this->repo_member_online_per_hour->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;

        //更新今天数据
//        if ($page == 1)
//        {
//            $this->serv_member_online_data->generate_per_hour_data($this->today);
//        }

//        $conds = [
//            'agent_id'      => $agent_id,
//            'date_start'    => $date_start,
//            'date_end'      => $date_end,
//            'page_size'     => $page_size, //每页几条
//            'page'          => $page, //第几页
//            'order_by'      => ['date', 'desc'],
//            'load'          => ['agent_maps'],
//            'count'         => 1, //是否返回总条数
//        ];
//        $rows = $this->repo_member_online_per_hour->get_list($conds);
//        $total_page = ceil($rows['total'] / $page_size);

        //获取渠道会员每天每个小时打点数据
        $sub_query = DB::table('member_online_data')
            ->select(
                'agent_id',
                DB::raw("SUM(IF(game1 = 1 OR game2 = 1, member_online_count, 0)) AS member_online_count"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(create_time, '%Y/%m/%d %H:00'), '+0:00', '+8:00'), '%Y/%m/%d') AS date"),
                DB::raw("DATE_FORMAT(CONVERT_TZ(FROM_UNIXTIME(create_time, '%Y/%m/%d %H:00'), '+0:00', '+8:00'), '%H:00') AS hour"),
        )->groupBy('agent_id', 'create_time');

        //实时聚合统计
        $query = DB::table(DB::raw("({$sub_query->toSql()}) AS sub"))
            ->select(
                DB::raw('date'),
                DB::raw('agent_id'),
                DB::raw('MAX(IF(HOUR(hour) = 0, member_online_count, 0)) AS h0'),
                DB::raw('MAX(IF(HOUR(hour) = 1, member_online_count, 0)) AS h1'),
                DB::raw('MAX(IF(HOUR(hour) = 2, member_online_count, 0)) AS h2'),
                DB::raw('MAX(IF(HOUR(hour) = 3, member_online_count, 0)) AS h3'),
                DB::raw('MAX(IF(HOUR(hour) = 4, member_online_count, 0)) AS h4'),
                DB::raw('MAX(IF(HOUR(hour) = 5, member_online_count, 0)) AS h5'),
                DB::raw('MAX(IF(HOUR(hour) = 6, member_online_count, 0)) AS h6'),
                DB::raw('MAX(IF(HOUR(hour) = 7, member_online_count, 0)) AS h7'),
                DB::raw('MAX(IF(HOUR(hour) = 8, member_online_count, 0)) AS h8'),
                DB::raw('MAX(IF(HOUR(hour) = 9, member_online_count, 0)) AS h9'),
                DB::raw('MAX(IF(HOUR(hour) = 10, member_online_count, 0)) AS h10'),
                DB::raw('MAX(IF(HOUR(hour) = 11, member_online_count, 0)) AS h11'),
                DB::raw('MAX(IF(HOUR(hour) = 12, member_online_count, 0)) AS h12'),
                DB::raw('MAX(IF(HOUR(hour) = 13, member_online_count, 0)) AS h13'),
                DB::raw('MAX(IF(HOUR(hour) = 14, member_online_count, 0)) AS h14'),
                DB::raw('MAX(IF(HOUR(hour) = 15, member_online_count, 0)) AS h15'),
                DB::raw('MAX(IF(HOUR(hour) = 16, member_online_count, 0)) AS h16'),
                DB::raw('MAX(IF(HOUR(hour) = 17, member_online_count, 0)) AS h17'),
                DB::raw('MAX(IF(HOUR(hour) = 18, member_online_count, 0)) AS h18'),
                DB::raw('MAX(IF(HOUR(hour) = 19, member_online_count, 0)) AS h19'),
                DB::raw('MAX(IF(HOUR(hour) = 20, member_online_count, 0)) AS h20'),
                DB::raw('MAX(IF(HOUR(hour) = 21, member_online_count, 0)) AS h21'),
                DB::raw('MAX(IF(HOUR(hour) = 22, member_online_count, 0)) AS h22'),
                DB::raw('MAX(IF(HOUR(hour) = 23, member_online_count, 0)) AS h23'),
                )
            ->groupBy('date', 'agent_id')
            ->orderBy('date', 'desc');
        $query->get();

        //筛选
        $agent_id and $query->where('agent_id', '=', $agent_id);
        $date_start and $query->where('date', '>=', $date_start);
        $date_end and $query->where('date', '<=', $date_end);
        //分页
        $page   = max(1, ($page ? $page : 1));
        $offset = ($page - 1) * $page_size;
        $query->limit($page_size)->offset($offset);
        //合并绑定参数
        $query->mergeBindings($sub_query);
        //总条数
        $count = $query->get()->count();
        $member_online_data = $query->get()->toArray();
        $member_online_data = json_decode(json_encode($member_online_data),true); //stdClass转数组
        $rows = [
            'total' => $count,
            'lists' => $member_online_data,
        ];
        $total_page = ceil($rows['total'] / $page_size);

        //获取代理信息
        $agents = $this->repo_agent->lists([
            'fields'    => ['id', 'realname'],
            'index'     => 'id',
            'where'     => [
                ['id', '=', sql_in($rows['lists'], 'agent_id')],
            ]])->toArray();

        foreach($rows['lists'] as $k => $v) //格式化数据
        {
            $row_plus = [
                'realname' => $agents[$v['agent_id']]['realname'] ?? '',
            ];
            $rows['lists'][$k] = array_merge($v, $row_plus);
        }

        if(get_action() == 'export_member_online')
        {
            $titles = [
                'realname'  => '渠道',
                'date'      => '日期',
                'h0'        => '00:00',
                'h1'        => '01:00',
                'h2'        => '02:00',
                'h3'        => '03:00',
                'h4'        => '04:00',
                'h5'        => '05:00',
                'h6'        => '06:00',
                'h7'        => '07:00',
                'h8'        => '08:00',
                'h9'        => '09:00',
                'h10'        => '10:00',
                'h11'        => '11:00',
                'h12'        => '12:00',
                'h13'        => '13:00',
                'h14'        => '14:00',
                'h15'        => '15:00',
                'h16'        => '16:00',
                'h17'        => '17:00',
                'h18'        => '18:00',
                'h19'        => '19:00',
                'h20'        => '20:00',
                'h21'        => '21:00',
                'h22'        => '22:00',
                'h23'        => '23:00',
            ];

            $status = $this->serv_util->export_data([
                'page_no'   => $page,
                'rows'      => $rows['lists'],
                'file'      => $request->input('file', ''),
                'fields'    => $request->input('fields', []), //列表所有字段
                'titles'    => $titles, //輸出字段
                'total_page' => $total_page,
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
     * 导出用户在线数据excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export_member_online(Request $request)
    {
        return $this->member_online_list($request);
    }

    /**
     * 获取渠道额度统计记录
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function agent_balance_data(Request $request)
    {
        $agent_id       = $request->input('agent_id', '');
        $is_saturate    = $request->input('is_saturate', ''); //是否饱和
        $page_size  = get_action() == 'export_agent_balance' ? 100 :
            $request->input('page_size', $this->repo_agent_balance_data->page_size);
        $page           = $request->input('page', 1);
        $date_start     = $request->input('date_start', '');
        $date_end       = $request->input('date_end', '');
        $date_start = empty($date_start) ? '2019/01/01' : $date_start;
        $date_end = empty($date_end) ? date('Y/m/d') : $date_end;

        //更新今天数据
        if ($page == 1)
        {
            $this->serv_agent_balance_data->generate_data($this->today);
        }

        $conds = [
            'field'     => DB::raw('DISTINCT date'),
            'fields'    => [
                'date',
                'agent_id',
                'withdraw_amount',
                'agent_balance',
                'remain_balance',
                DB::raw("IFNULL(ROUND(withdraw_amount / agent_balance * 100, 2), 0) AS saturation"), //取四舍五入
            ],
            'agent_id'      => $agent_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'is_saturate'   => $is_saturate,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'order_by'      => ['date', 'desc'],
            'load'          => ['agent_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_agent_balance_data->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size); //总页数

        if(get_action() == 'export_agent_balance')
        {
            $titles = [ //与fields字段匹配后为导出栏目
                'date'              => '日期',
                'realname'          => '渠道',
                'withdraw_amount'   => '取款额度',
                'remain_balance'    => '剩馀额度',
                'agent_balance'     => '总额度',
                'saturation'        => '饱和度',
            ];

            $status = $this->serv_util->export_data([
                'page_no'       => $page,
                'rows'          => $rows['lists'],
                'file'          => $request->input('file', ''),
                'fields'        => $request->input('fields', []), //列表要导出字段
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
     * 导出渠道额度excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export_agent_balance(Request $request)
    {
        return $this->agent_balance_data($request);
    }

    /**
     * 获取用户额度记录
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_balance_data(Request $request)
    {
        $agent_id   = $request->input('agent_id', '');
        $username   = $request->input('username', '');
        $realname   = $request->input('realname', '');
        $page_size  = get_action() == 'export_user_balance' ? 100 :
            $request->input('page_size', $this->repo_order_transfer->page_size);
        $page       = $request->input('page', 1);
        $date_start = $request->input('date_start', '');
        $date_end   = $request->input('date_end', '');
        $date_start = empty($date_start) ? '' :
            date_convert_timestamp("{$date_start} 00:00:00", get_admin_timezone());
        $date_end   = empty($date_end) ? '' :
            date_convert_timestamp("{$date_end} 23:59:59", get_admin_timezone());

        //获取用户id
        $uids = [];
        $user_conds = [];
        $username and $user_conds['username'] = $username;
        $realname and $user_conds['realname'] = $realname;
        if($user_conds)
        {
            $users = $this->repo_user->get_list($user_conds);
            $uids = sql_in($users, 'id');
        }

        $conds = [
            'field'     => DB::raw('DISTINCT uid'),
            'fields'    => [
                DB::raw("FROM_UNIXTIME(pay_time, '%Y/%m/%d') AS date"),
                'agent_id',
                'uid',
                //DB::raw("SUM(IF(type = 1, amount, 0)) AS deposit_amount"),
                DB::raw("SUM(IF(type = 2, amount, 0)) AS withdraw_amount"),
            ],
            'origin'        => [mod_order_transfer::ORIGIN_CLIENT, mod_order_transfer::ORIGIN_ADMIN],
            'uid'           => $uids,
            'agent_id'      => $agent_id,
            'date_start'    => $date_start,
            'date_end'      => $date_end,
            'page_size'     => $page_size, //每页几条
            'page'          => $page, //第几页
            'group_by'      => ['date', 'uid', 'agent_id'],
            'order_by'      => ['date', 'desc'],
            'load'          => ['agent_maps', 'user_maps'],
            'count'         => 1, //是否返回总条数
        ];
        $rows = $this->repo_order_transfer->get_list($conds);
        $total_page = ceil($rows['total'] / $page_size); //总页数

        if(get_action() == 'export_user_balance')
        {
            $titles = [ //与fields字段匹配后为导出栏目
                'date'              => '日期',
                'user_maps.username'    => 'ID',
                'user_maps.realname'    => '用户名',
                'agent_maps.realname'   => '渠道ID',
                'withdraw_amount'       => '个人取款',
            ];

            $status = $this->serv_util->export_data([
                'page_no'       => $page,
                'rows'          => $rows['lists'],
                'file'          => $request->input('file', ''),
                'fields'        => $request->input('fields', []), //列表要导出字段
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
     * 导出用户额度excel
     * @version 1.0.0
     * @param Request $request
     */
    public function export_user_balance(Request $request)
    {
        return $this->user_balance_data($request);
    }
}
