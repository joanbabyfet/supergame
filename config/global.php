<?php
/**
 * 全局变量
 */

return [
    //'is_maintenance'    => env('IS_MAINTENANCE', ''), //系统维护中 (改由后台配置)
    'role_super_admin'     => 1, //超级管理员
    'role_general_member'  => 2, //普通会员
    'role_general_agent'   => 3, //代理商
    'role_sub_account'   => 4, //子账号
    //守卫類型
    'guard_names' => [
        'admin' => '管理守卫',
        'agent' => '代理守卫',
        'api'   => '用户守卫',
    ],
    //运营后台设置
    'admin' => [
        'app_title' => '运营后台',
        'app_name'  => 'admin',
        'domain'    => env('ADMIN_DOMAIN', ''),
        'guard'     => env('ADMIN_GUARD', ''),
    ],
    //代理后台设置
    'adminag' => [
        'app_title' => '代理后台',
        'app_name'  => 'adminag',
        'domain'    => env('ADMINAG_DOMAIN', ''),
        'guard'     => env('ADMINAG_GUARD', ''),
    ],
    //用户端设置
    'client' => [
        'app_title' => '用户端',
        'app_name'  => 'client',
        'domain'    => env('CLIENT_DOMAIN', ''),
        'guard'     => env('CLIENT_GUARD', ''),
    ],
    //游戏api设置
    'api' => [
        'app_title' => '游戏api',
        'app_name'  => 'api',
        'domain'    => env('API_DOMAIN', ''),
        'guard'     => env('API_GUARD', ''),
    ],
    //第三方应用请求下面接口免登录，但是需要验证接口签名
    'app' => [
        '2207271525353540508' => [
            'app_name' => '平台',
            'app_id'   => '2207271525353540508',                //第三方应用id
            'app_key'  => 'fb15be6bf27d1f5121a4de5d751fa49b',   //密钥串
        ]
    ],
    'to_timezone' => 'ETC/GMT-8', //默认需要转化的时区，东七区是柬埔寨时间
    //h5地址
    'h5_url' => 'http://api.example.local/h5?id=%s',
    //socket配置
    'socket' => [
        'name'                  => 'businessworker',
        'admin_gateway_port'    => '2346',
        'agent_gateway_port'    => '2347',
        'hosts' => [
            'admin' => [
                'name'       => 'admin_socket_gateway',
                'listen'     => 'websocket://0.0.0.0:2346',
                'start_port' => 2300
            ],
            'agent' => [
                'name'       => 'agent_socket_gateway',
                'listen'     => 'websocket://0.0.0.0:2347',
                'start_port' => 2400
            ],
        ],
        'process_count'           => 1,
        'lan_ip'                  => '127.0.0.1',
        'ping_interval'           => 0,
        'ping_not_response_limit' => 3,
        'ping_data'               => '',
        'register_address'        => '127.0.0.1:1236'
    ],
    //google地图
    'map' => [
        'web_key' => '', //网页key
        'key'     => '', //验证地址是否正确
        'app_key' => '', //app key
    ],
    //翻译 key
    'translate_key' => '',
    //短信 every8d
    'every8d' => [
        'username'  => '',
        'password'  => ''
    ],
    //泰国本地短信商
    'smsmkt' => [
        'url'           => env('SMSMKT_URL'),
        'api_key'       => env('SMSMKT_API_KEY'),
        'secret_key'    => env('SMSMKT_SECRET_KEY'),
        'origin'        => env('SMSMKT_ORIGIN') //发送人
    ],
    'messagebird' => [
        'app_key'       => env('MESSAGEBIRD_APP_KEY'),
        'origin'        => env('MESSAGEBIRD_ORIGIN') //发送人
    ],
    // 上传设置
    'file_link'       => env('FILE_LINK'),
    //默认缓存时间2小时，单位秒
    'cache_time'       => 120 * 60,

    // IP白名单
    'ip_whitelist' => [
        '103.227.175.78',
        '96.9.67.134',
        '34.124.199.205', //vpn
    ],
    // IP黑名单
    'ip_blacklist' => [],
    // 国家白名单
    'country_whitelist' => [],
    // 国家黑名单
    'country_blacklist' => [
        'KH',
        'CN'
    ],
    // 该应用使用的语言列表
    'lang_map' => [
        'zh-tw'  => '繁体中文',
        'zh-cm'  => '简体中文',
        'en'  => '英文',
        'th'  => '泰语',
    ],
    //目录与文件权限
    'permission' => [
        'directory' => env('DIRECTORY_PERMISSION', 0777),
        'file' => env('FILE_PERMISSION', 0644)
    ],
    //钱包类型 1=转帐钱包 2=单一钱包
    'wallet_type'   => env('WALLET_TYPE'),
    //默认币种
    'currency'      => env('CURRENCY'),
    //允许文件格式
    'allowed_types' => env('ALLOWED_TYPES', 'jpeg|jpg|gif|png|bmp|webp|mp4|zip|rar|gz|bz2|xls|xlsx|pdf|doc|docx'),
    //允许上传文件大小的最大值（单位 KB），设置为 0 表示无限制
    'max_size'      => env('MAX_SIZE', 5*1024),
    //telegram 机器人
    'telegram_bot' => [
        'callback_url'  => env('TELEGRAM_BOT_CALLBACK_URL'),
        'token'         => env('TELEGRAM_BOT_TOKEN'),
    ],
    //因为涉及多币种转换交易，有些不可能整除，所以我们设置一个最大容差
    'pay_max_diff'  => env('PAY_MAX_DIFF', 0.01),
    //最小提现金额
    'min_amount'    => env('MIN_AMOUNT', 0.01),
    //游戏地址
    'game_url' => [
        'h5' => 'http://34.142.173.216:8001/web-mobile/?token=%s&lang=zh-tw',
        'pc' => 'http://34.142.173.216:8001/pc/?token=%s&lang=zh-tw',
    ],
    //rpc地址
    'rpc' => [
        'host' => env('RPC_HOST', ''),
        'port' => env('RPC_PORT', ''),
    ],
    //系统公庄uid
    'sys_gz_uid' => '040291ac491a3a7330836771f5d013a2'
];
