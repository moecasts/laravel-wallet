<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('holder');
            $table->unsignedBigInteger('wallet_id')->comment('钱包 id');
            $table->enum('type', ['deposit', 'withdraw'])->index()->comment('交易类型');
            $table->bigInteger('amount')->comment('交易数额');
            $table->boolean('confirmed')->comment('是否交易完成');
            $table->json('meta')->nullable()->comment('交易信息');
            $table->uuid('uuid')->unique()->comment('交易 uuid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
