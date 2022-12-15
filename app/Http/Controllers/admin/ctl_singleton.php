<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;

/**
 * 单例类, 使得类的一个对象成为系统中唯一实例
 * Class ctl_singleton
 * @package App\Http\Controllers\admin
 */
class ctl_singleton
{
    private static $instance;

    //定义私有构造函数，确保该单例类不能通过new关键字实例化
    private function __construct()
    {

    }

    //对外提供获取唯一实例方法
    public static function getInstance()
    {
        //检测类是否已实例化
        if (null === self::$instance)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }
}
