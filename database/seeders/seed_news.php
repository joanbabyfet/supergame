<?php

namespace Database\Seeders;

use App\Models\mod_news;
use Illuminate\Database\Seeder;

class seed_news extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //使用模型工厂文件生成测试数据
        mod_news::factory()->count(10)->create();
    }
}
