<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_role_has_permissions extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'permission_id',
            'role_id',
        ];

        $rows = [
            [52, 3], //代理商
            [53, 3],
            [54, 3],
            [55, 3],
            [56, 3],
            [57, 3],
            [58, 3],
            [59, 3],
            [60, 3],
            [61, 3],
            [62, 3],
            [63, 3],
            [64, 3],
            [65, 3],
            [66, 3],
            [67, 3],
            [68, 3],
            [73, 3],
            [74, 3],
            [75, 3],
            [76, 3],
            [77, 3],
            [78, 3],
            [79, 3],
            [52, 4], //子帐号
            [53, 4],
            [54, 4],
            [55, 4],
            [56, 4],
            [57, 4],
            [58, 4],
            [59, 4],
            [60, 4],
            [61, 4],
            [62, 4],
            [63, 4],
            [64, 4],
            [65, 4],
            [66, 4],
            [67, 4],
            [68, 4],
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
        DB::table('role_has_permissions')->insert($insert_data);
    }
}
