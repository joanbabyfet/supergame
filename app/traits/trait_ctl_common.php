<?php


namespace App\traits;


use App\services\serv_util;
use Illuminate\Http\Request;

trait trait_ctl_common
{
    /**
     * ping
     * 检测用,可查看是否返回信息及时间戳
     * @version 1.0.0
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping()
    {
        return res_success();
    }

    /**
     * 返回客戶端ip
     * @version 1.0.0
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ip(Request $request)
    {
        return res_success(['ip' => $request->ip()]);
    }

    /**
     * 获取图片验证码
     * @version 1.0.0
     * @param Request $request
     * @return string|void
     */
    public function get_captcha(Request $request, serv_util $serv_util)
    {
        //类型，login=登录 register=注册 bind_email=绑定邮箱 edit_phone=修改手机号码 forgot=忘记密码
        $type           = $request->input('type', 'login');
        $captcha_img = [];

        if(in_array($type, ['login', 'register', 'forgot', 'bind_email']))
        {
            $captcha_img = $serv_util->get_captcha();
        }
        return res_success(['captcha' => $captcha_img]);
    }

    /**
     * 重载图片验证码
     * @version 1.0.0
     * @param Request $request
     * @return string|void
     */
    public function reload_captcha(Request $request, serv_util $serv_util)
    {
        //类型，login=登录 register=注册 bind_email=绑定邮箱 edit_phone=修改手机号码 forgot=忘记密码
        $type           = $request->input('type', 'login');
        $captcha_img    = [];

        if(in_array($type, ['login', 'register', 'forgot', 'bind_email']))
        {
            $captcha_img = $serv_util->get_captcha();
        }
        return res_success(['captcha' => $captcha_img]);
    }

    /**
     * 获取角色选项
     * @version 1.0.0
     * @return mixed
     */
    public function get_role_options()
    {
        $rows = $this->repo_role->get_list([
            'guard_name'    => $this->guard,
        ]);
        $options = $this->serv_array->one_array($rows, ['id', 'name']);
        return res_success($options);
    }

    /**
     * 获取房间选项
     * @version 1.0.0
     * @return mixed
     */
    public function get_room_options()
    {
        $rows = $this->repo_room->get_list([]);
        $options = $this->serv_array->one_array($rows, ['id', 'name']);
        return res_success($options);
    }
}
