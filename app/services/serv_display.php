<?php


namespace App\services;


use Illuminate\Support\Facades\Storage;

class serv_display
{
    /**
     * 時間戳轉日期時間
     *
     * @param $time
     * @param null $timezone
     * @param string $format
     * @return false|string|null
     */
    public function datetime($time, $timezone = null, $format = 'Y/m/d H:i')
    {
        if(empty($time))
        {
            return null;
        }

        $dis_time = date($format,$time);

        return $dis_time;
    }

    /**
     * 時間戳轉日期
     *
     * @param $time
     * @param null $timezone
     * @param string $format
     * @return false|string|null
     */
    public function date($time, $timezone = null, $format = 'Y/m/d')
    {
        if(empty($time))
        {
            return null;
        }

        $dis_time = date($format, $time);

        return $dis_time;
    }

    /**
     * 图片显示
     *
     * @param $img
     * @param string $dir
     * @return mixed
     */
    public function img($img, $dir = 'image')
    {
        if(empty($img))
        {
            return $img;
        }

        return Storage::disk('public')->url("{$dir}/{$img}");
    }

    /**
     * 金额, 第二位四捨五入 例 500.545 => 500.55 500.544 => 500.54
     * @param $money
     * @param string $separator
     * @return string
     */
    public function money($money, $separator=',')
    {
        return number_format($money, 2, '.', $separator);
    }

    /**
     * 將秒数转换为时分秒的格式
     * gmdate從php8.1棄用會返回空字符串, 改用date
     * @param Int $times 时间，单位 秒
     * @return String
     */
    public function second2time($seconds)
    {
        $seconds = (int)$seconds;
        if ( $seconds < 0 )
        {
            return 0;
        }
        // 大于一个小时
        if( $seconds>3600 )
        {
            $days_num = '';
            // 大于一天
            if( $seconds>24*3600 )
            {
                $days		= (int)($seconds/86400);
                $days_num	= $days."天";
                $seconds	= $seconds%86400;//取余
            }
            $hours = intval($seconds/3600);
            $minutes = $seconds%3600;//取余下秒数
            $time = $days_num.$hours."时".date('i分s秒', $minutes);
        }
        // 等于一个小时
        elseif( $seconds == 3600 )
        {
            $time = date('1时', $seconds);
        }
        // 小于一小时
        else
        {
            // 大于一分钟
            if( $seconds>60 )
            {
                $time = date('i分s秒', $seconds);
            }
            // 等于一分钟
            elseif( $seconds == 60 )
            {
                $time = date('1分', $seconds);
            }
            // 小于一分钟
            else
            {
                $time = date('s秒', $seconds);
            }
        }
        return $time;
    }
}
