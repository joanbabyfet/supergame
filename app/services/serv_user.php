<?php


namespace App\services;


use App\lib\response;
use App\Models\mod_user;
use App\repositories\repo_user;
use App\repositories\repo_user_login_log;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\DB;

/**
 * 处理会员相关业务
 * Class serv_admin_user
 * @package App\services
 */
class serv_user
{
    use trait_service_base;

    private $repo_user;
    private $repo_user_login_log;

    public function __construct(
        repo_user $repo_user,
        repo_user_login_log $repo_user_login_log
    )
    {
        $this->repo_user            = $repo_user;
        $this->repo_user_login_log  = $repo_user_login_log;
    }

    /**
     * 检测该用户名是否已注册
     * @param array $data
     * @param array $ret_data
     * @return int|mixed
     */
    public function is_registered(array $data, &$ret_data = [])
    {
        //参数过滤
        $data_filter = data_filter([
            'account'                => 'required',
        ], $data);

        $status = 1; //该账号尚未注册
        try
        {
            if(!is_array($data_filter))
            {
                $this->exception(trans('api.api_param_error'), -1);
            }

            //检测帐号是否存在
            $user = $this->repo_user->find([
                'fields'    => ['id', 'status'],
                'where'     => [['username', '=', $data_filter['account']]]
            ]);
            $ret_data = empty($user) ? false : true; //true=已注册 false=尚未注册
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
     * 检测该用户是否为新增用户
     * @param $uid
     * @return int
     */
    public function is_new_user($uid)
    {
        $ret = 0;

        $where = []; //筛选
        $where[] = ['uid', '=', $uid];
        $count = $this->repo_user_login_log->get_field_value([
            'fields'    => [
                DB::raw("COUNT(DISTINCT FROM_UNIXTIME(login_time, '%Y/%m/%d')) AS count"),
            ],
            'where'     => $where
        ]);
        if($count === 1) //去重后登录日志只有1条一定是新增用户
        {
            $ret = 1;
        }
        return $ret;
    }
}
