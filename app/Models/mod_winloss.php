<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_winloss extends Model
{
    use HasFactory;

    protected $table = 'winloss';   //表名
    public $primaryKey = 'bet_id';   //主键
    protected $keyType = 'string'; //主键不是int, 需设置string, 否则belongsTo会错误
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
    const CREATED_AT = 'bet_time';
    const UPDATED_AT = 'update_time';
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

    //狀態
    const STATUS_NEW = 0;
    const STATUS_PAYOUT = 1;
    const STATUS_CANCEL = -1;
    const STATUS_INVALID = -2;
    public static $status_map = [
        self::STATUS_CANCEL     => '已取消',
        self::STATUS_INVALID    => '无效', //作废
        self::STATUS_NEW        => '已投注',
        self::STATUS_PAYOUT   => '已结算' //結算派彩
    ];

    //下注类型
    const BET_TYPE_BANKER = 1;
    const BET_TYPE_BZ = 2;
    const BET_TYPE_GZ = 3;
    const BET_TYPE_PLAYER = 4;
    public static $bet_type_map = [
        self::BET_TYPE_BANKER   => '当庄',
        self::BET_TYPE_BZ       => '帮庄',
        self::BET_TYPE_GZ       => '公庄',
        self::BET_TYPE_PLAYER   => '买闲'
    ];

    //格式化数据 下注类型
    public function getBetTypeTextAttribute()
    {
        $bet_type = $this->getAttribute('bet_type') ?? '';
        $maps = self::$bet_type_map;

        $text = '';
        $temp = [];
        $arr_bet_type = explode(',', $bet_type);
        foreach ($arr_bet_type as $v)
        {
            if(isset($maps[$v])){
                $temp[] = $maps[$v];
            }
        }
        $text = implode('/', $temp);
        return $text;
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

    //格式化数据 结算时间
    public function getSettleTimeTextAttribute()
    {
        $settle_time = $this->getAttribute('settle_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($settle_time) ? '' : Carbon::createFromTimestamp($settle_time)->format('Y-m-d H:i');
    }

    //所属桌子
//    public function table_maps()
//    {
//        return $this->hasOne('App\Models\mod_game_table', 'id', 'table_id')
//            ->select(['id', 'name', 'uid']);
//    }

    //所属用户
    public function user_maps()
    {
        return $this->hasOne('App\Models\mod_user', 'id', 'uid')
            ->select(['id', 'username', 'realname']);
    }

    //所属牌局
    public function round_maps()
    {
        return $this->hasOne('App\Models\mod_game_round', 'round_id', 'round_id')
            ->select(['game_round.round_id', 'game_round.video_url', 'game_round.pic', 'game_round.result']);
    }

    //格式化数据 是否上庄
    public function getIsSzTextAttribute()
    {
        $is_sz = $this->getAttribute('bet_type') ?? '';

        $text = "否";
        if($is_sz == self::BET_TYPE_BANKER){
            $text = '是';
        }
        return $text;
    }
}
