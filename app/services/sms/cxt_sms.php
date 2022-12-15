<?php


namespace App\services\sms;

/**
 * 短信环境类
 * Class sms_context
 * @package App\services\sms
 */
class cxt_sms
{
    private $strategy; //定义私有变量

    public function __construct(st_sms $st_sms) //依赖注入抽象策略类
    {
        $this->strategy = $st_sms;
    }

    public function send_msg($phone, $content = '')
    {
        return $this->strategy->send_msg($phone, $content);
    }
}
