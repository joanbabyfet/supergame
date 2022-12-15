<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class mod_admin_user_oplog extends Model
{
    use HasFactory;

    protected $table = 'admin_users_oplog';   //表名
    public $primaryKey = '_id';   //主键
    public $connection = 'mongodb';   //连接其他数据库 例mongo
    public $incrementing = true;   //主键是否支持自增,默认支持
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
        'uid',
        'username',
        'session_id',
        'msg',
        'op_time',
        'op_ip',
        'op_country',
        'op_url',
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];

    //格式化数据 操作时间
    public function getOpTimeTextAttribute()
    {
        $create_time = $this->getAttribute('op_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }
}
