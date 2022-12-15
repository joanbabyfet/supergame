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
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class mod_agent extends Authenticatable implements JWTSubject, Wallet, WalletFloat
{
    use HasFactory, Notifiable, HasRoles, HasWalletFloat;

    protected $table = 'agents';   //表名
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

    //狀態
    const DISABLE = 0;
    const ENABLE = 1;
    public static $status_map = [
        self::DISABLE   => '禁用',
        self::ENABLE    => '啟用'
    ];

    //可写入字段白名单 Post::create($request->all())
    protected $fillable = [
        'id',
        'username',
        'password',
        'realname',
        'email',
        'phone_code',
        'phone',
        'reg_ip',
        'create_user',
        'create_time'
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * 获取会储存到 jwt 声明中的标识
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * 返回包含要添加到 jwt 声明中的自定义键值对数组
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            //'username' => $this->username,
            'hst' => md5(gethostname()),
            'ipa' => md5(request()->ip()),
            'ura' => md5(request()->userAgent()),
        ];
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

    //格式化数据 添加时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }

    //添加人
    public function create_user_maps()
    {
        //只返回某几个字段时要包含关联字段才会有数据
        return $this->belongsTo('App\Models\mod_admin_user', 'create_user', 'id')
            ->select(['id', 'realname']);
    }

    //所属用户角色
    public function role_maps()
    {
        //model_has_roles为中介表, withPivot返回中介表字段(默认只返回关联键model_id与role_id)
        return $this->belongsToMany('App\Models\mod_role', 'model_has_roles', 'model_id', 'role_id')
            ->withPivot(['role_id', 'model_id'])->select(['id', 'name']);
    }

    //所属私钥
    public function app_key_maps()
    {
        return $this->belongsTo('App\Models\mod_app_key', 'id', 'agent_id')
            ->select(['agent_id', 'app_id', 'app_key']);
    }
}
