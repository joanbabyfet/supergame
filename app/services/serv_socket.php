<?php


namespace App\services;


use App\traits\trait_service_base;
use GatewayWorker\Lib\Gateway;

class serv_socket
{
    use trait_service_base;

    public function send(array $params)
    {
        //参数过滤
        $data_filter = data_filter([
            'type'          => 'required',
            'uid'           => '', //用户id, 选填
            'client_id'     => 'required', //客户端id
            'action'        => 'required', //类型
            'code'          => '',
            'msg'           => '',
            'data'          => '',
        ], $params);

        $client_id = $data_filter['client_id'] ?? '';
        $action    = $data_filter['action'] ?? '';
        $uid       = $data_filter['uid'] ?? '';
        $_data = is_array($data_filter['data']) ? $data_filter['data'] :
            json_decode($data_filter['data'], true); //兼容，数据若不是数组json则转数组
        $data = [
            'action'    => $action,
            'code'      => $data_filter['code'],
            'msg'       => htmlspecialchars_decode($data_filter['msg'], ENT_QUOTES), //雙引號與單引號都要轉換回來
            'timestamp' => time(),
            'data'      => $_data
        ];

        $status = 1;
        try
        {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            Gateway::sendToClient($client_id, $data); //向当前client_id发送数据
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'client_id'     => $client_id,
                'action'        => $action,
                'errcode'       => $e->getCode(),
                'errmsg'        => $e->getMessage(),
                'timestamp'     => time(),
                'uid'           => $uid,
                'data'          => $data,
            ]);
        }
        return $status;
    }
}
