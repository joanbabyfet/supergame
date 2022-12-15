<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_game_round extends Model
{
    use HasFactory;

    protected $table = 'game_round';   //表名
    public $primaryKey = 'round_id';   //主键
    protected $keyType = 'string'; //主键不是int, 需设置string, 否则belongsTo会错误
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
    const CREATED_AT = 'settle_time';
    //const UPDATED_AT = 'update_time';
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
    ];

    //将字段转其他类型
    protected $casts = [
        'result'    => 'array'
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];

    //格式化数据 结算时间
    public function getSettleTimeTextAttribute()
    {
        $settle_time = $this->getAttribute('settle_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($settle_time) ? '' : Carbon::createFromTimestamp($settle_time)->format('Y-m-d H:i');
    }
}
