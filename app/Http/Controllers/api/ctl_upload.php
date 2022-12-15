<?php

namespace App\Http\Controllers\api;

use App\services\serv_upload;
use App\traits\trait_ctl_upload;
use Illuminate\Http\Request;

/**
 * 文件上传控制器
 * Class ctl_upload
 * @package App\Http\Controllers\api
 */
class ctl_upload extends Controller
{
    use trait_ctl_upload;

    public function __construct(serv_upload $serv_upload)
    {
        parent::__construct();
        $serv_upload::$dir_num = 0; //不使用分隔目录
    }
}
