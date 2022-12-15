<?php


namespace App\repositories;


use App\Models\mod_agent;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class repo_agent
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔
    public $cache_key = 'auth_agent:%s'; //代理信息
    public $token_key = 'auth_agent_token:%s'; //token -> uid 映射
    public $uid_key   = 'auth_agent_uid:%s'; //uid -> token 映射
    public $wallet_cache_key = 'agent_wallet:%s'; //代理钱包

    public function __construct(mod_agent $mod_agent)
    {
        $this->model = $mod_agent;
    }

    /**
     * 获取列表
     * @param array $conds
     * @return array
     */
    public function get_list(array $conds)
    {
        $page_size  = !empty($conds['page_size']) ? $conds['page_size'] : $this->page_size;
        $order_by   = !empty($conds['order_by']) ? $conds['order_by'] : ['create_time', 'desc']; //默认添加时间正序
        $group_by   = !empty($conds['group_by']) ? $conds['group_by'] : []; //分组
        $status     = $conds['status'] ?? null;
        $realname   = !empty($conds['realname']) ? $conds['realname'] : '';
        $username   = !empty($conds['username']) ? $conds['username'] : '';
        $pid     = $conds['pid'] ?? null;
        $keyword    = !empty($conds['keyword']) ? $conds['keyword']:''; //关键词

        $where = []; //筛选
        $where[] = ['delete_time', '=', 0]; //未删除
        $realname and $where[] = ['realname', 'like', "%{$realname}%"];
        $username and $where[] = ['username', 'like', "%{$username}%"];
        $keyword and $where[] = [DB::raw('CONCAT(username, realname)'), 'like', "%{$keyword}%"];

        is_numeric($status) and $where[] = ['status', '=', $status];
        ($pid || $pid === '0') and $where[] = ['pid', '=', $pid];

        $rows = $this->lists([
            'fields'    => $conds['fields'] ?? null,
            'where'     => $where,
            'page'      => $conds['page'] ?? null,
            'page_size' => $page_size,
            'order_by'  => $order_by,
            'group_by'  => $group_by,
            'count'     => $conds['count'] ?? null, //是否显示总条数
            'limit'     => $conds['limit'] ?? null,
            'field'     => $conds['field'] ?? null,
            'append'    => $conds['append'] ?? null, //展示扩充字段(默认展示) []=不展示
            'lock'      => $conds['lock'] ?? null, //排他鎖
            'share'     => $conds['share'] ?? null, //共享鎖
            'load'      => $conds['load'] ?? null, //加载外表
            'index'     => $conds['index'] ?? null,
        ])->toArray();
        return $rows;
    }

    /**
     * 添加或修改
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function save(array $data, &$ret_data = [])
    {
        $do             = isset($data['do']) ? $data['do'] : '';
        //参数过滤
        $data_filter = data_filter([
            'do'                => 'required',
            'id'                => $do == 'edit' ? 'required' : '',
            'pid'               => '',
            //字母开头，允许5-20字节，允许字母数字下划线
            'username'          => in_array($do, ['edit']) ? '' : "required|regex:/^[a-zA-Z][a-zA-Z0-9_]{4,19}$/",
            //密码必须包含字母，数字，允许6-20字节
            'password'          => $do == 'edit'  ? 'nullable|regex:/^(?=.*\d)(?=.*[a-zA-Z]).{6,20}$/' : 'required|regex:/^(?=.*\d)(?=.*[a-zA-Z]).{6,20}$/',
            'realname'          => 'required',
            'desc'              => '',
            'currency'          => '', //币种
            'wallet_type'       => '', //钱包类型 1=转帐钱包 2=单一钱包
            'status'            => '',
            'role_id'           => '',
            'agent_balance'     => '',
            'safe_ips'          => '', //ip白名单
            'session_expire'    => '', //登录时长, 在此时间内用户无操作会自动退出登录, 适用web场景
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $do             = $data_filter['do'];
            $id             = $data_filter['id'] ?? '';
            $pid            = $data_filter['pid'] ?? '';
            $username       = $data_filter['username'] ?? '';
            $password       = $data_filter['password'] ?? '';
            $agent_balance  = $data_filter['agent_balance'] ?? 0; //代理额度
            $role_id = empty($data_filter['role_id']) ? '' : $data_filter['role_id']; //角色
            unset($data_filter['do'], $data_filter['id'], $data_filter['role_id'],
                $data_filter['username'], $data_filter['password']);

            if($do == 'add')
            {
                $exists = $this->find(['where' => [['realname', '=', $data_filter['realname']]]]);
                if($exists)
                {
                    $this->exception('该名称已经存在', -2);
                }

                $exists = $this->find(['where' => [['username', '=', $username]]]);
                if($exists)
                {
                    $this->exception('该账号已经存在', -3);
                }

                $data_filter['id'] = $id = random('web');
                $data_filter['username'] = strtolower($username);  //帐号转小写
                $data_filter['password'] = hash_password($password);
                $data_filter['remain_balance'] = $agent_balance; //剩馀额度
                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $data_filter['reg_ip'] = request()->ip();
                $this->insert($data_filter);
                $ret_data['id'] = $id;
            }
            elseif($do == 'edit')
            {
                $exists = $this->find(['where' => [
                    ['realname', '=', $data_filter['realname']],
                    ['id', '!=', $id]
                ]]);
                if($exists)
                {
                    $this->exception('该名称已经存在', -2);
                }

                //获取某代理提款金额
                $withdraw_amount = app(repo_order_transfer::class)->get_agent_withdraw_amount($id);
                $remain_balance = ($agent_balance > $withdraw_amount) ? $agent_balance - $withdraw_amount : 0;
                $data_filter['remain_balance'] = $remain_balance; //每次编辑时更新该字段

                if($password != '')
                {
                    $data_filter['password'] = hash_password($password);
                }

                $data_filter['update_time'] = time();
                $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $this->update($data_filter, ['id' => $id]);
            }

            //同步该用户的用户组
            $user = $this->find(['where' => [['id', '=', $id]]]);
            $user and $user->syncRoles($role_id);

            //初始化钱包
            if(!empty($user))
            {
                $balance = money($user->balance, ''); //有小數
                $cache_key = sprintf($this->wallet_cache_key, $id);
                Redis::set($cache_key, $balance); //写入redis
            }
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }

    /**
     * 软删除
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function del(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? [];
            unset($data_filter['id']);

            $data_filter['delete_time'] = time();
            $data_filter['delete_user'] = defined('AUTH_UID') ? AUTH_UID : '';
            $this->update($data_filter, [['id', $id]]);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }

    /**
     * 修改用户自己密码
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function edit_pwd(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'                => 'required',
            'old_password'      => 'required',
            'password'          => 'required',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id         = $data_filter['id'] ?? '';
            $old_password   = $data_filter['old_password'] ?? '';
            $password   = $data_filter['password'] ?? '';
            unset($data_filter['id'], $data_filter['old_password']);

            //检测原密码, auth()->user()->password为数据库保存的密文
            if(!check_password($old_password, auth()->user()->password))
            {
                $this->exception(trans('api.api_old_password_error'), -1);
            }

            $data_filter['password'] = hash_password($password);
            $data_filter['update_time'] = time();
            $data_filter['update_user'] = defined('AUTH_UID') ? AUTH_UID : '';
            $this->update($data_filter, ['id' => $id]);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }

    /**
     * 啟用或禁用
     * @param array $data
     * @return int|mixed
     * @throws \Throwable
     */
    public function change_status(array $data)
    {
        //参数过滤
        $data_filter = data_filter([
            'id'            => 'required',
            'status'        => '',
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter) || !is_numeric($data_filter['status']))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $id = $data_filter['id'] ?? '';
            unset($data_filter['id']);

            $data_filter['update_time'] = time();
            $this->update($data_filter, ['id' => $id]);
        }
        catch (\Exception $e)
        {
            $status = $this->get_exception_status($e);
            //记录日志
            logger(__METHOD__, [
                'status'  => $status,
                'errcode' => $e->getCode(),
                'errmsg'  => $e->getMessage(),
                'data'    => $data,
            ]);
        }
        return $status;
    }

    /**
     * 设置用户缓存
     * @param $user
     * @param string $uid
     * @param float|int $expire_time 缓存时间，单位秒 null=默認2小时
     */
    public function set_cache($user, $uid = '', $expire_time = null)
    {
        if ($expire_time === null) {
            $expire_time = config('global.cache_time');
        }

        $uid = empty($uid) ? AUTH_UID : $uid;
        $cache_key = sprintf($this->cache_key, $uid);
        Redis::setex($cache_key, $expire_time, json_encode($user, JSON_UNESCAPED_UNICODE)); //保存用户信息
    }

    /**
     * 删除用户缓存
     * @param string $uid
     */
    public function del_cache($uid = '')
    {
        $uid = empty($uid) ? AUTH_UID : $uid;
        $cache_key = sprintf($this->cache_key, $uid);
        Redis::del($cache_key);
    }

    /**
     * 根据代理主帐号获取代理信息
     * @param $username
     * @return array|mixed
     */
    public function get_agent_by_username($username)
    {
        $ret = [];
        if (empty($username)) return $ret;

        $row = $this->find(['where' => [
            ['username', '=', $username],
            ['pid', '=', '0']
        ]]);
        $ret = empty($row) ? []:$row->toArray();
        return $ret;
    }

    /**
     * 绑定token到uid
     * @param $token
     * @param string $uid
     * @param int $expire_time 缓存时间，单位秒 null=默認2小时
     */
    public function bind_token_uid($token, $uid = '', $expire_time = null)
    {
        if ($expire_time === null) {
            $expire_time = config('global.cache_time');
        }

        $uid = empty($uid) ? AUTH_UID : $uid;
        $token_key  = sprintf($this->token_key, $token);
        $uid_key    = sprintf($this->uid_key, $uid);
        //删除老token
        $old_token = Redis::get($uid_key);
        if (!empty($old_token))
        {
            $old_token_key = sprintf($this->token_key, $old_token);
            Redis::del($old_token_key);
        }
        //设置新token
        Redis::setex($token_key, $expire_time, $uid);
        //设置uid与token的映射
        Redis::setex($uid_key, $expire_time, $token);
    }

    /**
     * 解绑某个token
     * @param $token
     * @param string $uid
     */
    public function unbind_token_uid($token, $uid = '')
    {
        $uid        = empty($uid) ? AUTH_UID : $uid;
        $token_key  = sprintf($this->token_key, $token);
        $uid_key    = sprintf($this->uid_key, $uid);
        Redis::del($token_key);
        Redis::del($uid_key);
    }

    /**
     * 通过uid获取绑定在uid上的token
     * @param string $uid
     * @return string
     */
    public function get_token_by_uid($uid = '')
    {
        $uid    = empty($uid) ? AUTH_UID : $uid;
        $key    = sprintf($this->uid_key, $uid);
        $token  = Redis::get($key);
        return empty($token) ? '' : $token;
    }

    /**
     * 通过token获取uid
     * @param string $token
     * @return string
     */
    public function get_uid_by_token($token = '')
    {
        $key    = sprintf($this->token_key, $token);
        $uid    = Redis::get($key);
        return empty($uid) ? '' : $uid;
    }
}
