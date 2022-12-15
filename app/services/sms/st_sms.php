<?php


namespace App\services\sms;

/**
 * 短信策略接口, 接口强调特定功能实现, 抽象类强调所属关系
 * Interface st_sms
 * @package App\services\sms
 */
interface st_sms
{
    public function send_msg($phone, $content = '');
}
