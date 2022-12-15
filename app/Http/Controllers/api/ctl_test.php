<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\repositories\repo_admin_user;
use App\repositories\repo_app_key;
use App\repositories\repo_config;
use App\repositories\repo_game;
use App\repositories\repo_member_increase_data;
use App\repositories\repo_user;
use App\services\serv_req;
use App\services\serv_sys_mail;
use App\services\serv_sys_sms;
use App\services\serv_util;
use App\services\serv_wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * 测试用控制器
 * Class ctl_test
 * @package App\Http\Controllers\api
 */
class ctl_test extends Controller
{
    private $serv_util;
    private $serv_req;
    private $repo_user;

    public function __construct(
        serv_util $serv_util,
        serv_req $serv_req,
        repo_user $repo_user
    )
    {
        parent::__construct();
        $this->serv_util = $serv_util;
        $this->serv_req = $serv_req;
        $this->repo_user = $repo_user;
    }

    public function index(Request $request)
    {
//        $ip = '34.124.199.205';
//        return res_success([
//            'country' => ip2country($ip),
//            'lang'    => app()->getLocale(),
//        ]);

        //获取当前用户
//        $user = Auth::user();
//        $user->balance; //初始化钱包
//        $user->deposit(200);
        //var_dump($user->balance); //查看
        //$user->withdraw(50);

        //获取签名字段
//        $data = [
//            'app_id'   => '2207271525353540508',
//            'username' => 'leo555',
//        ];
//        $sign = $this->serv_util->sign($data, 'fb15be6bf27d1f5121a4de5d751fa49b', ['sign']);
//        pr($sign);

        //检测是否为手机
//        $is_mobile = $this->serv_req->is_mobile();
//        var_dump($is_mobile);

        //AES加密
//        $betlimit = 'USD-A,USD-C,USD-E'; //下注限红代码
//        $app_key = 'TD4E6In7K7GKjO8dbO3zfC8zdsbjMIaxh2DprfI9P5Y=';
//        $ret = aes_encrypt($betlimit, md5($app_key));
//        pr($ret);

        //AES解密
//        $data = 'l4uUg++aMI8z5m1GZvmwX4T95RgYn8q30g84dkHixTI=';
//        $app_key = 'TD4E6In7K7GKjO8dbO3zfC8zdsbjMIaxh2DprfI9P5Y=';
//        $ret = aes_decrypt($data, md5($app_key));
//        pr($ret);

        //通过uid获取token
//        $uid = 'cc2ad8fa6f5af2f56349044dd1c369ce';
//        $token = $this->repo_user->get_token($uid);
//        pr($token);
    }
}
