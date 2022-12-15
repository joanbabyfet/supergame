<?php


namespace App\services;


class serv_req
{
    /**
     * 获取token认证令牌
     * 优先级：请求头部,客户端token
     *
     * @return array|string|null
     */
    public function get_token()
    {
        $token = request()->input('_token', ''); //從 $_SERVER 服务器全局数组 获取值
        //$token = empty($token) ? request()->server('HTTP_AUTHORIZATION', '') : $token; //会包含Bearer
        $token = empty($token) ? request()->bearerToken() : $token;

        return $token;
    }

    /**
     * 获取客户端语言
     * 优先级：请求头部,客户端浏览器语言,配置文件zh-tw
     *
     * @return array|string|null
     */
    public function get_language()
    {
        $lang = request()->header('language', '');
        $lang = empty($lang) ? request()->server('HTTP_LANGUAGE', '') : $lang;
        $lang = empty($lang) ? config('app.locale') : strtolower($lang); //config('app.locale') 应用默认语言

        return $lang;
    }

    /**
     * 获取当前版本号
     * 优先级：客户端版本号,请求头部
     *
     * @return array|string|null
     */
    public function get_version()
    {
        $version = request()->server('HTTP_VERSION', '');
        $version = empty($version) ? request()->header('version', '') : $version;
        $version = empty($version) ? '' : $version;

        return $version;
    }

    /**
     * 获取客户端系统信息
     * 优先级：客户端系统信息,请求头部
     *
     * @return array|string|null
     */
    public function get_os_info()
    {
        $os = request()->server('HTTP_OS', '');
        $os = empty($os) ? request()->header('os', '') : $os;
        $os = empty($os) ? '' : $os;

        return $os;
    }

    /**
     * 获取客户端时区
     * 优先级：客户端系统信息,请求头部,配置文件
     *
     * @return array|\Illuminate\Config\Repository|mixed|string|null
     */
    public function get_timezone()
    {
        $timezone = request()->server('HTTP_TIMEZONE', '');
        $timezone = empty($timezone) ? request()->header('timezone', '') : $timezone;
        $timezone = empty($timezone) ? config('app.timezone') : $timezone;

        return $timezone;
    }

    /**
     * 获取系统類型
     * 优先级：客户端系统類型,请求头部
     *
     * @return array|string|null
     */
    public function get_os_type()
    {
        $os = request()->server('HTTP_OS', '');
        $os = empty($os) ? request()->header('os', '') : $os;

        if (strpos(strtolower($os), 'android') !== false)
        {
            $os = 'android';
        }
        elseif (strpos(strtolower($os), 'ios') !== false)
        {
            $os = 'ios';
        }
        elseif ($os === 'web')
        {
            $os = 'web';
        }

        return $os;
    }

    /**
     * 判断是否为手机
     * @return bool
     */
    public function is_mobile()
    {
        //正则表达式,批配不同手机浏览器UA关键词。
        $regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
        $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|";
        $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
        $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
        $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320×320|240×320|176×220";
        $regex_match .= "|mqqbrowser|juc|iuc|ios|ipad";
        $regex_match .= ")/i";

        //有HTTP_X_WAP_PROFILE则一定是移动设备
        return isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE']) or preg_match($regex_match, strtolower(request()->userAgent()));
    }
}
