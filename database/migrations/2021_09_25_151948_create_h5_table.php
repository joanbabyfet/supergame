<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateH5Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('h5', function (Blueprint $table) {
            $table->char('id', 32)->default('');
            $table->string('name',50)->default('')->nullable()->comment('H5页面标题');
            $table->text('content')->nullable()->comment('H5页面內容');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态 1:啟用 0:禁用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->primary(['id']);
        });
        $table = DB::getTablePrefix().'h5';
        DB::statement("ALTER TABLE `{$table}` comment'H5页面表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('h5');
    }
}
