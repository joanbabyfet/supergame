<?php

namespace App\Http\Controllers\adminag;

use App\repositories\repo_admin_user;
use App\repositories\repo_agent;
use App\repositories\repo_module;
use App\repositories\repo_role;
use App\repositories\repo_room;
use App\repositories\repo_user;
use App\services\serv_array;
use App\traits\trait_ctl_common;

/**
 * 公共接口控制器
 * Class ctl_common
 * @package App\Http\Controllers\adminag
 */
class ctl_common extends Controller
{
    use trait_ctl_common;

    private $repo_role;
    private $repo_room;
    private $repo_agent;
    private $repo_module;
    private $repo_admin_user;
    private $repo_user;
    private $serv_array;

    public function __construct(
        repo_role $repo_role,
        repo_room $repo_room,
        repo_agent $repo_agent,
        repo_module $repo_module,
        repo_admin_user $repo_admin_user,
        repo_user $repo_user,
        serv_array $serv_array
    )
    {
        parent::__construct();
        $this->repo_role          = $repo_role;
        $this->repo_room          = $repo_room;
        $this->repo_agent         = $repo_agent;
        $this->repo_module         = $repo_module;
        $this->repo_admin_user    = $repo_admin_user;
        $this->repo_user          = $repo_user;
        $this->serv_array         = $serv_array;
    }

    /**
     * 获取桌主选项
     * @version 1.0.0
     * @return mixed
     */
    public function get_user_options()
    {
        $rows = $this->repo_user->get_list([
            'agent_id'      => $this->pid
        ]);
        $options = $this->serv_array->one_array($rows, ['id', 'realname']);
        return res_success($options);
    }
}
