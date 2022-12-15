<?php

namespace App\Http\Controllers\admin;

use App\services\serv_telegram;
use Illuminate\Http\Request;

class ctl_telegram extends Controller
{
    private $serv_telegram;

    public function __construct(serv_telegram $serv_telegram)
    {
        parent::__construct();
        $this->serv_telegram = $serv_telegram;
    }

    /**
     * 设置回调地址
     * @version 1.0.0
     * @return mixed
     */
    public function set_webhook()
    {
        $status = $this->serv_telegram->set_webhook($ret_data);
        if($status < 0)
        {
            return res_error($this->serv_telegram->get_err_msg($status), $status);
        }
        return res_success($ret_data);
    }

    /**
     * 删除回调地址
     * @version 1.0.0
     * @return mixed
     */
    public function delete_webhook()
    {
        $status = $this->serv_telegram->delete_webhook($ret_data);
        if($status < 0)
        {
            return res_error($this->serv_telegram->get_err_msg($status), $status);
        }
        return res_success($ret_data);
    }

    /**
     * 获取当前回调信息
     * @version 1.0.0
     * @return mixed
     */
    public function get_webhook_info()
    {
        $status = $this->serv_telegram->get_webhook_info($ret_data);
        if($status < 0)
        {
            return res_error($this->serv_telegram->get_err_msg($status), $status);
        }
        return res_success($ret_data);
    }

    /**
     * 获取当前机器人信息
     * @version 1.0.0
     * @return mixed
     */
    public function get_me()
    {
        $status = $this->serv_telegram->get_me($ret_data);
        if($status < 0)
        {
            return res_error($this->serv_telegram->get_err_msg($status), $status);
        }
        return res_success($ret_data);
    }

    /**
     * 当bot接收到信息就会回调该方法, 不能有需要授权的中间件
     * @version 1.0.0
     * @return string
     */
    public function webhook()
    {
        $token = config('global.telegram_bot.token');
        $content = file_get_contents('php://input'); //获取bot送来的json字符串
        $data = json_decode($content, true);
        $chat_id = $data['message']['chat']['id'];
        $reply = $data['message']['text'];

        //回声机器人 send reply
        $send_to    = 'https://api.telegram.org/bot' . $token . '/sendMessage?chat_id='.$chat_id.'&text='.$reply;
        file_get_contents($send_to);
    }

    /**
     * 发送消息
     * @version 1.0.0
     * @return mixed
     */
    public function send(Request $request)
    {
        $chat_id    = $request->input('chat_id', '');
        $text       = $request->input('text', '');

        $status = $this->serv_telegram->send([
            'chat_id'   => $chat_id,    //接收消息的人或者群的id
            'text'      => $text,       //消息内容
        ]);
        if($status < 0)
        {
            return res_error($this->serv_telegram->get_err_msg($status), $status);
        }
        return res_success();
    }
}
