<?php


namespace App\services;


use App\lib\response;
use App\repositories\repo_api_req_log;
use App\traits\trait_service_base;
use GeoIp2\Database\Reader;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class serv_util
{
    use trait_service_base;

    private $serv_display;

    //上传目录数量
    public static $dir_num = 128;
    //分享码来源字符串
    private static $sharecode_source_str = "7S3GQXNCPEFJ8MKU6L2AVW9RDZHY54IOBT1";
    //分享类型
    public static $share_type_map = [
    ];

    public function __construct(serv_display $serv_display)
    {
        $this->serv_display     = $serv_display;
    }

    //寫入日誌封裝
    public function logger($name, $data)
    {
        //項目名
        $app_name = config('app.name');

        $data_str = $data;
        if(is_array($data) || is_object($data))
        {
            $data_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        //有狀態錯誤則記錄到錯誤日誌
        if (isset($data['status']) && $data['status'] <= 0)
        {
            log::error("{$app_name}->{$name}->{$data_str}\n\n");
        }
        //普通日誌
        else
        {
            log::info("{$app_name}->{$name}->{$data_str}\n\n");
        }

        return true;
    }

    /**
     * 生成唯一识别
     * @param string $type  類型
     * @param int $length   字元长度
     * @return string
     */
    public function random($type = 'web', $length = 32)
    {
        switch($type)
        {
            case 'basic':
                return mt_rand();   //使用 Mersenne Twister 算法返回随机整数
                break;
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
            case 'distinct':
            case 'hexdec':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    default:
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;

                    case 'numeric':
                        $pool = '0123456789';
                        break;

                    case 'nozero':
                        $pool = '123456789';
                        break;

                    case 'distinct':
                        $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                        break;

                    case 'hexdec':
                        $pool = '0123456789abcdef';
                        break;
                }

                $str = '';
                for ($i=0; $i < $length; $i++)
                {
                    $str .= substr($pool, mt_rand(0, strlen($pool) -1), 1);
                }
                return $str;
                break;
            case 'sha1' :
                return sha1(uniqid(mt_rand(), true));
                break;
            case 'uuid':
                $pool = ['8', '9', 'a', 'b'];
                return sprintf('%s-%s-4%s-%s%s-%s',
                    static::random('hexdec', 8),
                    static::random('hexdec', 4),
                    static::random('hexdec', 3),
                    $pool[array_rand($pool)],
                    static::random('hexdec', 3),
                    static::random('hexdec', 12));
                break;
            case 'unique':
                //会产生大量的重复数据
                //$str = uniqid();
                //生成的唯一标识中没有重复
                //版本>=7.1,使用 session_create_id()
                $str = version_compare(PHP_VERSION,'7.1.0','ge') ? md5(session_create_id()) : md5(uniqid(md5(microtime(true)),true));
                if ( $length == 32 )
                {
                    return $str;
                }
                else
                {
                    return substr($str, 8, 16);
                }
                break;
            case 'web':
                // 即使同一个IP，同一款浏览器，要在微妙内生成一样的随机数，也是不可能的
                // 进程ID保证了并发，微妙保证了一个进程每次生成都会不同，IP跟AGENT保证了一个网段
                // md5(当前进程id在目前微秒时间生成唯一id + 当前ip + 当前浏览器)
                $remote_addr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'; //兼容cli本地调用时会报错
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? ''; //兼容cli本地调用时会报错
                $str = md5(getmypid().uniqid(md5(microtime(true)),true).$remote_addr.$user_agent);
                if ( $length == 32 )
                {
                    return $str;
                }
                else
                {
                    return substr($str, 8, 16);
                }
                break;
            default:
        }
    }

    /**
     * 输出JSON
     * @param array $array
     * @return \Illuminate\Http\JsonResponse
     */
    public function exit_json(array $array)
    {
        //api接口才寫入日志
        if(defined('IN_ADMIN') || defined('IN_ADMINAG') || defined('IN_API') || defined('IN_CLIENT'))
        {
            //写入api访问日志, 用守卫名称来区分, 游戏sdk用api名称
            app(repo_api_req_log::class)->add_log([
                'type'     => defined('IN_ADMIN') ? config('global.admin.guard') :
                    (defined('IN_ADMINAG') ? config('global.adminag.guard') :
                        (defined('IN_CLIENT') ? config('global.client.guard') : 'api')),
                'url'      => request()->path(), //接口地址 例example
                'method'   => request()->method(),
                'res_data' => $array
            ]);
        }

        //header('Content-type: application/json'); //定義嚮應頭部
        //print json_encode($array);
        //exit();
        return response()->json($array);
    }

    /**
     * API成功响应
     * @param array $data
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse
     */
    public function res_success($data = [], $msg = 'success')
    {
        $array = [
            'code'      => response::SUCCESS,
            'msg'       => (string)$msg,
            'timestamp' => time(),
            'data'      => $data
        ];
        return $this->exit_json($array);
    }

    /**
     * API失败响应
     * @param string $msg
     * @param int $code
     * @param array $data
     * @return \Illuminate\Http\JsonResponse
     */
    public function res_error($msg = 'error', $code = response::FAIL, $data = [])
    {
        $array = [
            'code'      => (int)$code,
            'msg'       => (string)$msg,
            'timestamp' => time(),
            'data'      => $data
        ];
        return $this->exit_json($array);
    }

    /**
     * 參數錯誤
     * @return \Illuminate\Http\JsonResponse
     */
    public function invalid_params()
    {
        return $this->res_error(trans('api.api_param_error'), response::ERROR);
    }

    /**
     * 服务异常
     * @param string $msg
     * @return \Illuminate\Http\JsonResponse
     */
    public function unknown_error($msg = '服务异常，请稍后重试')
    {
        //写入日志
        $this->logger(__METHOD__, [
            'req_data'  => request()->all(), //送的全部參數
            'msg'       => $msg,
            'status'    => response::ERROR,
        ]);

        return $this->res_error(trans('api.api_server_error'), response::ERROR);
    }

    /**
     * 无权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function no_permission()
    {
        return $this->res_error(trans('api.api_no_permission'), response::ERROR);
    }

    // 字符串转数字，用于分表和图片分目录
    public function str2number($str, $maxnum = 128)
    {
        // 位数
        $bitnum = 1;
        if ($maxnum >= 100)
        {
            $bitnum = 3;
        }
        elseif ($maxnum >= 10)
        {
            $bitnum = 2;
        }

        // sha1:返回一个40字符长度的16进制数字
        $str = sha1(strtolower($str));
        // base_convert:进制建转换，下面是把16进制转成10进制，方便做除法运算
        // str_pad:把字符串填充为指定的长度，下面是在左边加0，共 $bitnum 位
        $str = str_pad(base_convert(substr($str, -2), 16, 10) % $maxnum, $bitnum, "0", STR_PAD_LEFT);
        return $str;
    }

    /**
     * 检查路径是否存在
     * @param $path
     * @return bool
     */
    public function path_exists($path)
    {
        $pathinfo = pathinfo($path . '/tmp.txt');

        if ( !empty( $pathinfo ['dirname'] ) )
        {
            if (file_exists ( $pathinfo ['dirname'] ) === false)
            {
                if (@mkdir ( $pathinfo ['dirname'], config('global.permission.directory'), true ) === false)
                {
                    return false;
                }
            }
        }
        return $path;
    }

    /**
     * 自定义数组转字符串
     *
     * @param array $arr
     * @return string
     */
    public function array_to_str($arr = [])
    {
        if(empty($arr))
        {
            return '';
        }
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 自定义字符串转数组
     *
     * @param string $str
     * @return mixed|string
     */
    public function str_to_array($str = '')
    {
        if(empty($str))
        {
            return '';
        }

        return json_decode($str, true);
    }

    /**
     * 获取随机伪造IP
     *
     * @return string
     */
    public function get_random_client_ip()
    {
        $ip = rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255);
        return $ip;
    }

    /**
     * 将远程图片下载至本地
     *
     * @param string $img_url
     * @return string
     */
    public function get_remote_image($img_url = '', $dir = '')
    {
        $filename = $this->get_remote_image_name($img_url, $dir);

        // 如果需要分隔目录上传
        $upload_dir = storage_path('app/public/');
        $upload_dir = empty($dir) ? $upload_dir : "{$upload_dir}{$dir}/";

        if(file_exists($upload_dir.$filename))
        {
            return $filename;
        }

        //如果没有http或者https则补默认http前缀
        if(strpos($img_url,'//') === 0)
        {
            $img_url = 'http:'.$img_url;
        }

        //防止空格下载不了
        $img_url = str_replace(' ', '%20', $img_url);

        $imgdata = @file_get_contents($img_url);//獲取數據流

        if($imgdata === false)
        {
            return $img_url;
        }

        file_put_contents($upload_dir.$filename, $imgdata);

        return $filename;
    }

    /**
     * 获取上传远程图片名称
     *
     * @param string $img_url
     * @param string $dir
     * @param string $content_type 文件類型
     * @return string
     */
    public function get_remote_image_name($img_url = '', $dir = '', $content_type = '')
    {
        //如果不是URL的图片略过
        if(strpos($img_url,'//') === false)
        {
            return $img_url;
        }

        //如果没有http或https则补默认http前缀
        if(strpos($img_url,'//') === 0)
        {
            $img_url = 'http:'.$img_url;
        }

        //防止空格下载不了
        $img_url = str_replace(' ', '%20', $img_url);

        $image_suffix = $this->get_image_suffix($img_url);

        $filename = @md5_file($img_url);
        $filename = empty($filename) ? random('web') : $filename;
        $filename = $filename.$image_suffix;

        //如果需要分隔目录上传
        $upload_dir = storage_path('app/public/');
        $upload_dir = empty($dir) ? $upload_dir : "{$upload_dir}{$dir}/";

        if (self::$dir_num > 0)
        {
            $dir_num = $this->str2number($filename, self::$dir_num);
            //檢測目錄是否存在,不存在則創建
            $this->path_exists($upload_dir.'/'.$dir_num);
            $filename = $dir_num.'/'.$filename;
        }

        return empty($dir) ? $filename : "{$dir}/{$filename}";
    }

    /**
     * 获取图片后缀
     *
     * @param string $image_url
     * @param string $content_type 文件類型
     * @return bool|string
     */
    public function get_image_suffix($image_url = '', $content_type = '')
    {
        if(empty($image_url))
        {
            return false;
        }

        if(strpos($image_url, 'png') !== false)
        {
            $image_suffix = '.png';
        }
        elseif (strpos($image_url, 'jpg') !== false)
        {
            $image_suffix = '.jpg';
        }
        elseif (strpos($image_url, 'gif') !== false)
        {
            $image_suffix = '.gif';
        }
        elseif (strpos($image_url, 'jpeg') !== false)
        {
            $image_suffix = '.jpg';
        }
        elseif(strpos($image_url,'.bmp') !== false)
        {
            $image_suffix = '.bmp';
        }
        else
        {
            return false;
        }

        return $image_suffix;
    }

    //获取服务器文件链接
    public function get_server_file_url($file, $dir = '')
    {
        if (empty($file))
        {
            return '';
        }
        return Storage::disk('public')->url("{$dir}/{$file}");
    }

    /**
     * 获取管理后台时区
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    public function get_admin_timezone()
    {
        return config('global.to_timezone'); //例：金邊所在時區 ETC/GMT-7
    }

    //分頁器
    public function pages($total, $page_size = 10, $page_no = null, $page_name = 'page')
    {
        //防止$page_size字段为空字符串而报错
        $page_size = !empty($page_size) ? $page_size : 10;
        $pages = new LengthAwarePaginator([], $total, $page_size, $page_no, [
            //分頁地址
            'path' => Paginator::resolveCurrentPath(),
            //第幾頁參數命名
            'pageName' => $page_name,
        ]);

        return $pages;
    }

    /**
     * 用户密码加密接口,默认使用算法bcrypt,长度默认60字元
     * @param $password
     * @return string
     */
    public function hash_password($password)
    {
        //return bcrypt($password);
        return app('hash')->make($password);
    }

    /**
     * 检测密码
     * @param $password 明文
     * @param $hash_password 数据库保存的密文
     * @return mixed
     */
    public function check_password($password, $hash_password)
    {
        return Hash::check($password, $hash_password);
    }

    /**
     * 生成token, 32位
     * @return string
     */
    public function make_token()
    {
        return $this->random('web');
    }

    /**
     * 检查签名
     *
     * @param array $data
     * @param $app_key
     * @param $check_sign 客户端送过来的签名
     * @return bool
     */
    public function check_sign(array $data, $app_key, $check_sign)
    {
        $_sign = $this->sign($data, $app_key);
        return $_sign === $check_sign;
    }

    /**
     * 签名方法
     *
     * @param array $data
     * @param $app_key 私鑰
     * @param array $exclude 不参加签名参数
     * @return string
     */
    public function sign(array $data, $app_key, $exclude = ['sign'])
    {
        //干掉sign参数
        if (!empty($exclude) && is_array($exclude))
        {
            foreach ($exclude as $key)
            {
                unset($data[$key]);
            }
        }

        ksort($data); //依键名做正序

        $query_str = http_build_query($data); //转成 a=xxx&b=xxx
        $query_arr = explode('&', $query_str);
        //由于http_build_query会对参数进行一次urlencode，所以这里需要加多一层urldecode
        $query_arr = array_map(function ($item) {
            return urldecode($item); //例：%E6%9D%8E%E8%81%B0%E6%98%8E => 李聰明
        }, $query_arr);

        $sign_text = implode('&', $query_arr);
        $sign_text .= '&key=' . $app_key;
        return strtoupper(md5($sign_text)); //md5不支持解密回原来字符串
    }

    /**
     * AES加密, 对称加密, 加解密都用同1组密钥, 场景: 参数值加密
     * @param $data
     * @param $key 密钥,16个字符
     * @param $iv 加密向量,16个字符, 使用AES-128-ECB不需设置iv
     * @param $method 加密模式, 默认AES-128-ECB
     * @return false|string
     */
    public function aes_encrypt($data, $key, $iv = '', $method = '')
    {
        $method = empty($method) ? 'AES-128-ECB' : $method;
        $iv     = empty($iv) ? '' : $iv;
        $data   = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($data);
    }

    /**
     * AES解密
     * @param $data
     * @param $key 密钥,16个字符
     * @param $iv 加密向量,16个字符, 使用AES-128-ECB不需设置iv
     * @param $method 加密模式, 默认AES-128-ECB
     * @return false|string
     */
    public function aes_decrypt($data, $key, $iv = '', $method = '')
    {
        $method = empty($method) ? 'AES-128-ECB' : $method;
        $iv     = empty($iv) ? '' : $iv;
        $data   = base64_decode($data);
        $data   = openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        return $data;
    }

    /**
     * 该函数用于过滤，对用户提交的数据进行处理
     *
     * @param $filter
     * @param $data
     * @param bool $magic_slashes
     */
    public function data_filter($filter, $data, $msg = [], $magic_slashes = true)
    {
        $ret = [];
        $msg = empty($msg) ? ['required' => ':attribute']:$msg; //只返回错误字段名
        $validator = Validator::make($data, $filter, $msg);

        if($validator->fails())
        {
            //返回第一个字段错误信息
            $ret = current($validator->errors()->all());
        }
        else
        {
            $ret = $data;
        }

        return $ret;
    }

    /**
     * 解码分享码
     * @param $code
     * @return float|int
     */
    public function decode_share_code($code)
    {
        $source_string = self::$sharecode_source_str;

        if (strrpos($code, '0') !== false)  //例 分享码0006NQ则返回最后一次出现位置2
        {
            $code = substr($code, strrpos($code, '0') + 1); //排除分享码0, 返回6NQ
        }

        $len  = strlen($code); //长度3
        $code = strrev($code); //返转字符串,返回QN6
        $num  = 0;

        for ($i=0; $i < $len; $i++)
        {
            $num += strpos($source_string, $code[$i]) * pow(35, $i); //4 * pow(35, 0)
        }
        return $num;
    }

    /**
     * 格式化时间输出
     *
     * @param $timestamp
     * @param $timezone
     * @param string $format
     * @return string
     */
    public function format_date($timestamp, $timezone, $format='Y/m/d H:i')
    {
        if (empty($timestamp))
        {
            return '';
        }

        //检查时区是否合法，防止时区乱写报错，不合法使用默认时区
        try
        {
            new \DateTimeZone($timezone);
        }
        catch(\Exception $e)
        {
            $timezone = config('global.to_timezone');
        }
        return $this->time_convert(['datetime' => $timestamp, 'to_timezone' => $timezone, 'format' => $format]);
    }

    /**
     * 不同时区时间转换
     * @param  array  $data
     * mod_common::time_convert([
     *      'datetime'      => KALI_TIMESTAMP,//可以是时间格式或者时间戳
     *      'from_timezone' => 'ETC/GMT-7',//默认为系统设置的时区，即 ETC/GMT
     *      'to_timezone'   => 'ETC/GMT-8',//转换成为的时区，默认获取用户所在国家对应时区
     *      'format'        => ''//格式化输出字符串。默认为Y-m-d H:i:s
     * ]);
     *
     * 一般直接使用 mod_common::time_convert(['datetime' => xxxxx]);
     * @return string
     */
    public function time_convert($data = array())
    {
        $datetime      = empty($data['datetime']) ? time() : $data['datetime'];
        $datetime      = is_numeric($datetime) ? '@'.$datetime : $datetime;
        $from_timezone = empty($data['from_timezone']) ? config('app.timezone') : $data['from_timezone'];
        //需要转化的时区
        $to_timezone   = empty($data['to_timezone']) ? config('global.to_timezone') : $data['to_timezone'];
        $format        = empty($data['format']) ? 'Y-m-d H:i:s' : $data['format'];

        $date_obj = new \DateTime($datetime, new \DateTimeZone($from_timezone));
        $date_obj->setTimezone(new \DateTimeZone($to_timezone));
        return $date_obj->format($format);
    }

    /**
     * 时间转时间戳
     * @param $date
     * @param $timezone
     * @return false|int|string
     * @throws \Exception
     */
    public function date_convert_timestamp($date, $timezone)
    {
        //数字直接返回
        if (preg_match("/^\d*$/", $date))
        {
            return $date;
        }

        if(empty($timezone) || !is_string($timezone))
        {
            return strtotime($date);
        }

        //非法日期
        if (!strtotime($date))
        {
            return 0;
        }

        //时区不合法使用默认时区
        try
        {
            $timezone = new \DateTimeZone($timezone);
        }
        catch (\Exception $e)
        {
            $timezone = new \DateTimeZone(config('global.to_timezone'));
        }

        $date_obj = new \DateTime($date, $timezone);
        $time = $date_obj->format('U');

        return $time;
    }

    /**
     * 將HTML幾個特殊字元跳脫成HTML Entity(格式：&xxxx;)格式
     * 包括(&),('),("),(<),(>)五個字符
     * @param $data
     * @return array|string
     */
    public function htmlentities($data)
    {
        if (is_array($data))
        {
            foreach ($data as $k => $v)
            {
                $data[$k] = $this->htmlentities($data[$k]);
            }
        }
        else
        {
            //同时转义双,单引号
            $data = htmlspecialchars(trim($data), ENT_QUOTES);
        }

        return $data;
    }

    /**
     * 分等级,使用递归
     * @param array $data 待分类的数组
     * @param int $pid 上级id 指定从哪个节点开始找
     * @param int $level 缩进 0=一级 1=二级 2=三级
     * @param bool $clear
     * @return array
     */
    public function make_level(array $data, $pid = 0, $level = 0, $field_id = 'id', $field_pid = 'pid', $clear = true)
    {
        //static的作用仅在第一次调用函数时对变量进行初始化,并保留变量值
        static $arr = [];
        //首次进入清除上次调用函数留下的静态变量的值，进入深一层循环时则不要清除。
        if($clear == true) $arr = [];

        foreach($data as $v)
        {
            if(isset($v[$field_pid]) && $v[$field_pid] == $pid)
            {
                $v['level'] = $level;
                $arr[] = $v;
                $this->make_level($data, $v[$field_id], $level+1,
                    $field_id, $field_pid, false);
            }
        }
        return $arr;
    }

    /**
     * 根据父类ID获取所有子类,递归,返回不含自己,因自己不能為自己下級
     * @param array $data
     * @param int $pid 指定从哪个节点开始找
     * @param int $level 层级 该节点层级
     * @param string $field_id id字段名
     * @param string $field_pid 上级id字段名
     * @return array
     */
    public function get_all_child(array $data, $pid = 0, $level = 0, $field_id = 'id', $field_pid = 'pid')
    {
        $arr = [];
        //遍历
        foreach($data as $v)
        {
            if(isset($v[$field_pid]) && $v[$field_pid] == $pid)
            {
                $v['level'] = $level;
                $arr[] = $v;
                $arr = array_merge($arr, $this->get_all_child($data, $v[$field_id], $level + 1,
                    $field_id, $field_pid));
            }
        }
        return $arr;
    }

    /**
     * 根据子类ID获取所有父类,递归,返回包自己 給15 返回15->14->1
     * @param array $data
     * @param int $id 指定从哪个节点开始找
     * @param int $level
     * @param string $field_id id字段名
     * @param string $field_pid 上级id字段名
     * @param bool $clear
     * @return array
     */
    public function get_all_parent(array $data, $id = 0, $level = 0, $field_id = 'id', $field_pid = 'pid', $clear = true)
    {
        //static的作用仅在第一次调用函数时对变量进行初始化,并保留变量值
        static $arr = [];
        //首次进入清除上次调用函数留下的静态变量的值，进入深一层循环时则不要清除。
        if($clear == true) $arr = [];

        foreach($data as $v)
        {
            if(isset($v[$field_id]) && $v[$field_id] == $id)
            {
                $v['level'] = $level;
                $arr[] = $v;
                $this->get_all_parent($data, $v[$field_pid], $level-1,
                    $field_id, $field_pid, false);
            }
        }
        return $arr;
    }

    /**
     * 根据子类ID获取所有父类ID
     * @param array $data
     * @param $id 指定从哪个节点开始找
     * @return array
     */
    public function get_all_parent_ids(array $data, $id)
    {
        $rows = $this->get_all_parent($data, $id);
        $ids = array_column($rows, 'id');
        sort($ids);
        return $ids;
    }

    /**
     * 根据父类ID获取所有子类ID
     * @param array $data
     * @param $id 指定从哪个节点开始找
     * @return array
     */
    public function get_all_child_ids(array $data, $pid)
    {
        $rows = $this->get_all_child($data, $pid);
        $ids = array_column($rows, 'id');
        sort($ids);
        return $ids;
    }

    /**
     * 获取多语言字段名
     * @param $field 字段名
     * @param string $lang 指定语言 zh-tw/zh-cn/en/th
     * @return string
     */
    public function get_lang_field($field, $lang='')
    {
        $lang = !empty($lang) ? $lang : (!empty($GLOBALS['lang']) ? $GLOBALS['lang'] : config('app.locale'));
        $ret =  ($lang === config('app.locale')) ? $field : "{$field}_{$lang}";
        return $ret;
    }

    /**
     * 获取当前请求的方法名称
     * @return mixed
     */
    public function get_action()
    {
        return  request()->route()->getActionMethod();
    }

    /**
     * 获取当前请求的控制器名称
     *
     * @return mixed
     */
    public function get_controller()
    {
        //return class_basename(request()->route()->getController()); //只有名称ctl_faq
        return get_class(request()->route()->getController()); //含路径App\Http\Controllers\admin\ctl_faq
    }

    /**
     * curl表单POST请求提交
     * @param $parameter
     * @param $url
     * @return bool|string
     */
    public function curl_post($url, $parameter = [], $header = [])
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parameter);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $output = curl_exec($curl);
        $info = curl_getinfo($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl); //关闭资源

        return [
            'head' => $info, //获取curl连接资源信息
            'body' => $output,
            'info' => [
                'status' => $http_code
            ]
        ];
    }

    /**
     * curl get请求提交
     * @param $parameter
     * @param $url
     * @return bool|string
     */
    public function curl_get($url, $header = [])
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        $output = curl_exec($curl);
        $info = curl_getinfo($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl); //关闭资源

        return [
            'head' => $info,    //获取curl连接资源信息
            'body' => $output,
            'info' => [
                'status' => $http_code
            ]
        ];
    }

    /**
     * Guzzle get请求提交
     * @param $parameter
     * @param $url
     * @return bool|string
     */
    public function api_get($url, $parameter = [], $header = [])
    {
        $client = new \GuzzleHttp\Client();
        $options = [];
        $parameter and $options['query'] = $parameter;
        $header and $options['headers'] = $header;
        $res = $client->request('GET', $url, $options);

        $output = $res->getBody()->getContents();
        $http_code = $res->getStatusCode();

        return [
            'head' => [],
            'body' => $output,
            'info' => [
                'status' => $http_code
            ]
        ];
    }

    /**
     * Guzzle表单POST请求提交
     * @param $parameter
     * @param $url
     * @return bool|string
     */
    public function api_post($url, $parameter = [], $header = [])
    {
        $client = new \GuzzleHttp\Client();
        $options = [];
        $parameter and $options['form_params'] = $parameter;
        $header and $options['headers'] = $header;
        $res = $client->request('POST', $url, $options);

        $output = $res->getBody()->getContents();
        $http_code = $res->getStatusCode();

        return [
            'head' => [],
            'body' => $output,
            'info' => [
                'status' => $http_code
            ]
        ];
    }

    /**
     * 生成用户分享码
     * @param $user_id 用户id
     * @param $type 类型 4=邀请好友/5=分享行程/6=活动
     * @param string $sub_type 子类型 例如活动渠道
     * @return string
     */
    public function create_share_code($user_id, $type, $sub_type='')
    {
        if (!isset(self::$share_type_map[$type]))
        {
            return '';
        }

        $source_string = self::$sharecode_source_str;
        $num           = (int)($user_id.$sub_type.$type); //后面加上类型数字
        $code          = '';
        while ( $num > 0)
        {
            $mod  = $num % 35; //取馀数
            $num  = ($num - $mod) / 35;
            $code = $source_string[$mod].$code; //从35个字符中随机获取
        }

        if(empty($code[5])) //只有5个字符则补足到6个
        {
            $code = str_pad($code,6,'0',STR_PAD_LEFT); //全部6个字符
        }
        return $code;
    }

    /**
     * 获取图片验证码
     * @return mixed
     */
    public function get_captcha()
    {
        return app('captcha')->create('default', true);
    }

    /**
     * 获得国家代码 例 SG或KH
     * @param string $ip
     * @return string|null
     * @throws \GeoIp2\Exception\AddressNotFoundException
     * @throws \MaxMind\Db\Reader\InvalidDatabaseException
     */
    public function ip2country($ip = '')
    {
        if (empty($ip)) {
            $ip = request()->ip();
        }

        $country = '';
        try {
            if ($ip == '127.0.0.1') { //修正报错
                $country = '-';
            } else {
                $db_path = storage_path('GeoLite2-City.mmdb');
                $reader  = new Reader($db_path);
                $record  = $reader->city($ip);
                $country = strtoupper($record->country->isoCode); //返回大写国家代码
            }
        } catch (\Exception $e) {
            //记录日志
            logger(__METHOD__, [
                'ip'      => $ip,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $country;
    }

    /**
     * 导出列表數據
     * @param array $args
     */
    public function export_data(array $args, &$ret_data = [])
    {
        $status = 1;
        try
        {
            //要匯出數據
            $rows  = isset($args['rows']) ? $args['rows']:[];
            //匯出類型
            $format  = isset($args['format']) ? $args['format']:'csv';
            //列表字段
            $fields  = isset($args['fields']) ? $args['fields']:[];
            //数组不怕填多一些字段，有用到的会用来显示，没用到的填了也不会影响程序,所以尽管把多个tab列表页用到的字段都填进去吧
            $titles  = isset($args['titles']) ? $args['titles']:[];
            //指定导出文件路径
            $file  = isset($args['file']) ? $args['file']:'';
            //当前页数
            $page_no  = isset($args['page_no']) ? $args['page_no']:null;
            //总页数
            $total_page  = isset($args['total_page']) ? $args['total_page']:null;

            if(!is_array($fields) || empty(array_filter($fields))) //過濾空数组
            {
                $this->exception('未选择任何字段', -1);
            }

            $export_rows = [];
            foreach ($rows as $_item) //需要导出数据
            {
                $_new_item = [];
                foreach ($fields as $field) //匹配送的字段
                {
                    $field_arr = explode('.', $field);
                    if(count($field_arr) == 2)
                    {
                        $field_val = isset($_item[$field_arr[0]][$field_arr[1]]) ? strip_tags($_item[$field_arr[0]][$field_arr[1]]) : '-';
                    }
                    else
                    {
                        $field_val = isset($_item[$field_arr[0]]) ? strip_tags($_item[$field_arr[0]]) : '-';
                    }
                    $_new_item[] = $field_val;
                }
                $export_rows[] = $_new_item;
            }

            if ($page_no == 1)
            {
                //生成的文件名
                $excel_file = 'excel_'.$this->serv_display->datetime(time(), null, 'YmdHis').'.'.$format;
            }
            else
            {
                $excel_file = $file;
            }

            if (empty($excel_file))
            {
                $this->exception('参数错误[file为空]', -2);
            }

            $upload_dir = storage_path('app/public/excel');
            $file_path = $upload_dir."/".$excel_file;

            // 目录不存在则生成
            if (!$this->path_exists($upload_dir))
            {
                $this->exception('保存目录不存在', -3);
            }

            $fp = fopen($file_path, 'a+'); //a+讀寫模式開啟
            if ($fp === false)
            {
                $this->exception('导出失败，请稍后重试', -4);
            }

            if ($page_no == 1)
            {
                $export_titles = [];//导出文件字段
                foreach ($fields as $_field)
                {
                    //干掉字符串中的 HTML、XML、PHP标签
                    $export_titles[] = isset($titles[$_field]) ? strip_tags($titles[$_field]) : '-';
                }
                fwrite($fp, "\xEF\xBB\xBF"); //防止亂碼
                fputcsv($fp, $export_titles); //格式化為csv文件,先寫入欄目
            }

            foreach($export_rows as $v)
            {
                fputcsv($fp, $v); //寫入數據
            }
            fclose($fp); //最後記得關閉該文件

            $ret_data = [
                'file'       => $excel_file,
                'excel_file' => $this->get_server_file_url($excel_file, 'excel'),
                'total_page' => $total_page,
            ];
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }

    /**
     * 生成订单id, bigint支持数字大小范围刚好19位
     * @param int $num 后缀几个数字
     * @return string
     */
    public function make_order_id($num = 7)
    {
        $uniqid = $this->random('numeric', $num);
        return date("ymdHis").$uniqid;
    }

    /**
     * 生成优惠券编号, 默认15位
     * @param int $num 后缀几个数字
     * @return string
     */
    public function make_no($num = 7)
    {
        $uniqid = $this->random('numeric', $num);
        return date("Ymd").$uniqid;
    }

    /**
     * 生成10位数兑换码
     * @param int $num 几个数字
     * @return string
     */
    public function make_exchange_code($num = 10)
    {
        $exchange_code = $this->random('numeric', $num);
        return $exchange_code;
    }
}
