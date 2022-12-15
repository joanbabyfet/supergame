<?php

namespace App\Http\Controllers\api;

use App\Models\mod_order_transfer;
use App\Models\mod_user;
use App\Models\mod_user_login_log;
use App\repositories\repo_app_key;
use App\repositories\repo_user;
use App\repositories\repo_user_login_log;
use App\repositories\repo_wallet;
use App\services\serv_order_transfer;
use App\services\serv_redis;
use App\services\serv_rpc_client;
use App\services\serv_user;
use App\services\serv_wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * 用户控制器
 * Class ctl_user
 * @package App\Http\Controllers\api
 */
class ctl_user extends Controller
{
    private $repo_user;
    private $repo_user_login_log;
    private $serv_user;
    private $repo_app_key;
    private $serv_wallet;
    private $serv_order_transfer;
    private $repo_wallet;
    private $serv_rpc_client;
    private $serv_redis;

    public function __construct(
        repo_user $repo_user,
        repo_user_login_log $repo_user_login_log,
        serv_user $serv_user,
        repo_app_key $repo_app_key,
        serv_wallet $serv_wallet,
        serv_order_transfer $serv_order_transfer,
        repo_wallet $repo_wallet,
        serv_rpc_client $serv_rpc_client,
        serv_redis $serv_redis
    )
    {
        parent::__construct();
        $this->repo_user = $repo_user;
        $this->repo_user_login_log = $repo_user_login_log;
        $this->serv_user = $serv_user;
        $this->repo_app_key = $repo_app_key;
        $this->serv_wallet = $serv_wallet;
        $this->serv_order_transfer = $serv_order_transfer;
        $this->repo_wallet = $repo_wallet;
        $this->serv_rpc_client = $serv_rpc_client;
        $this->serv_redis = $serv_redis;
    }

    /**
     * 登入
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $credentials                = $request->only(['username']);
        $credentials['password']    = $credentials['username'];
        $credentials['status']      = 1; //已激活
        $mobile                     = $request->input('mobile', 1); //h5或pc端, 默认h5

        if (!$token = auth($this->guard)->attempt($credentials))
        {
            return res_error(trans('api.api_login_pass_incorrect'), -2);
        }

        //根据token获取用户信息,jwt后台不需要保存Token
        $user = auth($this->guard)->authenticate($token)->toArray();

        //绑定token到uid, 游戏登入验证用, 与JWT_REFRESH_TTL一致默认14天
        $this->repo_user->bind_token_uid($token, $user['id'], 14 * 86400);

        //写入登录日志
//        $this->repo_user_login_log->save([
//            'uid'           => $user['id'],
//            'username'      => $user['username'],
//            'status'        => mod_user_login_log::ENABLE, //登入成功
//        ]);

        $login_ip = request()->ip();
        $this->serv_rpc_client->create_login_log([
            'uid'           => $user['id'],
            'username'      => $user['username'],
            'session_id'    => Session::getId(),
            'agent'         => request()->userAgent(),
            'login_time'    => time(),
            'login_ip'      => $login_ip,
            'login_country' => ip2country($login_ip),
            'exit_time'     => 0,
            'extra_info'    => '',
            'status'        => mod_user_login_log::ENABLE, //登入成功
            'cli_hash'      => md5($user['username'].'-'.$login_ip),
        ]);

        //检测是否为新增用户
        $is_new_user = $this->serv_user->is_new_user($user['id']);

        //更新登入时间与ip
        $login_ip = $request->ip();
        $session_id = Session::getId();
        $login_time = time();
        $login_country = ip2country($login_ip);
        $this->repo_user->update([
            'is_new_user'       => $is_new_user, //新增用户
            'session_id'        => $session_id, //web场景使用
            'login_time'        => $login_time,
            'login_ip'          => $login_ip,
            'login_country'     => $login_country,
        ], ['id' => $user['id']]);

        //更新缓存里面的信息, 与JWT_REFRESH_TTL一致默认14天
        $user['session_id']     = $session_id;
        $user['login_time']     = $login_time;
        $user['login_ip']       = $login_ip;
        $user['login_country']  = $login_country;
        //扩充字段
        $user['wallet_id']      = $this->repo_wallet->get_field_value([
            'fields'    => ['id'],
            'where' => [['holder_id', '=', $user['id']]]
        ]);
        $this->repo_user->set_cache($user, $user['id'], 14 * 86400);

        //组装游戏地址, 默认有效时间 10分钟
        $url = $mobile ? sprintf(config('global.game_url.h5'), $token) :
            sprintf(config('global.game_url.pc'), $token);

        $data = [
            'url'   => $url
        ];
        return res_success($data, trans('api.api_login_success'));
    }

    /**
     * 强迫登出玩家
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $username = $request->input('username', '');
        if(empty($username))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //根据用户帐号获取用户信息
        $user = $this->repo_user->get_user_by_username($username);
        if(!empty($user))
        {
            //通过uid获取该用户令牌
            $token = $this->repo_user->get_token($user['id']);
            //将该用户令牌放入黑名单, 主动让其失效
            $token and JWTAuth::setToken($token)->invalidate();
            //删除用户缓存
            $this->repo_user->del_cache($user['id']);
            //解绑某个token
            $this->repo_user->unbind_token_uid($user['id']);
            //干掉用户session_id, web场景使用
            $this->repo_user->update(['session_id' => ''], ['id' => $user['id']]);
        }
        return res_success([], trans('api.api_logout_success'));
    }

    /**
     * 注册
     * @version 1.0.0
     * @param Request $request
     */
    public function register(Request $request)
    {
        $app_id = $request->input('app_id');
        //$origin = $request->input('origin'); //注册来源
        $origin = is_mobile() ? mod_user::ORIGIN_H5 : mod_user::ORIGIN_PC;
        $username = $request->input('username', ''); //玩家帐号
        $nickname = $request->input('nickname', ''); //玩家昵称(选填)

        //根据应用id获取代理信息
        $row = $this->repo_app_key->find([
            'where' => [
                ['app_id', '=', $app_id]
            ]
        ]);
        $row and $row = $row->load('agent_maps'); //加载代理信息
        $row = empty($row) ? [] : $row->toArray();

        //玩家帐号加代理帐号前缀防止重复
        $username = $this->repo_user->get_prefix_account(
            $row['agent_maps']['username'], $username);

        DB::beginTransaction(); //开启事务, 保持数据一致
        $status = $this->repo_user->save([
            'do'            => 'add',
            'agent_id'      => $row['agent_id'] ?? '',
            'origin'        => $origin, //注册来源 1=H5 2=PC 3=安卓 4=IOS
            'username'      => $username,
            'password'      => $username,
            'realname'      => empty($nickname) ? $username : $nickname,
            'role_id'       => config('global.role_general_member'),
            'currency'      => $row['agent_maps']['currency'] ?? config('global.currency')
        ], $ret_data);
        if($status < 0)
        {
            DB::rollBack(); //手動回滚事务
            $status == -2 and $data = ['username' => $username]; //玩家帐号
            return res_error($this->repo_user->get_err_msg($status), $status, $data ?? []);
        }
        DB::commit(); //手動提交事务

        //获取用户信息
        $user = $this->repo_user->find([
            'where' => [
                ['id', '=', $ret_data['id']]
            ]
        ]);

        $data = [
            'username' => $user['username'], //玩家帐号
            'currency' => $user['currency'], //玩家币种
        ];
        return res_success($data, trans('api.api_add_success'));
    }

