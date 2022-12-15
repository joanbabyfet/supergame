<?php

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWalletFloat;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class mod_user extends Authenticatable implements JWTSubject, Wallet, WalletFloat
{
    use HasFactory, Notifiable, HasRoles, HasWallet, HasWalletFloat;

    protected $table = 'users';   //表名
    public $primaryKey = 'id';   //主键
    protected $keyType = 'string'; //主键不是int, 需设置string, 否则belongsTo会错误
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //注册来源
    const ORIGIN_H5         = 1;
    const ORIGIN_PC         = 2;
    const ORIGIN_ANDROID    = 3;
    const ORIGIN_IOS        = 4;
    public static $origin_map = [
        self::ORIGIN_H5         => 'H5',
        self::ORIGIN_PC         => 'PC',
        self::ORIGIN_ANDROID    => '安卓',
        self::ORIGIN_IOS        => 'IOS',
    ];

    //狀態
    const DISABLE = 0;
    const ENABLE = 1;
    public static $status_map = [
        self::DISABLE   => '禁用',
        self::ENABLE    => '啟用'
    ];

    //新增用户为第一次登录的用户
    const NEW_USER_YES = 1;
    const NEW_USER_NO = 0;
    public static $is_new_user_map = [
        self::NEW_USER_YES   => '是',
        self::NEW_USER_NO    => '否'
    ];

    //可写入字段白名单 Post::create($request->all())
    protected $fillable = [
        'id',
        'origin',
        'username',
        'password',
        'realname',
        'email',
        'phone_code',
        'phone',
        'reg_ip',
        'language',
        'create_user',
        'create_time'
    ];

    //将字段转其他类型
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //将字段隐藏不展示
    protected $hidden = [
        'password', 'remember_token', 'api_token'
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

    //格式化数据 注册来源
    public function getOriginTextAttribute()
    {
        $origin = $this->getAttribute('origin') ?? '';
        $maps = self::$origin_map;

        $text = "";
        if(isset($maps[$origin])){
            $text = $maps[$origin];
        }
        return $text;
    }

    //格式化数据 注册时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }

    //格式化数据 最后登录时间时间
    public function getLoginTimeTextAttribute()
    {
        $login_time = $this->getAttribute('login_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($login_time) ? '' : Carbon::createFromTimestamp($login_time)->format('Y-m-d H:i');
    }

    //格式化数据 新增用户为第一次登录的用户
    public function getIsNewUserTextAttribute()
    {
        $is_new_user = $this->getAttribute('is_new_user') ?? '';
        $maps = self::$is_new_user_map;

        $text = "";
        if(isset($maps[$is_new_user])){
            $text = $maps[$is_new_user];
        }
        return $text;
    }

    /**
     * 获取会储存到 jwt 声明中的标识
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); //将用户id保存在sub字段
    }

    /**
     * 返回包含要添加到 jwt 声明中的自定义键值对数组
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            //'username' => $this->username,
            'agent_id'  => $this->agent_id,
            'hst' => md5(gethostname()),
            'ipa' => md5(request()->ip()),
            'ura' => md5(request()->userAgent()),
            'exp' => strtotime("+14 day", time()) //与JWT_REFRESH_TTL一致默认14天
        ];
    }

    //所属渠道代理
    public function agent_maps()
    {
        return $this->hasOne('App\Models\mod_agent', 'id', 'agent_id')
            ->select(['id', 'realname']);
    }

    //所属用户角色
    public function role_maps()
    {
        //model_has_roles为中介表, withPivot返回中介表字段(默认只返回关联键model_id与role_id)
        return $this->belongsToMany('App\Models\mod_role', 'model_has_roles', 'model_id', 'role_id')
            ->withPivot(['role_id', 'model_id'])->select(['id', 'name']);
    }

    //所属钱包馀额
    public function wallet_maps()
    {
        return $this->hasOne('App\Models\mod_wallet', 'holder_id', 'id')
            ->select(['holder_id', 'balance']);
    }
}
