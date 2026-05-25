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
        Schema::create('businesses', function (Blueprint $table) {
              $table->id();
            $table->string('domain_name')->unique();
            $table->string('business_id')->unique();
            $table->string('business_email')->unique();
            $table->string('business_phone')->uniqid();
            $table->string('name');
            $table->string('title');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('city');
            $table->string('pin', 10);
            $table->string('sidebar_color')->default('#4f46e5'); // RGB Hex/Code
            $table->string('icon_color')->default('#f43f5e');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
