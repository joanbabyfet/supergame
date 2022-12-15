<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWalletsTable extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->morphs('holder'); //2021-10-10修改
            $table->string("holder_type");
            $table->char("holder_id", 32)->default('');
            $table->index(["holder_type", "holder_id"], null); //索引

            $table->string('name');
            $table->string('slug')->index();
            $table->uuid('uuid')->unique();
            $table->string('description')->nullable();
            $table->json('meta')->nullable();
            $table->decimal('balance', 64, 2)->default(0);
            $table->unsignedSmallInteger('decimal_places')->default(2);
            $table->timestamps();

            $table->unique(['holder_type', 'holder_id', 'slug']);
        });
        $table = DB::getTablePrefix().$this->table();
        DB::statement("ALTER TABLE `{$table}` comment'用戶錢包表'"); // 表注释

        //干掉级联
//        Schema::table($this->transactionTable(), function (Blueprint $table) {
//            $table->foreign('wallet_id')
//                ->references('id')
//                ->on($this->table())
//                ->onDelete('cascade')
//            ;
//        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::drop($this->table());
    }

    protected function table(): string
    {
        return (new Wallet())->getTable();
    }

    private function transactionTable(): string
    {
        return (new Transaction())->getTable();
    }
}
