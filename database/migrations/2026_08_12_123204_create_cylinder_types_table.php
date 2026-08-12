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
        Schema::create('cylinder_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->decimal('capacity', 8, 2)->nullable()->comment('Default capacity in m3, for reference/autofill only');
            $table->decimal('price_premium', 10, 2)->default(0)->comment('Added on top of gas sale price to auto-suggest a cylinder sale price');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cylinder_types');
    }
};
