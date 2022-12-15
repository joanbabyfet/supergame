<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class mod_agent_login_log extends Model
{
    use HasFactory;

    protected $table = 'agents_login_log';   //表名
    public $primaryKey = '_id';   //主键
    public $connection = 'mongodb';   //连接其他数据库 例mongo
    public $incrementing = true;   //主键是否支持自增,默认支持
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //狀態
    const DISABLE = 0;
    const ENABLE = 1;
    public static $status_map = [
        self::DISABLE   => '失敗',
        self::ENABLE    => '成功'
    ];

    //可写入字段白名单
    protected $fillable = [
        'uid',
        'username',
        'session_id',
        'agent',
        'login_time',
        'login_ip',
        'login_country',
        'status',
        'cli_hash',
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];
}
