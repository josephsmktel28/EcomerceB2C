<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal', 16, 2)->change();
            $table->decimal('discount', 16, 2)->default(0)->change();
            $table->decimal('tax', 16, 2)->change();
            $table->decimal('total', 16, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('subtotal')->change();
            $table->decimal('discount')->default(0)->change();
            $table->decimal('tax')->change();
            $table->decimal('total')->change();
        });
    }
};
