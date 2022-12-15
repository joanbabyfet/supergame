<?php

namespace App\Http\Controllers\adminag;

use App\Models\mod_agent;
use App\Models\mod_user_login_log;
use App\repositories\repo_agent;
use App\repositories\repo_agent_login_log;
use App\repositories\repo_agent_oplog;
use App\services\serv_menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ctl_index extends Controller
{
    private $repo_agent;
    private $repo_agent_login_log;
    private $repo_agent_oplog;
    private $serv_menu;
    private $module_id;

    public function __construct(
        repo_agent $repo_agent,
        repo_agent_login_log $repo_agent_login_log,
        repo_agent_oplog $repo_agent_oplog,
        serv_menu $serv_menu
    )
    {
        parent::__construct();
        $this->repo_agent = $repo_agent;
        $this->repo_agent_login_log = $repo_agent_login_log;
        $this->repo_agent_oplog = $repo_agent_oplog;
        $this->serv_menu = $serv_menu;
        $this->module_id = 27;
    }

    /**
     * 主入口
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {

    }

    /**
     * 登入
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['username', 'password']);
        $credentials['status'] = 1; //已激活
        $captcha    = $request->input('captcha');
        $key        = $request->input('key');
        config('app.debug') and $key = '123123'; //调试状态下使用测试验证码123123

        if((config('app.debug') && $captcha != '123123') ||
            (!config('app.debug') && !captcha_api_check($captcha, $key))) //检测图片验证码
        {
            return res_error(trans('api.api_img_captcha_error'), -1);
        }

        //获取用户信息
        $agent = $this->repo_agent->find(['where' => [['username', '=', $credentials['username']]]]);
        if(empty($agent)) //帐号不存在
        {
            //写入登录日志
            $this->repo_agent_login_log->save([
                'username'      => $credentials['username'],
                'status'        => mod_user_login_log::DISABLE, //登入失败
            ]);
            return res_error(trans('api.api_login_pass_incorrect'), -2);
        }

        if($agent->pid) //子帐号需检测主帐号状态
        {
            $master_agent = $this->repo_agent->find(['where' => [['id', '=', $agent->pid]]]);
            if($master_agent->status === mod_agent::DISABLE) //检测主帐号是否禁用
            {
                return res_error(trans('api.api_login_account_disabled'), -3);
            }
        }

        if($agent->status === mod_agent::DISABLE) //检测帐号是否禁用
        {
            return res_error(trans('api.api_login_account_disabled'), -4);
        }

        if (!$token = auth($this->guard)->attempt($credentials)) //检测帐密是否正确
        {
            //写入登录日志
            $this->repo_agent_login_log->save([
                'uid'           => $agent['id'],
                'username'      => $agent['username'],
                'status'        => mod_user_login_log::DISABLE, //登入失败
            ]);
            return res_error(trans('api.api_login_pass_incorrect'), -5);
        }

        //根据token获取代理信息,jwt后台不需要保存Token
        $user = auth($this->guard)->authenticate($token)->toArray();
        $jwt_ttl = auth($this->guard)->factory()->getTTL(); //單位:分鐘
        $api_token_expire = strtotime("+{$jwt_ttl} minutes", time());

        //绑定token到uid, websocket登入验证用, 与JWT_REFRESH_TTL一致默认14天
        $this->repo_agent->bind_token_uid($token, $user['id'], 14 * 86400);

        //更新登入时间与ip
        $login_ip = $request->ip();
        $session_id = Session::getId();
        $login_time = time();
        $login_country = ip2country($login_ip);
        $this->repo_agent->update([
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
        $this->repo_agent->set_cache($user, $user['id'], 14 * 86400);

        //写入登录日志
        $this->repo_agent_login_log->save([
            'uid'           => $user['id'],
            'username'      => $user['username'],
            'status'        => mod_user_login_log::ENABLE, //登入成功
        ]);

        $data = array_merge($user, [
            'access_token' => $token, //登录token
            'token_type' => 'bearer',
            'token_expire' => $api_token_expire //token过期时间戳
        ]);
        return res_success($data, trans('api.api_login_success'));
    }

    /**
     * 登出
     * @return mixed
     */
    public function logout()
    {
        $uid = defined('AUTH_UID') ? AUTH_UID : '';
        $token = request()->bearerToken();

        //干掉用户信息缓存
        $this->repo_agent->del_cache($uid);
        //干掉token缓存
        $token = $token ? $token : $this->repo_agent->get_token_by_uid($uid);
        $this->repo_agent->unbind_token_uid($token, $uid);

        auth($this->guard)->logout();
        return res_success([], trans('api.api_logout_success'));
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
//        //根据token获取代理信息,jwt后台不需要保存Token
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
     * 获取代理信息
     * @version 1.0.0
     * @return mixed
     */
    public function detail()
    {
        $user = auth($this->guard)->user();
        $user and $user = $user->load('role_maps'); //加载角色
        $user = empty($user) ? [] : $user->toArray();
        return res_success($user);
    }

    /**
     * 修改用户自己密码
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit_pwd(Request $request)
    {
        $id             = $request->input('id', '');
        $old_password   = $request->input('old_password', '');
        $password       = $request->input('password', '');

        $status = $this->repo_agent->edit_pwd([
            'id'            => $id,
            'old_password'  => $old_password,
            'password'      => $password
        ]);
        if($status < 0)
        {
            return res_error($this->repo_agent->get_err_msg($status), $status);
        }
        //寫入日志
        $this->repo_agent_oplog->add_log("修改密码 {$id}", $this->module_id);

        return res_success([], trans('api.api_update_success'));
    }

    /**
     * 获取后台菜单
     * @version 1.0.0
     * @param Request $request
     * @return mixed
     */
    public function get_menu(Request $request)
    {
        $purviews = get_purviews([ //获取当前用户权限，返回路由名称
            'guard' => $this->guard,
            'field' => 'name'
        ]);

        $rows = $this->serv_menu->get_menu_data([
            'guard'         => config('global.adminag.guard'),
            'purviews'      => $purviews,
            'is_permission' => 1,
            'order_by'      => ['sort', 'asc']
        ]);
        return res_success($rows);
    }
}
