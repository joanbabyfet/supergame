<?php


namespace App\lib;

/**
 * 响应码, 全局定义
 * 错误码 code=0 成功, code<0 错误
 * Class response
 * @package App\lib
 */
class response
{
    const SUCCESS = 0; //成功
    const FAIL = -1;   //失败
    const ERROR = -1;  //错误

    const UNKNOWN_ERROR_STATUS = -1211; //未知错误,一般都是数据库死锁
    const DEAD_LOCK_STATUS = -1213; //死锁全局返回状态

    const IN_MAINTENANCE = -10000; //系统维护中
    const NOT_IN_SAFE_IP = -10100; //IP不在白名单内,无法操作

    const NO_TOKEN = -4001; //缺少token
    const TOKEN_AUTH_FAIL = -4002; //无此用户, 未登录或登录超时
    const TOKEN_EXPIRED = -4003; //存取token 过期
    const REFRESH_TOKEN_EXPIRED = -4004; //刷新token 过期
    const TOKEN_INVALID = -4005; //token 无效
    const ORIGIN_IP_INVALID = -4006; //來源ip无效
    const ORIGIN_USER_AGENT_INVALID = -4007; //来源客户端识别无效
    const ORIGIN_HOSTNAME_INVALID = -4008; //来源本地主机名无效
    const RESTRICT_ACCESS_SITE = -4009; //限制访问网站

    const NO_PERMISSION = -4101; // 无权限
    const NO_ROLE = -4102; // 色角不足, 没权限执行本操作
}
