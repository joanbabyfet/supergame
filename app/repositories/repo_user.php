<?php


namespace App\repositories;


use App\Models\mod_user;
use App\traits\trait_repo_base;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class repo_user
{
    use trait_repo_base;

    private $model;   //需要定义为私有变量
    public $page_size = 20; //每页展示几笔
    public $cache_key = 'auth_user:%s'; //用户信息
    public $token_key = 'token:%s'; //绑定token到uid
    public $wallet_cache_key = 'user_wallet:%s'; //用户钱包

    public function __construct(mod_user $mod_user)
    {
        $this->model = $mod_user;
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
        $agent_id   = !empty($conds['agent_id']) ? $conds['agent_id'] : '';
        $origin     = $conds['origin'] ?? null;
        $date_start   = !empty($conds['date_start']) ? $conds['date_start'] : ''; //注册开始时间
        $date_end   = !empty($conds['date_end']) ? $conds['date_end'] : ''; //注册结束时间
        $type     = $conds['type'] ?? null;
        $username   = !empty($conds['username']) ? $conds['username'] : '';
        $realname   = !empty($conds['realname']) ? $conds['realname'] : '';
        $id       = !empty($conds['id']) ? $conds['id']:[]; //用戶id

        $where = []; //筛选
        $where[] = ['delete_time', '=', 0]; //未删除
        $agent_id and $where[] = ['agent_id', '=', $agent_id];
        is_numeric($status) and $where[] = ['status', '=', $status];
        is_numeric($origin) and $where[] = ['origin', '=', $origin];
        $date_start and $where[] = ['create_time', '>=', (int)$date_start];
        $date_end and $where[] = ['create_time', '<=', (int)$date_end];
        ($type == 1) and $where[] = ['is_new_user', '=', 1]; //新增用户为第一次登录的用户
        $username and $where[] = ['username', 'like', "%{$username}%"];
        $realname and $where[] = ['realname', 'like', "%{$realname}%"];
        $id and $where[] = ['id', 'in', $id];

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
            //字母开头, 允许5-20字节, 允许字母数字下划线, 长度41字符
            'username'          => in_array($do, ['edit']) ? '' : 'required|regex:/^[a-zA-Z][a-zA-Z0-9_]{4,40}$/',
            //密码必须包含字母数字, 允许6-20字节, 长度41字符
            'password'          => $do == 'edit'  ? 'nullable|regex:/^(?=.*\d)(?=.*[a-zA-Z]).{6,20}$/' : 'required|regex:/^(?=.*\d)(?=.*[a-zA-Z]).{6,41}$/',
            'agent_id'          => 'required',
            'origin'            => $do == 'edit'  ? '' : 'required', //注册來源
            'realname'          => 'required', //不同玩家昵称可重复
            'role_id'           => '',
            'email'             => '',
            'phone_code'        => '',
            'phone'             => '',
            'country_id'        => '',
            'province_id'       => '',
            'city_id'           => '',
            'area_id'           => '',
            'address'           => '',
            'language'          => '',
            'currency'          => '',
            'session_expire'    => '', //登录时长, 在此时间内用户无操作会自动退出登录, 适用web场景
        ], $data);

        $status = 1;
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            $do         = $data_filter['do'];
            $id         = $data_filter['id'] ?? '';
            $username   = $data_filter['username'] ?? '';
            $password   = $data_filter['password'] ?? '';
            $role_id = empty($data_filter['role_id']) ? '' : $data_filter['role_id']; //角色
            unset($data_filter['do'], $data_filter['id'], $data_filter['role_id'],
                $data_filter['username'], $data_filter['password']);

            if($do == 'add')
            {
                $exists = $this->find(['where' => [['username', '=', $username]]]);
                if($exists)
                {
                    $this->exception('该账号已经存在', -2);
                }

                $data_filter['id'] = $id = random('web');
                $data_filter['username'] = strtolower($username);  //帐号转小写
                $data_filter['password'] = hash_password($password);
                $data_filter['create_time'] = time();
                $data_filter['create_user'] = defined('AUTH_UID') ? AUTH_UID : '';
                $data_filter['reg_ip'] = request()->ip();
                $data_filter['language'] = config('app.locale');
                $this->insert($data_filter);
                $ret_data['id'] = $id;
            }
            elseif($do == 'edit')
            {
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
            'ban_desc'      => $data['status'] == mod_user::DISABLE ? 'required' : '',
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

        $uid        = empty($uid) ? AUTH_UID : $uid;
        $token_key  = sprintf($this->token_key, $uid);
        Redis::setex($token_key, $expire_time, $token);
    }

    /**
     * 解绑某个token
     * @param string $uid
     */
    public function unbind_token_uid($uid = '')
    {
        $uid        = empty($uid) ? AUTH_UID : $uid;
        $token_key  = sprintf($this->token_key, $uid);
        Redis::del($token_key);
    }

    /**
     * 通过uid获取token
     * @param $uid
     * @return mixed|string
     */
    public function get_token($uid)
    {
        $key    = sprintf($this->token_key, $uid);
        $token  = Redis::get($key);
        return empty($token) ? '' : $token;
    }

    /**
     * 根据用户帐号获取用户信息
     * @param $username
     * @return array|mixed
     */
    public function get_user_by_username($username)
    {
        $ret = [];
        if (empty($username)) return $ret;

        $row = $this->find(['where' => [
            ['username', '=', $username],
            ['delete_time', '=', 0] //未刪除
        ]]);
        $ret = empty($row) ? []:$row->toArray();
        return $ret;
    }

    /**
     * 获取代理帐号+玩家帐号
     * @param string $prefix
     * @param $username
     * @return string
     */
    public function get_prefix_account($prefix = '', $username)
    {
        $ret = '';
        if (empty($username)) return $ret;

        $username = empty($prefix) ? $username : $prefix.'_'.$username;
        return $username;
    }
}
