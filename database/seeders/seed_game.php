<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_game extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'code',
            'name',
            'type',
            'cover_img',
            'sort',
            'status',
            'create_time',
            'create_user',
        ];

        $rows = [
            ['PG', 'pj', 'LC', '', 0, 1, time(), '1'],
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
        DB::table('game')->insert($insert_data); //走批量插入
    }
}
