<?php

use App\lib\response;
use App\services\serv_array;
use App\services\serv_display;
use App\services\serv_req;
use App\services\serv_util;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 全局函数
 */
if (!function_exists('pr')) {
    /**
     * 打印
     * @param array $data
     */
    function pr($data = [])
    {
        echo '<pre>';
        print_r($data);
        exit;
    }
}

if (!function_exists('request_filter')) {
    /**
     * 获取过滤后请求参数
     * @param $keys
     * @return array
     */
    function request_filter($fields)
    {
        if(empty($fields)) return [];
        $ret = array_filter(request()->only(is_array($fields) ? $fields : func_get_args()));
        return $ret;
    }
}

if (!function_exists("user_can"))
{
    /**
     * 检测用户是否有权限
     * @param $permission 权限
     * @param null $guard 守卫
     * @return mixed
     */
    function user_can($permission, $guard = null)
    {
        return auth($guard)->user()->can($permission);
    }
}

if (!function_exists("user_has_role"))
{
    /**
     * 检测用户是否有该角色
     * @param $role 角色 格式 [1,2,3]
     * @param null $guard 守卫
     * @return mixed
     */
    function user_has_role(array $role, $guard = null)
    {
        //必须为int不然会报错
        $role = array_map('intval', $role);
        return auth($guard)->user()->hasAnyRole($role);
    }
}

if (!function_exists("make_tree")) {
    /**
     * 生成树
     * @param array $data 数据
     * @param int $pid 上级id 0=根节点
     * @return array
     */
    function make_tree(array $data, $field_id = 'id', $field_pid = 'pid', $pid = 0)
    {
        $tree = [];
        if (empty($data)) return $tree;

        $field_id = empty($field_id) ? 'id' : $field_id;
        $field_pid = empty($field_pid) ? 'pid' : $field_pid;

        $rows = [];
        foreach ($data as $k => $v) {
            $rows[$v[$field_id]] = $v; //获取字段id当键名
        }

        foreach ($data as $item)
        {
            if ($pid == $item[$field_pid]) //上级节点=根节点0
            {
                $tree[] = &$rows[$item[$field_id]];
            }
            elseif (isset($rows[$item[$field_pid]]))
            {
                $rows[$item[$field_pid]]['children'][] = &$rows[$item[$field_id]];
            }
        }
        return $tree;
    }
}

if (!function_exists('get_default_guard')) {
    /**
     * 获取当前守卫
     * @return mixed
     */
    function get_default_guard()
    {
        return Auth::getDefaultDriver();
    }
}

if (!function_exists('get_purviews')) {
    /**
     * 获取已认证用户权限
     * 用户权限 = 用户权限 + 组权限
     * @param array $data
     * @return array
     */
    function get_purviews(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'guard'        => '',
            'field'        => '',
        ], $data);

        $default_guard = get_default_guard();//默认守卫
        $guard = empty($data_filter['guard']) ? $default_guard : $data_filter['guard'];
        $field = empty($data_filter['field']) ? 'id' : $data_filter['field']; //默认返回id字段

        //获取该用户全部权限
        $purviews = auth($guard)->user()->getAllPermissions()
            ->pluck($field)->toArray(); //一律返回id,不要用name路由别名
        //检测是否有超级管理员权限
        if ( auth($guard)->user()->hasRole(config('global.role_super_admin')) ) //1=超级管理员
        {
            $purviews = ['*'];
        }

        return $purviews;
    }
}

if(!function_exists('page_error'))
{
    /**
     * 显示自定义错误页面
     * @param array $data
     * @return \Illuminate\Http\Response
     */
    function page_error(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'code'        => 'required',
        ], $data);

        $code = empty($data_filter) ? '404' : $data_filter['code']; //http_code

        return response()->view("system.{$code}", [], $code);
    }
}

if(!function_exists('display_img'))
{
    /**
     * 显示图片
     * @param $img
     * @param string $dir
     * @return \Illuminate\Http\Response
     */
    function display_img($img, $dir = 'image')
    {
        return app(serv_display::class)->img($img, $dir);
    }
}

