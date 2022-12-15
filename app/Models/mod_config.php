<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_config extends Model
{
    use HasFactory;

    protected $table = 'config';   //表名
    public $primaryKey = 'name';   //主键
    protected $keyType = 'string'; //主键不是int, 需设置string, 否则belongsTo会错误
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    //const DELETED_AT = 'delete_time';
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //可写入字段白名单
    protected $fillable = [
        'type',
        'name',
        'value',
        'title',
        'group',
        'sort',
    ];

    //将字段转其他类型
    protected $casts = [
    ];

    //将字段隐藏不展示
    protected $hidden = [
    ];

    //分组
    public static $options = array(
        'config'        => '基本配置',
        'config_game'   => '游戏基本配置',
        'config_table'  => '建桌配置',
    );
}
