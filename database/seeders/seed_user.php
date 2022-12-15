<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_user extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'id',
            'agent_id',
            'origin',
            'username',
            'password',
            'realname',
            'email',
            'phone_code',
            'phone',
            'status',
            'session_expire',
            'session_id',
            'reg_ip',
            'language',
            'currency',
            'create_time',
        ];

        //初始化系统公庄
        $rows = [
            //['09496c2d28f28ddabefb7ef2e278e95d', '9eff3e40b42fa665b18437d2e91a7b3c', 1, 'agent1_chris', bcrypt('agent1_chris'), '克里斯', 'chris@gmail.com', '', '', 1, 1440, '', '127.0.0.1', config('app.locale'), config('global.currency'), time()],
            [config('global.sys_gz_uid'), '9eff3e40b42fa665b18437d2e91a7b3c', 1, 'agent1_sysgz', bcrypt('Q9W57Bgb3B'), '系统公庄', '', '', '', 1, 1440, '', '127.0.0.1', config('app.locale'), config('global.currency'), time()],
        ];

        $insert_data = [];
        foreach ($rows as $row)
        {
            $item = [];
            foreach ($fields as $k => $field)
            {
                $item[$field] = $row[$k];
            }
            $insert_data[] = $item;
        }
        DB::table('users')->insert($insert_data); //走批量插入
    }
}
