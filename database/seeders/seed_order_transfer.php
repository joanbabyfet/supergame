<?php

namespace Database\Seeders;

use App\Models\mod_order_transfer;
use Illuminate\Database\Seeder;

class seed_order_transfer extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //使用模型工厂文件生成测试数据
        mod_order_transfer::factory()->count(10)->create();
    }
}
