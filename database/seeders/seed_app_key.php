<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_app_key extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_id = date("ymdHis").random('numeric', 7);
        $app_key = random('web');

        $fields = [
            'app_id',
            'app_key',
            'agent_id',
            'desc',
        ];

        $rows = [
            [$app_id, $app_key, '9eff3e40b42fa665b18437d2e91a7b3c', ''],
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
        DB::table('app_key')->insert($insert_data);
    }
}
