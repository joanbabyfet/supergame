<?php

namespace Database\Seeders;

use App\Models\mod_member_online_per_hour;
use Illuminate\Database\Seeder;

class seed_member_online_per_hour extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //使用模型工厂文件生成测试数据
        mod_member_online_per_hour::factory()->count(1000)->create();
    }
}
