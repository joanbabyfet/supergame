<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 发送邮件事件类
 * Class evt_send_mail
 * @package App\Events
 */
class evt_send_mail
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    //这里要定义公有变量,才能在监听器调用
    public $to           = []; //收件人
    public $subject      = ''; //主旨
    public $view         = ''; //模版 例 mail.example
    public $view_data    = []; //模版数据

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->to           = $data['to'] ?? [];
        $this->subject      = $data['subject'] ?? '';
        $this->view_data    = $data['view_data'] ?? [];
        $this->view         = $data['view'] ?? '';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        //return new PrivateChannel('channel-name');
        return [];
    }

    /**
     * 取得廣播事件名稱
     *
     * @return string
     */
//    public function broadcastAs()
//    {
//        return '';
//    }

    /**
     * 取得廣播資料。
     *
     * @return array
     */
//    public function broadcastWith()
//    {
//        return ['user' => $this->user->id];
//    }
}
