<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up(): void
    {
        Schema::create($this->table(), function (Blueprint $table) {
            $table->bigIncrements('id');
            //$table->morphs('payable'); //2021-10-10修改
            $table->string("payable_type");
            $table->char("payable_id", 32)->default('');
            $table->index(["payable_type", "payable_id"], null);

            $table->unsignedBigInteger('wallet_id');
            $table->enum('type', ['deposit', 'withdraw'])->index();
            $table->decimal('amount', 64, 2);
            $table->boolean('confirmed');
            $table->json('meta')->nullable();
            $table->uuid('uuid')->unique();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id'], 'payable_type_payable_id_ind');
            $table->index(['payable_type', 'payable_id', 'type'], 'payable_type_ind');
            $table->index(['payable_type', 'payable_id', 'confirmed'], 'payable_confirmed_ind');
            $table->index(['payable_type', 'payable_id', 'type', 'confirmed'], 'payable_type_confirmed_ind');
        });
        $table = DB::getTablePrefix().$this->table();
        DB::statement("ALTER TABLE `{$table}` comment'钱包流水表'"); // 表注释
    }

    public function down(): void
    {
        Schema::drop($this->table());
    }

    private function table(): string
    {
        return (new Transaction())->getTable();
    }
}
