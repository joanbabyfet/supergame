<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_app_key extends Model
{
    use HasFactory;

    protected $table = 'app_key';   //表名
    public $primaryKey = 'app_id';   //主键
    protected $keyType = 'string'; //主键不是int, 需设置string, 否则belongsTo会错误
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
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

    //格式化数据 添加时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }

    //所属渠道代理
    public function agent_maps()
    {
        return $this->hasOne('App\Models\mod_agent', 'id', 'agent_id')
            ->select(['id', 'realname', 'username', 'currency']);
    }
}
