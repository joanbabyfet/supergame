<?php

namespace App\Http\Controllers\adminag;

use App\traits\trait_ctl_upload;

/**
 * 文件上传控制器
 * Class ctl_upload
 * @package App\Http\Controllers\adminag
 */
class ctl_upload extends Controller
{
    use trait_ctl_upload;

    public function __construct()
    {
        parent::__construct();
    }
}
