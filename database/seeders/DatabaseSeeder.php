<?php

namespace Database\Seeders;

use App\Models\mod_example;
use App\Models\mod_news;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 使用数据填充文件生成基础数据
        $this->call([
            seed_roles::class,
            seed_model_has_roles::class,
            seed_admin_user::class,
            seed_module::class,
            seed_permissions::class,
            seed_role_has_permissions::class,
            seed_menu::class,
            seed_currency_type::class,
            seed_country::class,
            seed_area::class,
            seed_config::class,
            seed_game::class,
            seed_agent::class,
            seed_app_key::class,
            seed_user::class,
            seed_example::class, //测试数据
            //seed_news::class, //测试数据
            //seed_order_transfer::class, //测试数据
        ]);
    }
}
