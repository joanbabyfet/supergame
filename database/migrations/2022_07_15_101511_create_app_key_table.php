<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateAppKeyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_key', function (Blueprint $table) {
            $table->char('app_id', 19)->default('')->comment("应用唯一标识");
            $table->char('app_key', 32)->default('')->nullable()->comment("应用私钥");
            $table->char('agent_id', 32)->default('')->nullable()->comment("渠道代理id");
            $table->string('desc',200)->default('')->nullable()->comment('说明');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->primary(['app_id']);
        });
        $table = DB::getTablePrefix().'app_key';
        DB::statement("ALTER TABLE `{$table}` comment'应用私匙表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_key');
    }
}
