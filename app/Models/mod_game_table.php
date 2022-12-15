<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_game_table extends Model
{
    use HasFactory;

    protected $table = 'game_table';   //表名
    public $primaryKey = 'id';   //主键
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = true;   //主键是否支持自增,默认支持
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
    ];

    //将字段转其他类型
    protected $casts = [
        'config'    => 'array'
    ];

    //将字段隐藏不展示
    protected $hidden = [
        'password'
    ];

    //狀態
    const DISABLE = 0;
    const ENABLE = 1;
    const PENDING = 2;
    public static $status_map = [
        self::DISABLE   => '禁用',
        self::ENABLE    => '啟用',
        self::PENDING   => '等待执行',
    ];

    //类型
    const TYPE_CASH = 1;
    const TYPE_CREDIT = 2;
    public static $type_map = [
        self::TYPE_CASH     => '现金',
        self::TYPE_CREDIT   => '信用'
    ];

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

    //格式化数据 类型
    public function getTypeTextAttribute()
    {
        $type = $this->getAttribute('type') ?? '';
        $maps = self::$type_map;

        $text = "";
        if(isset($maps[$type])){
            $text = $maps[$type];
        }
        return $text;
    }

    //格式化数据 累计时间
    public function getDurationTextAttribute()
    {
        $start_time = $this->getAttribute('start_time') ?? '';
        $text = second2time(time() - $start_time); //现在时间减去开始时间
        return $text;
    }

    //格式化数据 创建时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }

    //格式化数据 开始时间
    public function getStartTimeTextAttribute()
    {
        $start_time = $this->getAttribute('start_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($start_time) ? '' : Carbon::createFromTimestamp($start_time)->format('Y-m-d H:i');
    }

    //格式化数据 结束时间
    public function getEndTimeTextAttribute()
    {
        $end_time = $this->getAttribute('end_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($end_time) ? '' : Carbon::createFromTimestamp($end_time)->format('Y-m-d H:i');
    }

    //所属渠道代理
    public function agent_maps()
    {
        return $this->hasOne('App\Models\mod_agent', 'id', 'agent_id')
            ->select(['id', 'realname']);
    }

    //所属桌主
    public function user_maps()
    {
        return $this->hasOne('App\Models\mod_user', 'id', 'uid')
            ->select(['id', 'username', 'realname']);
    }

    //所属房间
    public function room_maps()
    {
        return $this->hasOne('App\Models\mod_room', 'id', 'room_id')
            ->select(['id', 'name']);
    }
}
