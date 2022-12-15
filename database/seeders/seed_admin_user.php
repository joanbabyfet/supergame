<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_admin_user extends Seeder
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
            'username',
            'password',
            'realname',
            'email',
            'phone_code',
            'phone',
            'status',
            'safe_ips',
            'session_expire',
            'session_id',
            'reg_ip',
            'create_time',
        ];

        $rows = [
            ['1', 'admin', bcrypt('Bb123456'), '管理員', 'admin@gmail.com', '', '', 1, '', 1440, '', '127.0.0.1', time()],
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
        DB::table('admin_users')->insert($insert_data); //走批量插入
    }
}
