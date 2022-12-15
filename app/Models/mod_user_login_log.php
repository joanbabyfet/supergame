<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_user_login_log extends Model
{
    use HasFactory;

    protected $table = 'users_login_log';   //表名
    public $primaryKey = 'id';   //主键
    //public $connection = '';   //连接其他数据库 例mongo
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
        'extra_info'    => 'array'
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];

    //格式化数据 最后登录时间
    public function getLoginTimeTextAttribute()
    {
        $login_time = $this->getAttribute('login_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($login_time) ? '' : Carbon::createFromTimestamp($login_time)->format('Y-m-d H:i');
    }

    //格式化数据 状态
    public function getStatusTextAttribute()
    {
        $status = $this->getAttribute('status') ?? '';
        $maps = self::$status_map;

        $text = "";
        if(isset($maps[$status])){
            $text = $maps[$status];
        }
        return $text;
    }
}
