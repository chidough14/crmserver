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
        Schema::create('offline_followers_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('follower_id')->nullable();
            $table->string('message')->nullable();
            $table->boolean('isRead')->nullable()->default(false);
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
        Schema::dropIfExists('offline_followers_messages');
    }
};
