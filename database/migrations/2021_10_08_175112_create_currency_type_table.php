<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCurrencyTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_type', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',30)->default('')->nullable()->comment('名稱');
            $table->string('en_name',30)->default('')->nullable()->comment('英文名稱');
            $table->string('code',10)->default('')->nullable()->comment('英文縮寫');
            $table->string('symbol',10)->default('')->nullable()->comment('货币符号');
            $table->decimal('exg_rate',10, 6)->default(0.000000)->nullable()->comment('汇率:1美元兑换其它币种的汇率');
            $table->smallInteger("sort")->default(0)->nullable()->comment('排序：数字小的排前面');
            $table->tinyInteger("status")->default(1)->nullable()->comment('状态：0=禁用 1=启用');
            $table->integer('create_time')->default(0)->nullable()->comment("創建時間");
            $table->char('create_user', 32)->default('0')->nullable()->comment("創建人");
            $table->integer('update_time')->default(0)->nullable()->comment("修改時間");
            $table->char('update_user', 32)->default('0')->nullable()->comment("修改人");
            $table->integer('delete_time')->default(0)->nullable()->comment("刪除時間");
            $table->char('delete_user', 32)->default('0')->nullable()->comment("刪除人");
        });
        $table = DB::getTablePrefix().'currency_type';
        DB::statement("ALTER TABLE `{$table}` comment'幣種表'"); // 表注释
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_type');
    }
}
