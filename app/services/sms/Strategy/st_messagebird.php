<?php


namespace App\services\sms\Strategy;

use App\repositories\repo_sms_send_log;
use App\services\sms\st_sms;

/**
 * messagebird 短信具体策略类, 不要在里面定义构造函数
 * Class st_messagebird
 * @package App\services\sms\Strategy
 */
class st_messagebird implements st_sms
{
    /**
     * 发送短信/消息
     * @param $phone
     * @param string $content
     * @return bool true表示发送成功 false发送失败
     * @throws \Throwable
     */
    public function send_msg($phone, $content = '')
    {
        //检验手机号码
        if (preg_match('/^[+0-9]{8,14}$/', $phone) == false)
        {
            return false;
        }

        $status = 1;
        try
        {
            //调试状态下，只记录不发送
            if (config('app.debug'))
            {
                //插入日志
                app(repo_sms_send_log::class)->save([
                    'phone'     => $phone,
                    'content'   => $content,
                    'send_time' => time(),
                    'result'    => 'debug'
                ]);
            }
            else
            {
                //smsmkt 泰国短信商
//                $api_url = config('global.smsmkt.url');
//                $header = [
//                    'Content-Type'  => 'application/json',
//                    'api_key'       => config('global.smsmkt.api_key'),
//                    'secret_key'    => config('global.smsmkt.secret_key'),
//                ];
//                $data = [
//                    'sender'    => config('global.smsmkt.origin'),
//                    'phone'     => $phone,
//                    'message'   => $content,
//                ];
//                $res  = api_post($api_url, $data, $header);

                $messageBird            = new \MessageBird\Client(config('global.messagebird.app_key'));
                $message                = new \MessageBird\Objects\Message;
                $message->originator    = config('global.messagebird.origin');
                $message->type          = 'sms';
                $message->datacoding    = 'unicode';
                $message->recipients    = [$phone]; //不加+也能收到, 格式 85586207239
                $message->body          = $content;
                $res                    = $messageBird->messages->create($message);
                if($res)
                {
                    //插入日志
                    app(repo_sms_send_log::class)->save([
                        'phone'     => $phone,
                        'content'   => $content,
                        'send_time' => time(),
                        'result'    => is_array($res) || is_object($res) ? htmlspecialchars(json_encode($res, JSON_UNESCAPED_UNICODE), ENT_QUOTES) : $res,
                        'req_data'  => json_encode(request()->all(), JSON_UNESCAPED_UNICODE)
                    ]);
                }
            }
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //插入日志
            app(repo_sms_send_log::class)->save([
                'phone'     => $phone,
                'content'   => $content,
                'send_time' => time(),
                'result'    => $e->getMessage(),
            ]);
        }
        return $status;
    }
}
