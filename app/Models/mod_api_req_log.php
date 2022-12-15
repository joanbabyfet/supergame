<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class mod_api_req_log extends Model
{
    use HasFactory;

    protected $table = 'api_req_log';   //表名
    public $primaryKey = '_id';   //主键
    public $connection = 'mongodb';   //连接其他数据库 例mongo
    public $incrementing = true;   //主键是否支持自增,默认支持
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
        'type',
        'url',
        'method',
        'uid',
        'req_data',
        'res_data',
        'req_time',
        'req_ip',
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];
}
