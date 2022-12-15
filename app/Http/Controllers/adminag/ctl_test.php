<?php

namespace App\Http\Controllers\adminag;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 测试用控制器
 * Class ctl_test
 * @package App\Http\Controllers\adminag
 */
class ctl_test extends Controller
{
    public function index(Request $request)
    {
        $ip = '34.124.199.205';
        return res_success(['country' => ip2country($ip)]);
    }
}
