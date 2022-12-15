<?php

namespace App\Console\Commands;

use GatewayWorker\BusinessWorker;
use GatewayWorker\Gateway;
use GatewayWorker\Register;
use Illuminate\Console\Command;
use Workerman\Worker;

class cron_workerman extends Command
{
    /**
     * The name and signature of the console command.
     * action=参数 --d=选项
     * @var string
     */
    protected $signature = 'workerman {action} {--d}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'workerman';

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
        $argv[0] = 'wk';
        $argv[1] = $action;
        $argv[2] = $this->option('d') ? '-d':''; //获取到选项返回true, 否则false
        $this->start();
    }

    private function start()
    {
        $this->start_gateway('admin');
        $this->start_gateway('agent');
        $this->start_businessworker();
        $this->start_register();
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

    private function start_businessworker()
    {
        $worker                     = new BusinessWorker();
        $worker->name               = config('global.socket.name');
        $worker->count              = config('global.socket.process_count');
        $worker->registerAddress    = config('global.socket.register_address');
        $worker->eventHandler       = \App\Events\evt_workerman::class;
    }

    private function start_register()
    {
        new Register('text://0.0.0.0:1236'); //实例化一个容器, register 必须是text协议
    }
}
