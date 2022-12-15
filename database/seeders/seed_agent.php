<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_agent extends Seeder
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
            'pid',
            'username',
            'password',
            'realname',
            'desc',
            'agent_balance',
            'email',
            'phone_code',
            'phone',
            'status',
            'safe_ips',
            'session_expire',
            'session_id',
            'reg_ip',
            'create_user',
            'create_time',
        ];

        $rows = [
            ['9eff3e40b42fa665b18437d2e91a7b3c', '0', 'agent1', bcrypt('abc123#'), 'super666', '', 0.00, 'agent@gmail.com', '', '', 1, '', 1440, '', '127.0.0.1', '1', time()],
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
        DB::table('agents')->insert($insert_data); //走批量插入
    }
}
