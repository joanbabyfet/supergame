<?php


namespace App\services\sms\Strategy;


use App\repositories\repo_sms_send_log;
use App\services\sms\st_sms;

/**
 * smsmkt 短信具体策略类
 * Class st_messagebird
 * @package App\services\sms\Strategy
 */
class st_smsmkt implements st_sms
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

    }
}
