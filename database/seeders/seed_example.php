<?php

namespace Database\Seeders;

use App\Models\mod_example;
use Illuminate\Database\Seeder;

class seed_example extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //使用模型工厂文件生成测试数据
        mod_example::factory()->count(100)->create();
    }
}
