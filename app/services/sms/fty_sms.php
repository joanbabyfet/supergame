<?php


namespace App\services\sms;


use App\services\sms\Strategy\st_messagebird;
use App\services\sms\Strategy\st_smsmkt;
use App\traits\trait_service_base;

/**
 * 短信简单工厂类(静态工厂), 严格来说不是设计模式, 統一管理所有具体策略类用
 * Class fty_sms
 * @package App\services\sms\Strategy
 */
class fty_sms
{
    use trait_service_base;

    /**
     * 选择短信商
     * @param $type
     * @return st_messagebird|st_smsmkt
     */
    public static function strategy($type)
    {
        $strategy = new \stdClass();
        switch($type)
        {
            case 'messagebird':
                $strategy = new st_messagebird();
                break;
            case 'smsmkt':
                $strategy = new st_smsmkt();
                break;
            default:
                self::exception('类型错误', -1); //返回类型错误
        }
        return $strategy; //返回对象
    }
}
