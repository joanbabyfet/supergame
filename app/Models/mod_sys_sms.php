<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_sys_sms extends Model
{
    use HasFactory;

    protected $table = 'sys_sms';   //表名
    public $primaryKey = 'id';   //主键
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = true;   //主键是否支持自增,默认支持
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];

    //短信发送对象
    const OBJECT_TYPE_ALL      = 1;
    const OBJECT_TYPE_PERSONAL = 2;
    const OBJECT_TYPE_LEVEL    = 3;
    const OBJECT_TYPE_REG_TIME = 4;
    public static $object_type = [
        1=>'所有用户',
        2=>'个人',
        3=>'会员等级',
        4=>'注册时间'
    ];
}
