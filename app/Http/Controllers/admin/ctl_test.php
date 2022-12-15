<?php

namespace App\Http\Controllers\admin;

use App\Events\evt_send_mail;
use App\Jobs\job_example;
use App\Jobs\job_send_mail;
use App\Jobs\job_send_sms;
use App\Models\mod_admin_user;
use App\Models\mod_example;
use App\Models\mod_sys_sms;
use App\Models\mod_user_login_log;
use App\repositories\repo_admin_user;
use App\repositories\repo_api_req_log;
use App\repositories\repo_app_key;
use App\repositories\repo_config;
use App\repositories\repo_example;
use App\repositories\repo_game;
use App\repositories\repo_member_increase_data;
use App\repositories\repo_order_transfer;
use App\repositories\repo_user;
use App\services\serv_display;
use App\services\serv_redis;
use App\services\serv_req;
use App\services\serv_rpc_client;
use App\services\serv_sys_mail;
use App\services\serv_sys_sms;
use App\services\serv_util;
use App\services\serv_wallet;
use Grpc\ChannelCredentials;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;


/**
 * 测试用控制器
 * Class ctl_test
 * @package App\Http\Controllers\admin
 */
class ctl_test extends Controller
{
    private $repo_config;
    private $serv_req;
    private $serv_sys_mail;
    private $serv_sys_sms;
    private $repo_admin_user;
    private $repo_member_increase_data;
    private $serv_util;
    private $repo_game;
    private $repo_app_key;
    private $serv_wallet;
    private $serv_display;
    private $serv_rpc_client;
    private $repo_order_transfer;
    private $repo_user;
    private $serv_redis;
    private $repo_example;
    private $repo_api_req_log;

    public function __construct(
        repo_config $repo_config,
        serv_req $serv_req,
        serv_sys_mail $serv_sys_mail,
        serv_sys_sms $serv_sys_sms,
        repo_admin_user $repo_admin_user,
        repo_member_increase_data $repo_member_increase_data,
        serv_util $serv_util,
        repo_game $repo_game,
        repo_app_key $repo_app_key,
        serv_wallet $serv_wallet,
        serv_display $serv_display,
        serv_rpc_client $serv_rpc_client,
        repo_order_transfer $repo_order_transfer,
        repo_user $repo_user,
        serv_redis $serv_redis,
        repo_example $repo_example,
        repo_api_req_log $repo_api_req_log
    )
    {
        parent::__construct();
        $this->repo_config = $repo_config;
        $this->serv_req = $serv_req;
        $this->serv_sys_mail = $serv_sys_mail;
        $this->serv_sys_sms = $serv_sys_sms;
        $this->repo_admin_user = $repo_admin_user;
        $this->repo_member_increase_data = $repo_member_increase_data;
        $this->serv_util = $serv_util;
        $this->repo_game = $repo_game;
        $this->repo_app_key = $repo_app_key;
        $this->serv_wallet = $serv_wallet;
        $this->serv_display = $serv_display;
        $this->serv_rpc_client = $serv_rpc_client;
        $this->repo_order_transfer = $repo_order_transfer;
        $this->repo_user = $repo_user;
        $this->serv_redis = $serv_redis;
        $this->repo_example = $repo_example;
        $this->repo_api_req_log = $repo_api_req_log;
    }

