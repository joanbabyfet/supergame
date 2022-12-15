<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu', function (Blueprint $table) {
            $table->Increments('id')->comment("id");
            $table->integer('parent_id')->default(0)->nullable()->comment("上级id");
            $table->tinyInteger("level")->default(0)->nullable()->comment('等级');
            $table->string('name',50)->default('')->nullable()->comment('菜单名称');
            $table->tinyInteger('type')->default(1)->nullable()->comment('类型 0=目录 1=菜单');
            //$table->string('cat',30)->default('menu')->nullable()->comment('类型 menu=主菜单, goods=商品分类');
            $table->string('guard_name',30)->default('')->nullable()->comment('守卫');
            $table->string('url',255)->default('')->nullable()->comment('菜单URL');
            $table->string('icon',50)->default('')->nullable()->comment('图标');
            $table->string('perms',100)->default('')->nullable()->comment('权限关联(路由别名)');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序');
            $table->tinyInteger("is_show")->default(1)->nullable()->comment('是否展示：0=否 1=是');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("创建时间");
            $table->char('create_user', 32)->default('0')->nullable()->comment("创建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("删除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("删除人");
        });
        $table = DB::getTablePrefix().'menu';
        DB::statement("ALTER TABLE `{$table}` comment'菜单表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('menu');
    }
}
