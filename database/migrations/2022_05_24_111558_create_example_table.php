<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateExampleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('example', function (Blueprint $table) {
            $table->char('id', 32)->default('');
            $table->integer('cat_id')->default(0)->nullable()->comment('分類id');
            $table->string('title',100)->default('')->nullable()->comment('標題');
            $table->text("content")->nullable()->comment('內容');
            $table->string('img',255)->default('')->nullable()->comment('圖片');
            $table->string('file',255)->default('')->nullable()->comment('附件');
            $table->tinyInteger("is_hot")->default(0)->nullable()->comment('是否热门新聞：0=否 1=是');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
            $table->primary(['id']);
            $table->index('title');
        });
        $table = DB::getTablePrefix().'example';
        DB::statement("ALTER TABLE `{$table}` comment'文章表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('example');
    }
}
