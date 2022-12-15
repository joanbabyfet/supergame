<?php

namespace App\Console\Commands;

use GatewayWorker\Gateway;
use Illuminate\Console\Command;
use Workerman\Worker;

class cron_start_agent_gateway extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wk:agent_gateway {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        global $argv;
        $action = $this->argument('action'); //获取参数
        if(!in_array($action, ['start', 'stop', 'restart', 'reload', 'status', 'connections']))
        {
            $this->error('参数错误');
            exit;
        }
        //$argv[0] = 'wk'; //干掉才能正常啟用
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d':''; //获取到选项返回true, 否则false
        $this->start_gateway('agent');
        Worker::runAll(); //运行worker
    }

    private function start_gateway($type)
    {
        $config = config('global.socket.hosts');
        $type = array_key_exists($type, $config) ? $type : 'admin';

        //实例化一个容器, 客户端定时发送心跳(推荐)
        $gateway                        = new Gateway($config[$type]['listen']); //gateway进程
        $gateway->name                  = $config[$type]['name']; //gateway名称，status方便查看
        $gateway->count                 = config('global.socket.process_count'); //gateway进程数
        $gateway->lanIp                 = config('global.socket.lan_ip'); //本机ip，分布式部署时使用内网ip
        $gateway->startPort             = $config[$type]['start_port']; // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000, 一般会使用4001 4002 4003 4004 4个端口作为内部通讯端口
        $gateway->pingInterval          = config('global.socket.ping_interval'); //几秒送1次心跳包, 0=不送
        $gateway->pingNotResponseLimit  = config('global.socket.ping_not_response_limit'); //送心跳包后,客户端不回应N次后即断开连接
        $gateway->pingData              = config('global.socket.ping_data'); //向客户端送心跳数据
        $gateway->registerAddress       = config('global.socket.register_address'); //服务注册地址
    }
}
