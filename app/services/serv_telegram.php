<?php


namespace App\services;


use App\traits\trait_service_base;

class serv_telegram
{
    use trait_service_base;

    private $token;
    private $callback_url;

    public function __construct()
    {
        $this->token          = config('global.telegram_bot.token');
        $this->callback_url   = config('global.telegram_bot.callback_url');
    }

    /**
     * 设置回调地址
     * @param array $ret_data
     * @return int|mixed
     */
    public function set_webhook(&$ret_data = [])
    {
        $status = 1;
        try
        {
            $url    = 'https://api.telegram.org/bot' . $this->token . '/setWebhook';
            $param = [
                'url' => $this->callback_url
            ];
            $res = api_post($url, $param);
            $ret = json_decode($res['body'], true);
            if($ret['ok'] === false)
            {
                $this->exception('error', -1);
            }
            $ret_data = $ret;
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }

    /**
     * 删除回调地址
     * @param array $ret_data
     * @return int|mixed
     */
    public function delete_webhook(&$ret_data = [])
    {
        $status = 1;
        try
        {
            $url    = 'https://api.telegram.org/bot' . $this->token . '/deleteWebhook';
            $param = [
                'url' => $this->callback_url
            ];
            $res = api_post($url, $param);
            $ret = json_decode($res['body'], true);
            if($ret['ok'] === false)
            {
                $this->exception('error', -1);
            }
            $ret_data = $ret;
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }

    /**
     * 获取当前回调信息
     * @param array $ret_data
     * @return int|mixed
     */
    public function get_webhook_info(&$ret_data = [])
    {
        $status = 1;
        try
        {
            $url    = 'https://api.telegram.org/bot' . $this->token . '/getWebhookInfo';
            $param = [
                'url' => $this->callback_url
            ];
            $res = api_post($url, $param);
            $ret = json_decode($res['body'], true);
            if($ret['ok'] === false)
            {
                $this->exception('error', -1);
            }
            $ret_data = $ret;
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }

    /**
     * 获取当前机器人信息
     * @param array $ret_data
     * @return int|mixed
     */
    public function get_me(&$ret_data = [])
    {
        $status = 1;
        try
        {
            $url    = 'https://api.telegram.org/bot' . $this->token . '/getMe';
            $param = [
                'url' => $this->callback_url
            ];
            $res = api_post($url, $param);
            $ret = json_decode($res['body'], true);
            if($ret['ok'] === false)
            {
                $this->exception('error', -1);
            }
            $ret_data = $ret;
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }

    /**
     * 发送消息
     * @param array $data
     * @return int|mixed
     */
    public function send(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'chat_id'   => 'required', //接收消息的人或者群的id 例message.chat.id
            'text'      => 'required', //消息内容
        ], $data);

        $status = 1;
        try
        {
            $url    = 'https://api.telegram.org/bot' . $this->token . '/sendMessage';
            $param = [
                'chat_id'   => $data_filter['chat_id'],
                'text'      => $data_filter['text'],
            ];
            $res = api_post($url, $param);
            $ret = json_decode($res['body'], true);
            if($ret['ok'] === false)
            {
                $this->exception('error', -1);
            }
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'  => $e->getMessage(),
            ]);
        }
        return $status;
    }
}