    /**
     * 登刷新认证token
     * 例如 token 有效时间为 60 分钟，刷新时间为 20160 分钟，在 60 分钟内可以通过这个 token 获取新 token，
     * 但是超过 60 分钟是不可以的，然后你可以一直循环获取，直到总时间超过 20160 分钟，不能再获取
     * @version 1.0.0
     * @return mixed
     */
//    public function refresh_token()
//    {
//        $token = auth($this->guard)->refresh();
//
//        //根据token获取用户信息,jwt后台不需要保存Token
//        $user = auth($this->guard)->authenticate($token)->toArray();
//        $jwt_ttl = auth($this->guard)->factory()->getTTL(); //單位:分鐘
//        $api_token_expire = strtotime("+{$jwt_ttl} minutes", time());
//
//        $data = [
//            'uid'           => $user['id'],
//            'access_token'  => $token, //登录token
//            'token_type'    => 'bearer',
//            'token_expire'  => $api_token_expire //token过期时间戳
//        ];
//        return res_success($data);
//    }

    /**
     * 获取用户信息
     * @version 1.0.0
     * @return mixed
     */
//    public function detail()
//    {
//        $user = auth($this->guard)->user();
//        $user and $user = $user->load('role_maps'); //加载角色
//        $user = empty($user) ? [] : $user->toArray();
//        return res_success($user);
//    }

    /**
     * 更新语言
     * @version 1.0.0
     * @return mixed
     */
//    public function change_lang(Request $request)
//    {
//        $lang   = $request->input('lang', '');
//        $uid    = $request->user()->id;
//
//        if (!array_key_exists($lang, config('global.lang_map')))
//        {
//            return res_invalid_params();
//        }
//
//        //更新该用户语言
//        $status = $this->repo_user->update([
//            'language'        => $lang,
//            'update_time'     => time(),
//        ], ['id' => $uid]);
//        if($status < 0)
//        {
//            return res_error($this->repo_user->get_err_msg($status), $status);
//        }
//        app()->setLocale($lang); //设置语言
//
//        return res_success([], trans('api.api_update_success'));
//    }

