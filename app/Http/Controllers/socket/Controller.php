<?php

namespace App\Http\Controllers\socket;

use App\lib\response;
use App\repositories\repo_config;
use App\services\serv_socket;
use App\services\serv_wk_gateway;
use GatewayWorker\Lib\Gateway;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

/**
 * 父控制器
 * Class Controller
 * @package App\Http\Controllers\admin
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $action     = '';
    protected $token      = '';
    protected $client_id  = '';
    protected $ct         = '';
    protected $uid        = '';
    protected $user       = [];
    protected $guard      = '';

    public function __construct()
    {

    }

    //主入口
    public function handle(Request $request)
    {
        $client_id          = $request->input('client_id', '');
        $action             = $request->input('action', ''); //类型
        $token              = $request->input('token', '');
        $data               = $request->input('data', []);
        $ct                 = $request->input('ct', '');
        $this->ct           = $ct;
        $this->guard        = $ct;
        $this->action       = $action;
        //$this->token        = $token;
        $this->client_id    = $client_id;

        //系统是否维护中
        $sys_in_maintenance = app(repo_config::class)->get('sys_in_maintenance', [
            'type' => 'int', 'default' => 0, 'group' => 'config'
        ]);
        if ($sys_in_maintenance !== 0)
        {
            return $this->error(trans('api.api_in_maintenance'), response::IN_MAINTENANCE);
        }

        if (empty($action) || empty($token) || empty($client_id))
        {
            return null;
        }

        $uid = $this->get_uid_by_token($token);
        $this->uid = $uid;

        //记录日志
        logger(__METHOD__, [
            'action'    => $action,
            'token'     => $token,
            'client_id' => $client_id,
            'uid'       => $uid,
            'data'      => $data
        ]);

        if (empty($uid)) //检测是否登入
        {
            return $this->error('未登录，请先登录', response::TOKEN_AUTH_FAIL);
        }

        //根据类型执行不同的业务
        $method = 'action_'.$action;
        if (method_exists($this, $method))
        {
            $res = app()->call([$this, $method], [
                'request' => $request
            ]);
            return $res;
        }
    }

    /**
     * 客户端第一次连接返回信息
     * @return int|mixed
     */
    public function action_say_hi()
    {
        //$old_client_id = Gateway::getClientIdByUid($this->uid); //挤掉之前登录的账号（h5版本允许多端链接）
        $old_client_id = app(serv_wk_gateway::class)->get_clientid_by_uid($this->uid);

        Gateway::bindUid($this->client_id, $this->uid); //绑定用户到链接

        //绑定用户到链接
        app(serv_wk_gateway::class)->bind_uid($this->client_id, $this->uid);

        $res = $this->success();

        //发送离线消息
        $this->send_offline_msg();

        $_SESSION['uid'] = [
            'ct'            => $this->ct,
            'old_client_id' => $old_client_id,
            'new_client_id' => $this->client_id,
            'uid'           => $this->uid
        ];
        return $res;
    }

    /**
     * 成功返回
     * @param array $data
     * @param string $msg
     * @param int $code
     * @return int|mixed
     */
    public function success($data = [], $msg = 'success', $code = response::SUCCESS)
    {
        return $this->send($this->client_id, $this->action, $code, $msg, $data);
    }

    /**
     * 失败返回
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return int|mixed
     */
    public function error($msg = 'error', $code = response::FAIL, $data = [])
    {
        return $this->send($this->client_id, $this->action, $code, $msg, $data);
    }

    /**
     * 发送数据
     * @param $client_id
     * @param $action
     * @param $code
     * @param string $msg
     * @param array $data
     * @return int|mixed
     */
    public function send($client_id, $action, $code, $msg='', $data=[])
    {
        return app(serv_socket::class)->send([
            'type'          => $this->ct, //admin=运营后台, agent=代理后台
            'uid'           => $this->uid,
            'client_id'     => $client_id,
            'action'        => $action,
            'code'          => $code,
            'msg'           => $msg,
            'data'          => $data
        ]);
    }

    /**
     * 发送离线消息
     */
    public function send_offline_msg()
    {
        echo '+++++++++++++++++++++++++++++++' . "\n"; //每个消息后加"\n"代表换行
    }

    /**
     * 打印日志在console上, 调试用
     * @param $msg
     * @param string $context
     */
    public function log($msg, $context='')
    {
        $time = time();
        echo "{$context} $msg {$time} \n"; //每个消息后加"\n"代表换行
    }
}
