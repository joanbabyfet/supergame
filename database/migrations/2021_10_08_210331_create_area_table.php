<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code',10)->default('')->nullable()->comment('手機號國碼');
            $table->string('name',30)->default('')->nullable()->comment('名稱');
            $table->string('en_name',50)->default('')->nullable()->comment('英文名稱');
            $table->string('en_short_name',15)->default('')->nullable()->comment('英文缩写');
            $table->tinyInteger("level")->default(0)->nullable()->comment('级别：0=国家，1=省份，2=城市');
            $table->integer('pid')->default(0)->nullable()->comment("上級ID");
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
        });
        $table = DB::getTablePrefix().'area';
        DB::statement("ALTER TABLE `{$table}` comment'全球国家、省州、区域表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('area');
    }
}
