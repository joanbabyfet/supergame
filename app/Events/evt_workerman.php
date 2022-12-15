<?php

namespace App\Events;

use App\services\serv_wk_gateway;
use GatewayWorker\Lib\Gateway;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

/**
 * 监听处理 workerman 各种事件, 主要处理 onConnect onMessage onClose 三个方法
 * workerman的长链接服务器, 只作消息的推送处理, 不要把所有业务逻辑都写在该事件类回调中
 * Class evt_workerman
 * @package App\Events
 */
class evt_workerman
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * 当businessWorker进程启动时触发。每个进程生命周期内都只会触发一次
     * @param $businessWorker
     */
    public function onWorkerStart($businessWorker)
    {

    }

    /**
     * 当客户端连接时触发
     * @param $client_id
     */
    public function onConnect($client_id)
    {
        //记录日志
        logger(__METHOD__, [
            'client_id' => $client_id,
            'session'   => $_SESSION,
        ]);
    }

    /**
     * 当客户端连接WebSocket时触发
     * @param $client_id
     * @param $data
     */
    public function onWebSocketConnect($client_id, $data)
    {

    }

    /**
     * 客户端送来消息时
     * @param $client_id
     * @param $data
     */
    public function onMessage($client_id, $data)
    {
        if (!is_array($data) && $data === '~H#C~') //心跳包直接返回
        {
            return Gateway::sendToCurrentClient('~H#S~');
        }

        $data = is_array($data) ? $data : json_decode($data, true);

        $start_timestamp = time();
        $config = config('global.socket');
        $gateway_port   = $_SERVER['GATEWAY_PORT'];
        $ip   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';

        $tcp_gateway_map = [ //端口控制器映射表实, 避免所有业务写在单一文件上
            $config['admin_gateway_port']       => 'admin',
            $config['agent_gateway_port']       => 'agent',
        ];

        //正常tcp
        $req_data = [];
        if (!empty($tcp_gateway_map[$gateway_port]))
        {
            //必填参数
            foreach (['action', 'token'] as $_f)
            {
                if (empty($data[$_f]))
                {
                    return Gateway::sendToClient($client_id, 'invalid request, received->'.$data);
                }
            }

            $req_data   = [
                'client_id' => $client_id,
                'action'    => empty($data['action']) ? '' : $data['action'],
                'token'     => empty($data['token']) ? '' : $data['token'],
                'data'      => empty($data['data']) ? [] : $data['data']
            ];
            $_SESSION['ct'] = $req_data['ct'] = $tcp_gateway_map[$gateway_port];
        }
        else
        {
            return Gateway::sendToClient($client_id, 'invalid request');
        }

        $req_data['client_id'] = $client_id;
        $ctl = empty($req_data['ct']) ? '' : 'ctl_'.$req_data['ct'];
        $request = new Request($req_data);//发送数据
        $controller = app()->make("App\Http\Controllers\socket\\".$ctl); //從容器解析型別
        //调用请求接口,先到主入口
        app()->call([$controller, 'handle'], [
            'request' => $request
        ]);

        //记录日志
        logger(__METHOD__, [
            'action'        => 'finish',
            'client_id'     => $client_id,
            'client_ip'     => $ip,
            'req_data'      => json_encode($req_data, JSON_UNESCAPED_UNICODE),
            'done_seconds'  => time() - $start_timestamp,
            'timestamp'     => time()
        ]);
    }

    /**
     * 当客户端断开连接时
     * @param $client_id
     */
    public function onClose($client_id)
    {
        if(empty($_SESSION['uid'])) {
            $data = [
                'no_say_hi' => true,
                'client_id' => $client_id,
                'session'   => $_SESSION,
            ];
        }
        else {
            $data = [
                'client_id' => $client_id,
                'session'   => $_SESSION,
            ];
        }
        //记录日志
        logger(__METHOD__, $data);

        //干掉client_id
        app(serv_wk_gateway::class)->del_clientid($client_id);
    }
}
