<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('config', function (Blueprint $table) {
            $table->string('type',10)->default('string')->nullable()->comment('變量類型');
            $table->string('name',80)->default('')->nullable()->comment('變量名');
            $table->text('value')->nullable()->comment('變量值');
            $table->string('title',50)->default('')->nullable()->comment('說明標題');
            //$table->string('info', 200)->default('')->nullable()->comment('備註');
            //$table->smallInteger("groupid")->default(1)->nullable()->comment('分組id');
            $table->string('group',20)->default('config')->nullable()->comment('分組');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->primary(['name']);
            $table->index('name');
        });
        $table = DB::getTablePrefix().'config';
        DB::statement("ALTER TABLE `{$table}` comment'系统配置变量表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('config');
    }
}
