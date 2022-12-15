<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('country', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',60)->default('')->nullable()->comment('名称');
            $table->string('en_short_name',15)->default('')->nullable()->comment('英文缩写');
            $table->string('en_name',60)->default('')->nullable()->comment('英文名称');
            $table->tinyInteger('is_fix')->default(0)->nullable()->comment('是否固定(会员不能手动选择)： 0=否， 1=是');
            $table->tinyInteger('is_default')->default(0)->nullable()->comment('是否默认：0=否，1=是');
            $table->string('mobile_prefix',10)->default('')->nullable()->comment('电话前缀');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
        });
        $table = DB::getTablePrefix().'country';
        DB::statement("ALTER TABLE `{$table}` comment'國家表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('country');
    }
}