    public function index(Request $request)
    {
//        $ip = '34.124.199.205';
//        return res_success(['country' => ip2country($ip)]);

        //分派任务到异步队列池
//        $job_example = new job_example();
//        dispatch($job_example);

        //获取系统配置
//        $business_hours = $this->repo_config->get('business_hours');
//        pr($business_hours);

        //请求头参数
//        $language = $this->serv_req->get_language();
//        $token = $this->serv_req->get_token();
//        $version = $this->serv_req->get_version();
//        $os_info = $this->serv_req->get_os_info();
//        $timezone = $this->serv_req->get_timezone();
//        $os_type = $this->serv_req->get_os_type();
//        pr($os_type);

        //根据用户设置语言, 返回 content 或 content_en字段内容
//        $content_field = get_lang_field('content', 'en');
//        pr($content_field);

//        $url = 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=1&n=10&mkt=en-US';
//        $res  = curl_get($url);
//        $ret= json_decode($res['body'], true);
//        pr($ret);

//        $url = 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=1&n=10&mkt=en-US';
//        $res  = curl_post($url);
//        $ret= json_decode($res['body'], true);
//        pr($ret);

//        $url = 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=1&n=10&mkt=en-US';
//        $res  = api_get($url);
//        $ret= json_decode($res['body'], true);
//        pr($ret);

//        $url = 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=1&n=10&mkt=en-US';
//        $res  = api_post($url);
//        $ret= json_decode($res['body'], true);
//        pr($ret);

        //检测图片验证码
//        $captcha    = $request->input('captcha', ''); //验证码
//        $key        = $request->input('key', ''); //验证码生成的key
//        if (!captcha_api_check($captcha, $key))
//        {
//            return res_error('验证不通过', -1);
//        }
//        else
//        {
//            return res_success([], '验证通过');
//        }

        //推送任务到队列
//        $view_data = [
//            '4c1ea0e231e194467d8801f4d70d7a09' => [
//                'realname'  =>  '1',
//                'username'  =>  '2',
//                'code'      =>  '3',
//            ]
//        ];
//        $to = [
//            '4c1ea0e231e194467d8801f4d70d7a09' => [
//                'email' => 'test@gmail.com',
//                'name' => 'peter',
//            ]
//        ];
//        $job = new job_send_mail([
//            'to'        => $to,
//            'subject'   => '测试',
//            'view'      => 'mail.example',
//            'view_data' => $view_data,
//        ]);
//        dispatch($job);

        //依据发送对象推送任务到队列
//        $view_data = [
//            '4c1ea0e231e194467d8801f4d70d7a09' => [
//                'realname'  =>  '1',
//                'username'  =>  '2',
//                'code'      =>  '3',
//            ]
//        ];
//        $status = $this->serv_sys_mail->send([
//            'object_type'   => 4,
//            'object_ids'    => '2022/06/13,2022/06/22',
//            'subject'       => '测试',
//            'view'          => 'mail.example',
//            'view_data'     => $view_data,
//        ]);

        //推送任务到队列
//        $send_users = [
//            [
//                'id'            => '4c1ea0e231e194467d8801f4d70d7a09',
//                'phone_code'    => '855',
//                'phone'         => '86207239',
//                'language'      => 'zh-tw'
//            ]
//        ];
//        $job = new job_send_sms([
//            'content'       => '测试',
//            'content_en'    => 'test',
//            'send_users'    => $send_users, //接收人数组
//            'send_uid'      => defined('AUTH_UID') ? AUTH_UID : '',
//        ]);
//        dispatch($job);

        //依据发送对象推送任务到队列
//        $status = $this->serv_sys_sms->send([
//            'object_type'   => mod_sys_sms::OBJECT_TYPE_REG_TIME,
//            'object_ids'    => '2022/06/13,2022/06/23',
//            'name'          => '送优惠券',
//            'content'       => '测试',
//            'content_en'    => 'test',
//            'send_uid'      => defined('AUTH_UID') ? AUTH_UID : '',
//        ]);

        //时区转换, 转为东京本地时间
//        $datetime = time_convert([
//            '1656522000'      => 1656642047,
//            //'to_timezone'   => 'Asia/Tokyo', //柬时间
//        ]);
//        pr($datetime);

        //时间转转间戳

        //批量插入或更新
//        $data = [
//            [
//                'date'                      => '2022/07/01',
//                'agent_id'                  => '8696c8d0701d0436b468df4bfc2fe3d3',
//                'origin'                    => 1,
//                'member_count'              => 1,
//                'member_increase_count'     => 1,
//                'member_active_count'       => 1,
//                'member_retention_count'    => 1,
//                'create_time'               => time()
//            ],
//            [
//                'date'                      => '2022/07/02',
//                'agent_id'                  => '8696c8d0701d0436b468df4bfc2fe3d3',
//                'origin'                    => 1,
//                'member_count'              => 1,
//                'member_increase_count'     => 1,
//                'member_active_count'       => 1,
//                'member_retention_count'    => 1,
//                'create_time'               => time()
//            ]
//        ];
//        $this->repo_member_increase_data->insertOrUpdate($data,
//            ['date', 'agent_id'],
//            ['origin', 'member_count', 'member_increase_count', 'member_active_count', 'member_retention_count', 'create_time'],
//        );

        //生成订单id
        //pr($this->serv_util->make_order_id());
        //生成优惠券编号
        //pr($this->serv_util->make_no());
        //生成10位数兑换码
        //pr($this->serv_util->make_exchange_code());

        //根据游戏代码获取游戏信息
//        $row = $this->repo_game->get_game_by_code('PG');
//        $game_id = $row ? $row['id'] : '';
//        pr($game_id);

        //生成应用id与key
        //pr($this->repo_app_key->create_app_key());

        //上分
//        $this->serv_wallet->deposit([
//            'uid'       => '01db46fc49e2eb7f18a9181bd0d68b1e',
//            'amount'    => 1000,
//        ]);

        //下分
//        $this->serv_wallet->withdraw([
//            'uid'       => '01db46fc49e2eb7f18a9181bd0d68b1e',
//            'amount'    =>  25,
//        ]);

        //生成8位数字桌号 例 100000001
//        $input = '1';
//        //$no = str_pad($input, 8, '0', STR_PAD_LEFT);
//        $no = $this->serv_util->make_order_id(5);
//        pr($no);

        //单例模式
//        $obj1 = ctl_singleton::getInstance();
//        $obj2 = ctl_singleton::getInstance();
//        var_dump($obj1, $obj2);
//        exit;

        //转时分秒
//        $time = $this->serv_display->second2time(89400);
//        pr($time);

        //通过事件发送邮件
//        $view_data = [
//            '4c1ea0e231e194467d8801f4d70d7a09' => [
//                'realname'  =>  '1',
//                'username'  =>  '2',
//                'code'      =>  '3',
//            ]
//        ];
//        $to = [
//            '4c1ea0e231e194467d8801f4d70d7a09' => [
//                'email' => 'alan025.infinity@gmail.com',
//                'name' => 'peter',
//            ]
//        ];
//        event(new evt_send_mail([
//            'to'        => $to,
//            'subject'   => '测试',
//            'view'      => 'mail.example',
//            'view_data' => $view_data,
//        ]));

        //根据token获取用户信息
//        $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hZG1pbmFwaS5wdGdhbWUubG9jYWxcL2xvZ2luIiwiaWF0IjoxNjYzMzExMzgyLCJleHAiOjE2NjMzOTc3ODIsIm5iZiI6MTY2MzMxMTM4MiwianRpIjoid2EwUEpHWWlEUENvNjVTTyIsInN1YiI6IjEiLCJwcnYiOiI5YTA1ZTEyM2JhOTRlNmQwOGYzNmY3NTFhNDcyMzI0N2RmYzZiNGJjIiwiaHN0IjoiYjgxZGI0OWMzMDJmOGM1ZmNjMTMyODIyNjlhZTgwMzQiLCJpcGEiOiJmNTI4NzY0ZDYyNGRiMTI5YjMyYzIxZmJjYTBjYjhkNiIsInVyYSI6IjYyMGVlYWNjZjBmMDNkYzUxZWE1YTlmMWYzZmI0MzYwIn0.WhKpup3y34R2dkF2JGmrj2ryfgAhxvSSuy13Q1qD2Bo';
//        $user = auth($this->guard)->authenticate($token)->toArray();
//        pr($user['id']);

        //将密码加密
        //pr(bcrypt('agent1_testplayer1002'));

        //rpc调用
        //修改建桌与游戏基本配置时
        //$status = $this->serv_rpc_client->change_config();
        //pr($status);

        //干掉桌子时通知游戏服
//        $status = $this->serv_rpc_client->delete_table(10000002);
//        pr($status);

        //变更房间状态时通知游戏服
//        $status = $this->serv_rpc_client->change_room_status(13);
//        pr($status);

        //封禁玩家时通知游戏服, DELETED = 0, DISABLED = 1, ENABLED = 2
//        $status = $this->serv_rpc_client->change_user_status(['id'    => 'de6f8f6127f553245fbd0bce973a6727', 'status'    => 1]);
//        pr($status);

        //启用玩家时通知游戏服, DELETED = 0, DISABLED = 1, ENABLED = 2
//        $status = $this->serv_rpc_client->change_user_status(['id'    => 'de6f8f6127f553245fbd0bce973a6727', 'status'    => 2]);
//        pr($status);

        //创建房间后通知游戏服
//        $data = [
//            'id'            => 99999999,
//            'game_id'       => 1,
//            'name'          => '测试房间32822',
//            'cover_img'     => 'https://teststatic.wwin.city/image/056/e262a91ef4e1739bb7929522d7a17378.jpg',
//            'video_url'     => 'https://stream7.iqilu.com/10339/upload_transcode/202002/18/20200218114723HDu3hhxqIT.mp4',
//            'desc'          => '测试内容',
//            'sort'          => 0,
//            'status'        => 1,
//        ];
//        $status = $this->serv_rpc_client->create_room($data);
//        pr($status);

        //修改房间后通知游戏服
//        $data = [
//            'id'            => 99999999,
//            'game_id'       => 1,
//            'name'          => '测试房间32822',
//            'cover_img'     => 'https://teststatic.wwin.city/image/056/e262a91ef4e1739bb7929522d7a17378.jpg',
//            'video_url'     => 'https://stream7.iqilu.com/10339/upload_transcode/202002/18/20200218114723HDu3hhxqIT.mp4',
//            'desc'          => '测试内容',
//            'sort'          => 0,
//            'status'        => 1,
//        ];
//        $status = $this->serv_rpc_client->update_room($data);
//        pr($status);

        //充值后通知游戏服
//        $data = [
//            'holder_id'         => 'cc2ad8fa6f5af2f56349044dd1c369ce',
//            'name'              => 'Default Wallet',
//            'slug'              => 'default',
//            'description'       => '',
//            'balance'           => 100.00,
//            'transaction_id'    => '1970080807045221',
//            'currency'          => 'HKD',
//        ];
//        $status = $this->serv_rpc_client->deposit($data, $ret_data);
        //pr($ret_data);

        //提款后通知游戏服
//        $data = [
//            'holder_id'         => 'cc2ad8fa6f5af2f56349044dd1c369ce',
//            'balance'           => 10.00,
//            'transaction_id'    => '19702331307035221', //平台送订单号
//        ];
//        $status = $this->serv_rpc_client->withdraw($data, $ret_data);
//        pr($ret_data);

        //展示牌局图片
//        $img = display_img('card/1/221017/22101711.jpg', '');
//        pr($img);

        //修正转帐记录表 代理id为空
//        $rows = $this->repo_order_transfer->lists(['where' => [
//            ['type', '=', 3],
//            ['amount', '<', 0],
//        ]])->toArray();
//        $uids = sql_in($rows, 'uid');
//
//        //获取用户列表
//        $users = $this->repo_user->get_list([
//            'index' => 'id',
//            'id'    => $uids
//        ]);
//
//        $data = [];
//        foreach($rows as $k => $v)
//        {
//            $data[] = [
//                'id'        => $v['id'],
//                'origin'    => 3,
//                'type'      => 2,
//                //'agent_id'  => isset($users[$v['uid']]) ? $users[$v['uid']]['agent_id'] : '',
//            ];
//        }
//        //批量更新
//        $status = $this->repo_order_transfer->insertOrUpdate($data,
//            ['id'],
//            ['origin', 'type']
//        );

        //开始有签到功能日期
//        $start_date = '2017-01-01';
//        $today_date = '2017-01-12';
//        $start_time = strtotime($start_date);
//        $today_time = strtotime($today_date);
//        //第几天
//        $offset = floor(($today_time - $start_time) / 86400);
//        $uid = 1;
//        $cache_key = sprintf("sign_%d", $uid);
//        Redis::setBit($cache_key, $offset, 1);
//        $status = Redis::getBit($cache_key, $offset);
//        $is_sign = $status == 1 ? '已簽到' : '還沒簽到';
//        //计算总签到次数 2
//        $count = Redis::bitCount($cache_key);
//        var_dump($count);
//        exit;

        //写入登入日志
//        $login_ip = request()->ip();
//        $username = 'agent1_leo555';
//        $status = $this->serv_rpc_client->create_login_log([
//            'uid'           => '76e2bdcb4f773e3525c1e91c545ded11',
//            'username'      => $username,
//            'session_id'    => Session::getId(),
//            'agent'         => request()->userAgent(),
//            'login_time'    => time(),
//            'login_ip'      => $login_ip,
//            'login_country' => ip2country($login_ip),
//            'exit_time'     => 0,
//            'extra_info'    => '',
//            'status'        => mod_user_login_log::ENABLE, //登入成功
//            'cli_hash'      => md5($username.'-'.$login_ip),
//        ]);
//        pr($status);

        //遇锁立刻返回
//        if (!$this->serv_redis->lock('test'))
////        {
////            return res_error();
////        }
////
////        for($i=1; $i<=1000; $i++)
////        {
////            $this->repo_example->save([
////                'do'        => 'add',
////                'cat_id'    => 0,
////                'title'     => random('numeric'),
////                'content'   => '我是内容',
////                'img'       => '',
////                'sort'      => 0,
////                'status'    => mod_example::ENABLE,
////            ]);
////        }
////
////        $this->serv_redis->unlock('test');

        //遇锁等待3秒
//        if ($this->serv_redis->lock('test', 3))
//        {
//            for($i=1; $i<=800; $i++)
//            {
//                $this->repo_example->save([
//                    'do'        => 'add',
//                    'cat_id'    => 0,
//                    'title'     => random('numeric'),
//                    'content'   => '我是内容',
//                    'img'       => '',
//                    'sort'      => 0,
//                    'status'    => mod_example::ENABLE,
//                ]);
//            }
//
//            $this->serv_redis->unlock('test');
//        }

        //筛选json字段
//        $order_id = '2208282152290879784';
//        $rows = DB::table('transactions')
//            ->select(
//                'id',
//                'type',
//                'amount',
//                DB::raw("JSON_EXTRACT(meta, '$.order_id') as order_id"),
//                )
//            ->whereRaw("JSON_EXTRACT(meta, '$.order_id') = '{$order_id}'")
//            ->get()->toArray();
//        $rows = json_decode(json_encode($rows),true); //stdClass转数组
//        pr($rows);

        //测试mongo
        //获取列表
//        $rows = DB::connection('mongodb')
//            ->collection('admin_users_login_log')
//            ->get()->toArray();
//        pr($rows);

//        $rows = DB::connection('mongodb')
//            ->collection('admin_users_login_log')
//            ->where('_id', '=', '62de4e5a5a35000043006793')
//            ->get()->toArray();
//        pr($rows);

        //原生sql
//        $rows = DB::select('select * from pt_room where id = ?', [4]);
//        pr($rows);
        //DB::statement('truncate table pt_example'); //不返回任何数据
        //$affect_num = DB::delete('delete from pt_h5'); //返回删除行数
        //pr($affect_num);
        //$row = DB::select('select * from pt_agents where id = :id', ['id' => '99fd36ecbbe076a38d9575f7cb275bb5']);
        //pr($row);
        //$affect_num = DB::insert('insert into pt_h5(id, name, content) values(?, ?, ?)', ['32328932', 'test', 'xxxx']);
        //pr($affect_num);
//        $affect_num = DB::update('update pt_h5 set name = ?, content = ? where id = ?', ['test', '123', '32328932']);
//        pr($affect_num);
    }
}
