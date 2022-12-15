<?php


namespace App\services;

use App\traits\trait_service_base;
use Backstage\ChangeConfigRequest;
use Backstage\ConfigServiceClient;
use Backstage\DeleteRoomRequest;
use Backstage\DeleteTableBackstageRequest;
use Backstage\DepositRequest;
use Backstage\MaintainConfig;
use Backstage\RoomRequest;
use Backstage\RoomServiceClient;
use Backstage\TableServiceClient;
use Backstage\User;
use Backstage\UserLoginLogRequest;
use Backstage\UserServiceClient;
use Backstage\WalletServiceClient;
use Backstage\WithdrawRequest;

/**
 * rpc客户端, 调用golang服务器端程序
 * Class serv_rpc_client
 * @package App\services
 */
class serv_rpc_client
{
    use trait_service_base;

    private $host;
    private $port;

    public function __construct()
    {
        $this->host = config('global.rpc.host');
        $this->port = config('global.rpc.port');
    }

    /**
     * 修改建桌与游戏基本配置时通知游戏服, 场景总后台
     * 1.客户端写入数据库与redis后, 通知服务端从redis获取最新数据
     * 2.验证值格式是否正确 例只能输入数据1000
     * 3.将数据送给game server
     * @param array $ret_data
     * @return int|mixed
     */
    public function change_config(&$ret_data = [])
    {
        $status = 1;
        try
        {
            $client = new ConfigServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new ChangeConfigRequest();

            [$response, $res_status] = $client->ChangeConfig($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getStatus();
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 干掉桌子时通知游戏服, 场景总后台与代理后台
     * 1.当status=1且该桌没有任何玩家时干掉该桌子
     * 2.当桌子有玩家参与, lobby server先变更status=2等待中, 然后保存在内存待牌局结束后干掉桌子与里面玩家
     * @param $id 桌子id
     * @param array $ret_data
     * @return int|mixed
     */
    public function delete_table(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
            'user_id'       => 'required', //管理者id
        ], $data);

        $status = 1;
        try
        {
            $client = new TableServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new DeleteTableBackstageRequest();
            $request->setId($data_filter['id']);
            $request->setUserId($data_filter['user_id']);

            [$response, $res_status] = $client->DeleteTable($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回已删除桌号
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 变更房间状态时通知游戏服, 场景总后台 (弃用)
     * 1.当禁用时, 通知服务端干掉该房redis (supergame_database_supergame_cache:lobby:room)
     * @param $id 房间id
     * @param array $ret_data
     * @return int|mixed
     */
    public function change_room_status($id, &$ret_data = [])
    {
        $status = 1;
        try
        {
            $client = new RoomServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new DeleteRoomRequest();
            $request->setId($id);

            [$response, $res_status] = $client->DeleteRoom($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回已删除房间号
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 创建房间后通知游戏服, 场景总后台
     * 1.客户端写入数据库不写入redis后, 通知服务端写入redis (supergame_database_supergame_cache:lobby:room)
     * @param array $data 房间数据
     * @return int|mixed
     */
    public function create_room(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
            'game_id'       => 'required',
            'name'          => 'required',
            'cover_img'     => 'required',
            'video_url'     => 'required',
            'desc'          => '',
            'sort'          => '',
            'status'        => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new RoomServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new RoomRequest();
            $request->setId($data_filter['id']);
            $request->setGameId($data_filter['game_id']);
            $request->setName($data_filter['name']);
            $request->setCoverImg($data_filter['cover_img']);
            $request->setVideoUrl($data_filter['video_url']);
            $request->setDesc($data_filter['desc']);
            $request->setSort($data_filter['sort']);
            $request->setStatus($data_filter['status']);

            [$response, $res_status] = $client->CreateRoom($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回创建房间号
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 修改房间后通知游戏服, 场景总后台
     * 1.客户端写入数据库不写入redis后, 通知服务端写入redis (supergame_database_supergame_cache:lobby:room)
     * @param array $data 房间数据
     * @return int|mixed
     */
    public function update_room(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
            'game_id'       => 'required',
            'name'          => 'required',
            'cover_img'     => 'required',
            'video_url'     => 'required',
            'desc'          => '',
            'sort'          => '',
            'status'        => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new RoomServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new RoomRequest();
            $request->setId($data_filter['id']);
            $request->setGameId($data_filter['game_id']);
            $request->setName($data_filter['name']);
            $request->setCoverImg($data_filter['cover_img']);
            $request->setVideoUrl($data_filter['video_url']);
            $request->setDesc($data_filter['desc']);
            $request->setSort($data_filter['sort']);
            $request->setStatus($data_filter['status']);

            [$response, $res_status] = $client->UpdateRoom($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回房间号
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 封禁玩家时通知游戏服, 场景总后台与代理后台
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function change_user_status(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'id'         => 'required', //玩家id
            'status'     => 'required', //玩家状态 DELETED = 0, DISABLED = 1, ENABLED = 2
        ], $data);

        $status = 1;
        try
        {
            $client = new UserServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new User();
            $request->setId($data_filter['id']);
            $request->setStatus($data_filter['status']);

            [$response, $res_status] = $client->ChangeStatus($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回已删除玩家id
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 封禁渠道代理时通知游戏服, 场景总后台
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function change_agent_status(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'id'         => 'required', //代理id
            'status'     => 'required', //代理状态 DELETED = 0, DISABLED = 1, ENABLED = 2
        ], $data);

        $status = 1;
        try
        {
            $client = new UserServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new User();
            $request->setId($data_filter['id']);
            $request->setStatus($data_filter['status']);

            [$response, $res_status] = $client->ChangeChannelStatus($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getId(); //返回已删除代理id
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 充值时通知游戏服, 场景总后台与sdk
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function deposit(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'holder_id'         => 'required',
            'name'              => 'required',
            'slug'              => 'required',
            'description'       => '',
            'balance'           => 'required',
            'transaction_id'    => '', //平台送订单号
            'currency'          => '',
            'origin'            => 'required', //来源
            'remark'            => '', //后台备注
            'agent_id'          => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new WalletServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new DepositRequest();
            $request->setHolderId($data_filter['holder_id']);
            $request->setName($data_filter['name']);
            $request->setSlug($data_filter['slug']);
            $request->setDescription($data_filter['description']);
            $request->setBalance($data_filter['balance']);
            $request->setTransactionId($data_filter['transaction_id']);
            $request->setOrigin($data_filter['origin']);
            $request->setRemark($data_filter['remark']);
            $request->setCurrency($data_filter['currency']);
            $request->setAgentId($data_filter['agent_id']);

            [$response, $res_status] = $client->Deposit($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = [
                'order_id'  => $response->getOrderId(), //返回订单号
                'balance'   => $response->getBalance(), //返回金额
            ];
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 提款时通知游戏服, 场景总后台与sdk
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function withdraw(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'holder_id'         => 'required',
            'balance'           => 'required',
            'transaction_id'    => '', //平台送订单号
            'currency'          => '',
            'origin'            => 'required', //来源
            'remark'            => '', //后台备注
            'agent_id'          => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new WalletServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new WithdrawRequest();
            $request->setHolderId($data_filter['holder_id']);
            $request->setBalance($data_filter['balance']);
            $request->setTransactionId($data_filter['transaction_id']);
            $request->setOrigin($data_filter['origin']);
            $request->setRemark($data_filter['remark']);
            $request->setCurrency($data_filter['currency']);
            $request->setAgentId($data_filter['agent_id']);

            [$response, $res_status] = $client->Withdraw($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = [
                'order_id' => $response->getOrderId(), //返回订单号
                'balance'  => $response->getBalance(), //返回金额
            ];
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 添加登錄日志, 场景sdk
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function create_login_log(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'uid'               => 'required',
            'username'          => 'required',
            'session_id'        => '',
            'agent'             => '',
            'login_time'        => '',
            'login_ip'          => '',
            'login_country'     => '',
            'exit_time'         => '',
            'extra_info'        => '',
            'status'            => '',
            'cli_hash'          => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new UserServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new UserLoginLogRequest();
            $request->setUid($data_filter['uid']);
            $request->setUsername($data_filter['username']);
            $request->setSessionId($data_filter['session_id']);
            $request->setAgent($data_filter['agent']);
            $request->setLoginTime($data_filter['login_time']);
            $request->setLoginIp($data_filter['login_ip']);
            $request->setLoginCountry($data_filter['login_country']);
            $request->setExitTime($data_filter['exit_time']);
            $request->setExtraInfo($data_filter['extra_info']);
            $request->setStatus($data_filter['status']);
            $request->setCliHash($data_filter['cli_hash']);

            [$response, $res_status] = $client->CreateLoginLog($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = [];
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
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
     * 变更维护配置, 场景总后台
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function change_maintain_config(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'title'     => 'required',
            'content'   => 'required',
            'mode'      => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $client = new ConfigServiceClient("{$this->host}:{$this->port}",[
                'credentials' =>  \Grpc\ChannelCredentials::createInsecure()
            ]);

            //请求体, 设置要送的参数值
            $request = new MaintainConfig();
            $request->setTitle($data_filter['title']);
            $request->setContent($data_filter['content']);
            $request->setMode($data_filter['mode']);

            [$response, $res_status] = $client->ChangeMaintainConfig($request)->wait(); //调用方法请求体过去并等待响应
            if ($res_status->code != \Grpc\STATUS_OK) { //0=成功
                $this->exception('error', -1);
            }
            $ret_data = $response->getStatus(); //返回状态
        }
        catch (\Exception $e)
        {
            $status = $e->getCode();
            //記錄日誌
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
            ]);
        }
        return $status;
    }
}
