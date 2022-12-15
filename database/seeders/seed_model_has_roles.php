<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class seed_model_has_roles extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $fields = [
            'role_id',
            'model_type',
            'model_id',
        ];

        $rows = [
            [1, 'App\Models\mod_admin_user', '1'],
            [2, 'App\Models\mod_user', '09496c2d28f28ddabefb7ef2e278e95d'],
            [3, 'App\Models\mod_agent', '9eff3e40b42fa665b18437d2e91a7b3c'],
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
        DB::table('model_has_roles')->insert($insert_data);
    }
}
