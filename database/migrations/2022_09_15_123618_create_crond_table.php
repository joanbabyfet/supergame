<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCrondTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crond', function (Blueprint $table) {
            $table->Increments('id');
            $table->string('runtime_format', 20)->default('')->nullable()->comment('执行时间格式');
            $table->string('command_name', 100)->default('')->nullable()->comment('任务脚本');
            $table->integer('lasttime')->default(0)->nullable()->comment('最后执行时间');
            $table->string('runtime', 30)->default('0')->nullable()->comment('运行时间');
            $table->integer('update_time')->default(0)->nullable()->comment("更新时间");
            $table->unique(['command_name']);
        });
        $table = DB::getTablePrefix().'crond';
        DB::statement("ALTER TABLE `{$table}` comment'计划任务表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crond');
    }
}
