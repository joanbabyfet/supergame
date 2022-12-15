<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_order_transfer extends Model
{
    use HasFactory;

    protected $table = 'order_transfer';   //表名
    public $primaryKey = 'id';   //主键
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

    //订单来源
    const ORIGIN_CLIENT     = 1; //玩家下单
    const ORIGIN_ADMIN      = 2; //运营后台下单
    const ORIGIN_PAYOUT     = 3; //结算派彩

    //交易类型
    const DEPOSIT = 1;
    const WITHDRAW = 2;
    public static $type_map = [
        self::DEPOSIT   => '充值',
        self::WITHDRAW  => '取款'
    ];

    //支付状态
    const PAY_STATUS_PENDING = 0;
    const PAY_STATUS_SUCCESS = 1;
    const PAY_STATUS_FAIL = -1;
    public static $pay_status_map = [
        self::PAY_STATUS_PENDING   => '待支付',
        self::PAY_STATUS_SUCCESS  => '成功',
        self::PAY_STATUS_FAIL  => '失败'
    ];

    //回调状态
//    const CALLBACK_STATUS_PENDING = 0;
//    const CALLBACK_STATUS_SUCCESS = 1;
//    const CALLBACK_STATUS_FAIL = -1;
//    public static $callback_status_map = [
//        self::CALLBACK_STATUS_PENDING   => '未确认',
//        self::CALLBACK_STATUS_SUCCESS  => '成功',
//        self::CALLBACK_STATUS_FAIL  => '失败'
//    ];

    //格式化数据 添加时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }

    //格式化数据 支付时间
    public function getPayTimeTextAttribute()
    {
        $pay_time = $this->getAttribute('pay_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($pay_time) ? '' : Carbon::createFromTimestamp($pay_time)->format('Y-m-d H:i');
    }

    //所属渠道代理
    public function agent_maps()
    {
        return $this->hasOne('App\Models\mod_agent', 'id', 'agent_id')
            ->select(['id', 'realname']);
    }

    //所属用户
    public function user_maps()
    {
        return $this->hasOne('App\Models\mod_user', 'id', 'uid')
            ->select(['id', 'username', 'realname', 'phone_code', 'phone']);
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

    //添加人
    public function create_user_maps()
    {
        //只返回某几个字段时要包含关联字段才会有数据
        return $this->belongsTo('App\Models\mod_admin_user', 'create_user', 'id')
            ->select(['id', 'realname']);
    }
}
