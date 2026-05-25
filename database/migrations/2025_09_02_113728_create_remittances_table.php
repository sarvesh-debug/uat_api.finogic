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
        Schema::create('remittances', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('remId')->unique();
            $table->string('phone')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('gst_pan')->nullable(); // GSTIN or Business PAN
            $table->string('services'); // selected service
            $table->boolean('referral')->default(false); 
            $table->string('apikey')->nullable();
            $table->string('panno')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('monthly_limit')->nullable();
            $table->string('perday_limit')->nullable();
            $table->string('pincode')->nullable();
            $table->string('city')->nullable();
            $table->string('recipient_name')->nullable();
            $table->string('recipient_account')->nullable();
            $table->string('recipient_ifsc')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('lockamount', 12, 2)->nullable();
            $table->string('status')->default('pending'); // pending, success, failed
            $table->boolean('isKyc')->default(false); // true, false

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remittances');
    }
}; 