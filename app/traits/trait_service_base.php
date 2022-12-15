<?php


namespace App\traits;


use App\lib\response;

trait trait_service_base
{
    public static $msg_maps = [];

    /**
     * 获取异常信息
     *
     * @param $status
     * @return mixed|string
     */
    public function get_err_msg($status)
    {
        return isset(static::$msg_maps[$status]) ? static::$msg_maps[$status] : 'Unknown error!';
    }

    /**
     * 统一异常处理
     *
     * @param \Exception $e
     * @return int|mixed
     */
    public function get_exception_status(\Exception $e)
    {
        $err_code                = $e->getCode();
        $status                  = $err_code >= 0 ? response::UNKNOWN_ERROR_STATUS : $err_code;
        self::$msg_maps[$status] = $e->getMessage();

        return $status;
    }

    /**
     * 抛异常封装
     *
     * @param string $msg
     * @param null $code
     * @throws \Exception
     */
    public function exception($msg = '', $code = null)
    {
        $code = $code ? $code : response::UNKNOWN_ERROR_STATUS;
        throw new \Exception($msg, $code);
    }
}
