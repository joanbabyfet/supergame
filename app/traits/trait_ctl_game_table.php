<?php


namespace App\traits;


use App\Models\mod_game_table;
use Carbon\Carbon;
use Illuminate\Http\Request;

trait trait_ctl_game_table
{
    /**
     * 获取桌子详情
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail(Request $request)
    {
        return $this->_detail($request);
    }

    /**
     * 获取牌桌记录详情
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history_detail(Request $request)
    {
        return $this->_detail($request);
    }

    /**
     * 共用详情
     * @param Request $request
     * @return mixed
     */
    private function _detail(Request $request)
    {
        $id = $request->input('id'); //桌子id
        if(empty($id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }
        $row = $this->repo_game_table->find(['where' => [['id', '=', $id]]]);
        if (empty($row))
        {
            return res_error('该桌号不存在');
        }
        $row = $row->toArray();

        //桌子类型
        $row['type_text'] = array_key_exists($row['type'], mod_game_table::$type_map) ?
            mod_game_table::$type_map[$row['type']] : '';
        //渠道名称
        $agent_info = $this->repo_agent->find(['where' => [
            ['id', '=', $row['agent_id']],
            ['pid', '=', '0']
        ]]);
        $row['agent_name'] = $agent_info['realname'];
        //开始时间
        $row['start_time_text'] = empty($row['start_time']) ? '' : Carbon::createFromTimestamp($row['start_time'])->format('Y-m-d H:i');
        //结束时间
        $row['end_time_text'] = empty($row['end_time']) ? '' : Carbon::createFromTimestamp($row['end_time'])->format('Y-m-d H:i');
        //游戏时长
        $row['duration_text'] = second2time(time() - $row['start_time']);
        //桌主
        $user_info = $this->repo_user->find(['where' => [
            ['id', '=', $row['uid']]
        ]]);
        $row['table_owner_text'] = $user_info['realname'];

        //获取桌子总输赢
        $id and $table_winloss = $this->repo_winloss->get_table_winloss_list([
            'table_id'  => [$id], //获取桌子id
        ]);

        $total_platform_commission = $table_winloss[$id]['total_platform_commission'] ?? 0;
        $total_gz_amount = $table_winloss[$id]['total_gz_amount'] ?? 0;
        $total_income = $total_platform_commission + $total_gz_amount;
        $total_table_owner_commission = $table_winloss[$id]['total_table_owner_commission'] ?? 0;
        $member_count = $table_winloss[$id]['member_count'] ?? 0;

        //累计用户(去重)
        $row['member_count'] = $member_count;
        //平台总收入
        $row['total_income'] = money($total_income, '');
        //当前平台总抽水
        $row['total_platform_commission'] = $total_platform_commission;
        //当前公庄总输赢
        $row['total_gz_amount'] = $total_gz_amount;
        //桌主抽水
        $row['total_table_owner_commission'] = $total_table_owner_commission;

        return res_success($row);
    }
}