if(!function_exists('display_money'))
{
    /**
     * 显示金額 第二位四捨五入 500.545 => 500.55 500.544 => 500.54
     * @param $money
     * @param string $separator
     * @return mixed
     */
    function display_money($money, $separator=',')
    {
        return app(serv_display::class)->money($money, $separator);
    }
}
if (!function_exists('pr_sql_start')) {
    /**
     * 开启sql打印
     * @param array $data
     */
    function pr_sql_start($data = [])
    {
        DB::enableQueryLog();
    }
}
if (!function_exists('pr_sql_end')) {
    /**
     * 打印sql
     * @param array $data
     */
    function pr_sql_end($data = [])
    {
        echo '<pre>';
        print_r(DB::getQueryLog());
        exit;
    }
}
if (!function_exists('pr_mongo_start')) {
    /**
     * 开启mongo语法打印
     * @param array $data
     */
    function pr_mongo_start($data = [])
    {
        DB::connection('mongodb')->enableQueryLog();
    }
}
if (!function_exists('pr_mongo_end')) {
    /**
     * 打印mongo语法
     * @param array $data
     */
    function pr_mongo_end($data = [])
    {
        echo '<pre>';
        print_r(DB::connection('mongodb')->getQueryLog());
        exit;
    }
}
if (!function_exists('data_filter')) {
    /**
     * 该函数用于过滤，对用户提交的数据进行处理
     *
     * @param $filter
     * @param $data
     * @param bool $magic_slashes
     */
    function data_filter($filter, $data, $msg = [], $magic_slashes = true)
    {
        return app(serv_util::class)->data_filter($filter, $data, $msg, $magic_slashes);
    }
}
if (!function_exists('random')) {
    /**
     * 生成唯一识别
     * @param string $type
     * @param int $length
     * @return mixed
     */
    function random($type = 'web', $length = 32)
    {
        return app(serv_util::class)->random($type, $length);
    }
}
if (!function_exists('logger')) {
    /**
     * 获取异常信息
     * @param $status
     * @return mixed|string
     */
    function logger($name, $data)
    {
        return app(serv_util::class)->logger($name, $data);
    }
}
if (!function_exists('res_success')) {
    /**
     * API成功响应
     * @param array $data
     * @param string $msg
     * @return mixed
     */
    function res_success($data = [], $msg = 'success')
    {
        return app(serv_util::class)->res_success($data, $msg);
    }
}
if (!function_exists('res_error')) {
    /**
     * API失败响应
     * @param string $msg
     * @param $code
     * @param array $data
     * @return mixed
     */
    function res_error($msg = 'error', $code = response::FAIL, $data = [])
    {
        return app(serv_util::class)->res_error($msg, $code, $data);
    }
}
if (!function_exists('res_invalid_params')) {
    /**
     * 參數錯誤
     * @return \Illuminate\Http\JsonResponse
     */
    function res_invalid_params()
    {
        return app(serv_util::class)->invalid_params();
    }
}
if (!function_exists('res_unknown_error')) {
    /**
     * 服务异常
     * @return \Illuminate\Http\JsonResponse
     */
    function res_unknown_error()
    {
        return app(serv_util::class)->unknown_error();
    }
}
if (!function_exists('res_no_permission')) {
    /**
     * 无权限
     * @return \Illuminate\Http\JsonResponse
     */
    function res_no_permission()
    {
        return app(serv_util::class)->no_permission();
    }
}
if (!function_exists('ip2country')) {
    /**
     * 获得国家代码 例 SG或KH
     * @param string $ip
     * @return string
     */
    function ip2country($ip = '')
    {
        return app(serv_util::class)->ip2country($ip);
    }
}
if (!function_exists('date_convert_timestamp')) {
    /**
     * 时间转时间戳
     * @param $date
     * @param $timezone
     * @return mixed
     */
    function date_convert_timestamp($date, $timezone)
    {
        return app(serv_util::class)->date_convert_timestamp($date, $timezone);
    }
}
if (!function_exists('format_date')) {
    /**
     * 格式化时间输出
     * @param $timestamp
     * @param $timezone
     * @param string $format
     * @return string
     */
    function format_date($timestamp, $timezone, $format='Y/m/d H:i')
    {
        return app(serv_util::class)->format_date($timestamp, $timezone, $format);
    }
}
if (!function_exists('time_convert')) {
    /**
     * 不同时区时间转换
     * @param array $data
     * @return string
     */
    function time_convert($data = array())
    {
        return app(serv_util::class)->time_convert($data);
    }
}
if (!function_exists('get_admin_timezone')) {
    /**
     * 获取管理后台时区
     * @param $date
     * @param $timezone
     * @return mixed
     */
    function get_admin_timezone()
    {
        return app(serv_util::class)->get_admin_timezone();
    }
}
if (!function_exists('get_lang_field')) {
    /**
     * 获取多语言字段名
     * @param $field
     * @param string $lang
     * @return string
     */
    function get_lang_field($field, $lang='')
    {
        return app(serv_util::class)->get_lang_field($field, $lang);
    }
}
if (!function_exists('curl_post')) {
    /**
     * curl表单POST请求提交
     * @param $url
     * @param array $parameter
     * @param array $header
     * @return string
     */
    function curl_post($url, $parameter = [], $header = [])
    {
        return app(serv_util::class)->curl_post($url, $parameter, $header);
    }
}
if (!function_exists('curl_get')) {
    /**
     * curl get请求提交
     * @param $url
     * @param array $header
     * @return bool|string
     */
    function curl_get($url, $header = [])
    {
        return app(serv_util::class)->curl_get($url, $header);
    }
}
if (!function_exists('api_get')) {
    /**
     * Guzzle get请求提交
     * @param $url
     * @param array $parameter
     * @param array $header
     * @return bool|string
     */
    function api_get($url, $parameter = [], $header = [])
    {
        return app(serv_util::class)->api_get($url, $parameter, $header);
    }
}
if (!function_exists('api_post')) {
    /**
     * Guzzle表单POST请求提交
     * @param $url
     * @param array $parameter
     * @param array $header
     * @return bool|string
     */
    function api_post($url, $parameter = [], $header = [])
    {
        return app(serv_util::class)->api_post($url, $parameter, $header);
    }
}
if (!function_exists('get_action')) {
    /**
     * 获取当前请求的方法名称
     * @return mixed
     */
    function get_action()
    {
        return app(serv_util::class)->get_action();
    }
}
if (!function_exists('get_controller')) {
    /**
     * 获取当前请求的控制器名称
     * @return mixed
     */
    function get_controller()
    {
        return app(serv_util::class)->get_controller();
    }
}
if (!function_exists('check_password')) {
    /**
     * 检测密码
     * @param $password
     * @param $hash_password 数据库保存的密文
     * @return mixed
     */
    function check_password($password, $hash_password)
    {
        return app(serv_util::class)->check_password($password, $hash_password);
    }
}
if (!function_exists('hash_password')) {
    /**
     * 用户密码加密接口,默认使用算法bcrypt,长度默认60字元
     * @param $password
     * @return string
     */
    function hash_password($password)
    {
        return app(serv_util::class)->hash_password($password);
    }
}
if (!function_exists('check_sign')) {
    /**
     * 检查签名
     * @param array $data
     * @param $app_key
     * @param $check_sign
     * @return bool
     */
    function check_sign(array $data, $app_key, $check_sign)
    {
        return app(serv_util::class)->check_sign($data, $app_key, $check_sign);
    }
}
if (!function_exists('sign')) {
    /**
     * 签名方法
     * @param array $data
     * @param $app_key
     * @param array $exclude
     * @return string
     */
    function sign(array $data, $app_key, $exclude = ['sign'])
    {
        return app(serv_util::class)->sign($data, $app_key, $exclude);
    }
}
if (!function_exists('aes_encrypt')) {
    /**
     * AES加密, 对称加密, 加解密都用同1组密钥, 场景: 参数值加密
     * @param $data
     * @param $key 密钥,16个字符
     * @param $iv 加密向量,16个字符, 使用AES-128-ECB不需设置iv
     * @param $method 加密模式, 默认AES-128-ECB
     * @return false|string
     */
    function aes_encrypt($data, $key, $iv = '', $method = '')
    {
        return app(serv_util::class)->aes_encrypt($data, $key, $iv, $method);
    }
}
if (!function_exists('aes_decrypt')) {
    /**
     * AES解密
     * @param $data
     * @param $key 密钥,16个字符
     * @param $iv 加密向量,16个字符, 使用AES-128-ECB不需设置iv
     * @param $method 加密模式, 默认AES-128-ECB
     * @return false|string
     */
    function aes_decrypt($data, $key, $iv = '', $method = '')
    {
        return app(serv_util::class)->aes_decrypt($data, $key, $iv, $method);
    }
}
if (!function_exists('money')) {
    /**
     * 金额, 第二位四捨五入 例 500.545 => 500.55 500.544 => 500.54
     * @param $money
     * @param string $separator
     * @return string
     */
    function money($money, $separator=',')
    {
        return app(serv_display::class)->money($money, $separator);
    }
}
if (!function_exists('second2time')) {
    /**
     * 將秒数转换为时分秒的格式
     * gmdate從php8.1棄用會返回空字符串, 改用date
     * @param Int $times 时间，单位 秒
     * @return String
     */
    function second2time($seconds)
    {
        return app(serv_display::class)->second2time($seconds);
    }
}
if (!function_exists('one_array')) {
    /**
     * 转一维数组
     * @param array $array
     * @param array $key_pair
     * @return array
     */
    function one_array(array $array,array $key_pair)
    {
        return app(serv_array::class)->one_array($array, $key_pair);
    }
}
if (!function_exists('make_order_id')) {
    /**
     * 生成订单id, bigint支持数字大小范围刚好19位
     * @param int $num 后缀几个数字
     * @return string
     */
    function make_order_id($num = 7)
    {
        return app(serv_util::class)->make_order_id($num);
    }
}
if (!function_exists('sql_in')) {
    /**
     * IN (ID) 使用的一维数组
     * @param array $array
     * @param $field
     * @return array
     */
    function sql_in(array $array, $field)
    {
        return app(serv_array::class)->sql_in($array, $field);
    }
}
if (!function_exists('is_mobile')) {
    /**
     * 判断是否为手机
     * @return bool
     */
    function is_mobile()
    {
        return app(serv_req::class)->is_mobile();
    }
}
