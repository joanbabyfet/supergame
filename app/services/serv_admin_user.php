<?php


namespace App\services;

use App\repositories\repo_admin_user;
use App\traits\trait_service_base;

/**
 * 处理管理员相关业务
 * Class serv_admin_user
 * @package App\services
 */
class serv_admin_user
{
    use trait_service_base;

    private $repo_admin_user;

    public function __construct(repo_admin_user $repo_admin_user)
    {
        $this->repo_admin_user     = $repo_admin_user;
    }
}
