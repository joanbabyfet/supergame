<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_model_has_roles extends Model
{
    use HasFactory;

    protected $table = 'model_has_roles';   //表名
    public $primaryKey = 'role_id';   //主键
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
}