    /**
     * 检查账号是否已被注册
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
//    public function account_is_registered(Request $request)
//    {
//        $account   = $request->input('account', '');
//
//        $status = $this->serv_user->is_registered([
//            'account' => $account
//        ], $ret_data);
//        if($status < 0)
//        {
//            return res_error($this->serv_user->get_err_msg($status), $status);
//        }
//
//        $lang_key = $ret_data ? 'api.api_account_registered' : 'api.api_account_not_registered';
//        $data = [
//            'is_registered' => $ret_data ? 1 : 0, //是否已注册
//        ];
//        return res_success($data, trans($lang_key));
//    }

    /**
     * 玩家充值
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function deposit(Request $request)
    {
        $username       = $request->input('username', '');
        $amount         = $request->input('amount', 0);
        $transaction_id = $request->input('transaction_id', '');
        if(empty($username) || empty($amount) || empty($transaction_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //根据帐号获取用户信息
        $user = $this->repo_user->get_user_by_username($username);
        if(empty($user))
        {
            return res_error('玩家不存在', -2);
        }

        //创建订单流程
        //防止并发
        $lock_name = "deposit_{$username}";
        if ($this->serv_redis->lock($lock_name, 3))
        {
            $status = $this->serv_order_transfer->create([
                'origin'            => mod_order_transfer::ORIGIN_CLIENT, //玩家下单
                'uid'               => $user['id'],
                'agent_id'          => $user['agent_id'],
                'transaction_id'    => $transaction_id,
                'type'              => mod_order_transfer::DEPOSIT,
                'amount'            => $amount,
                'currency'          => $user['currency'],
            ], $ret_data);

            $this->serv_redis->unlock($lock_name);
        }
        if($status < 0)
        {
            return res_error($this->serv_order_transfer->get_err_msg($status), $status);
        }
        return res_success($ret_data, trans('api.api_submit_success'));
    }

    /**
     * 玩家提款
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function withdraw(Request $request)
    {
        $username = $request->input('username', '');
        $amount = $request->input('amount', 0);
        $transaction_id = $request->input('transaction_id', '');
        if(empty($username) || empty($amount) || empty($transaction_id))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //根据帐号获取用户信息
        $user = $this->repo_user->get_user_by_username($username);
        if(empty($user))
        {
            return res_error('玩家不存在', -2);
        }

        //创建订单流程
        //防止并发
        $lock_name = "withdraw_{$username}";
        if ($this->serv_redis->lock($lock_name, 3))
        {
            $status = $this->serv_order_transfer->create([
                'origin'            => mod_order_transfer::ORIGIN_CLIENT, //玩家下单
                'uid'               => $user['id'],
                'agent_id'          => $user['agent_id'],
                'transaction_id'    => $transaction_id,
                'type'              => mod_order_transfer::WITHDRAW,
                'amount'            => $amount,
                'currency'          => $user['currency'],
            ], $ret_data);

            $this->serv_redis->unlock($lock_name);
        }
        if($status < 0)
        {
            return res_error($this->serv_order_transfer->get_err_msg($status), $status);
        }
        return res_success($ret_data, trans('api.api_submit_success'));
    }

    /**
     * 获取玩家馀额, 若redis与库不一玫则以redis为基准
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_balance(Request $request)
    {
        $username = $request->input('username', '');
        if(empty($username))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //获取用户信息
        $user = $this->repo_user->get_user_by_username($username);
        if(empty($user))
        {
            return res_error('玩家不存在', -1);
        }

        //获取用户馀额
        $balance = $this->serv_wallet->get_balance($user['id']);

        $data = [
            'balance'   => money($balance, ''), //金额统一返回字符串
            'currency'  => $user['currency']
        ];
        return res_success($data);
    }

    /**
     * 查询玩家是否在线
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function is_online(Request $request)
    {
        $username = $request->input('username', '');
        if(empty($username))
        {
            return res_error(trans('api.api_param_error'), -1);
        }

        //获取用户信息
        $user = $this->repo_user->get_user_by_username($username);
        if(empty($user)) { //不存在
            $status = -1;
        }
        elseif($user['status'] == 0) { //封禁
            $status = 2;
        }
        elseif($user['online'] == 1) { //在线
            $status = 1;
        }
        elseif($user['online'] == 0) { //不在线
            $status = 0;
        }

        $data = [
            'status'   => $status, //玩家状态 -1=不存在 0=不在线 1=在线= 2=封禁
        ];
        return res_success($data);
    }
}
