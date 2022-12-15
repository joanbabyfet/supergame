<?php


namespace App\services;


use App\repositories\repo_sms_send_log;
use App\traits\trait_service_base;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class serv_send_mail
{
    use trait_service_base;

    private $repo_sms_send_log;

    public function __construct(repo_sms_send_log $repo_sms_send_log)
    {
        $this->repo_sms_send_log    = $repo_sms_send_log;
    }

    /**
     * 发送邮件
     * @param array $to 收件人数组
     * @param $subject 主旨
     * @param string $view 视图
     * @param array $view_data 视图数据
     */
    public function send_mail(array $to, $subject, $view = '', $view_data = [])
    {
        $to             = is_array(reset($to)) ? $to : [$to]; //兼容一维数组
        $view_data      = is_array(reset($view_data)) ? $view_data : [$view_data];
        $from = [ //寄送人
            'email' => config('mail.from.address'),
            'name'  => config('mail.from.name'),
        ];
        foreach($to as $k => $item)
        {
            $mail_data = empty($view_data) ? [] : $view_data[$k];
            //发送
            Mail::send($view, $mail_data, function($mail) use ($from, $item, $subject)
            {
                $mail->from($from['email'], $from['name']);
                $mail->to($item['email'], $item['name'])->subject($subject);
            });
            //避免太過頻繁的查詢
            usleep(100000); //让进程挂起一段时间,避免cpu跑到100% (单位微秒 1秒=1000000)
        }
    }
}
