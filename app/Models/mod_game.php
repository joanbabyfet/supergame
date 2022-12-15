<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class mod_game extends Model
{
    use HasFactory;

    protected $table = 'game';   //表名
    public $primaryKey = 'id';   //主键
    //protected $connection = '';   //连接其他数据库 例mongo
    public $incrementing = false;   //主键是否支持自增,默认支持
    const CREATED_AT = 'create_time';
    const UPDATED_AT = 'update_time';
    const DELETED_AT = 'delete_time';
    public $timestamps = false; //false=不让 Eloquent 自动维护时间戳这两个字段
    //扩充字段
    protected $appends = [];

    //游戏类型 LC=真人视讯 CB=棋牌 SB=体育游戏 SL=老虎机 LK=彩票 FH=捕鱼 PK=扑克 OT=其他
    const GAME_TYPE_LC = 'LC';
    const GAME_TYPE_CB = 'CB';
    const GAME_TYPE_SB = 'SB';
    const GAME_TYPE_SL = 'SL';
    const GAME_TYPE_LK = 'LK';
    const GAME_TYPE_FH = 'FH';
    const GAME_TYPE_PK = 'PK';
    const GAME_TYPE_OT = 'OT';
    public static $game_type = [
        self::GAME_TYPE_LC   => '真人视讯',
        self::GAME_TYPE_CB   => '棋牌',
        self::GAME_TYPE_SB   => '体育游戏',
        self::GAME_TYPE_SL   => '老虎机',
        self::GAME_TYPE_LK   => '彩票',
        self::GAME_TYPE_FH   => '捕鱼',
        self::GAME_TYPE_PK   => '扑克',
        self::GAME_TYPE_OT   => '其他',
    ];

    //PG牌头选项
    public static $card_head_type = [
        1   => 'one by one',
        2   => 'cut ears',
        3   => 'nakadori',
        4   => 'building',
        5   => 'blockbust',
        6   => 'head phoenix'
    ];

    //PG牌值
    public static $card_value_type = [
        '12'   => '丁三',
        '24'   => '二四',
        '23'   => '杂五',
        '14'   => '杂五',
        '25'   => '杂七',
        '34'   => '杂七',
        '26'   => '杂八',
        '35'   => '杂八',
        '36'   => '杂九',
        '45'   => '杂九',
        '15'   => '零霖六',
        '16 '  => '高脚七',
        '46'   => '红头十',
        '56'   => '斧头',
        '22'   => '板凳',
        '33'   => '长三',
        '55'   => '梅牌',
        '13'   => '鹅牌',
        '44'   => '人牌',
        '11'   => '地牌',
        '66'   => '天牌',
    ];

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
        'code',
        'name',
        'type',
        'cover_img',
        'sort',
        'status',
    ];

    //将字段转其他类型
    protected $casts = [
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
}
