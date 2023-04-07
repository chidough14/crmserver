<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->json('products');
            $table->integer('subtotal')->nullable();
            $table->integer('total');
            $table->json('shipping')->nullable();
            $table->string('delivery_status')->nullable();
            $table->string('payment_status')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
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
        Schema::dropIfExists('stripe_payments');
    }
};
