<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auction_winners', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('bid_id')->nullable()->index();
            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('auction_winners');
    }
};
