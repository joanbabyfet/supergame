<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 只对数据进行定义, 不参与业务逻辑
 * Class mod_example
 * @package App\Models
 */
class mod_example extends Model
{
    use HasFactory;

    protected $table = 'example';   //表名
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

    //可写入字段白名单
    protected $fillable = [
        'id',
        'cat_id',
        'title',
        'content',
        'img',
        'sort',
        'status',
    ];

    //将字段转其他类型, 例某字段存储的是 timestamp，在orm获取数据后再处理成我们指定类型datetime:Y-m-d H:i:s可识别的样子
    protected $casts = [
        //'create_time' => 'timestamp',
        //'update_time' => 'timestamp',
        //'delete_time' => 'timestamp',
    ];

    //将字段隐藏不展示
    protected $hidden = [
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

    //格式化数据 添加时间
    public function getCreateTimeTextAttribute()
    {
        $create_time = $this->getAttribute('create_time') ?? '';
        //避免时区不一致造成时间错误 config('app.timezone') 要设置正确
        return empty($create_time) ? '' : Carbon::createFromTimestamp($create_time)->format('Y-m-d H:i');
    }
}
