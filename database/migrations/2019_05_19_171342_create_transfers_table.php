<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('from');
            $table->unsignedBigInteger('from_wallet_id');
            $table->string('action')->comment('操作行为：read/download/subscribe/trial 等');
            $table->morphs('to');
            $table->unsignedBigInteger('to_wallet_id');
            $table->bigInteger('fee')->defalut(0)->comment('手续费');
            $table->unsignedBigInteger('deposit_id')->comment('收款事务 id');
            $table->unsignedBigInteger('withdraw_id')->comment('付款事务 id');
            $table->uuid('uuid')->unique();
            $table->boolean('refund')->default(false)->comment('交易是否完成');
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
        Schema::dropIfExists('transfers');
    }
}
