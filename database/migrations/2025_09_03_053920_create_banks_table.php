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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('emp_id'); // employee id
            $table->string('bank_name'); // bank name
            $table->string('account_no')->unique(); // account number
            $table->string('ifsc', 11); // IFSC code (11 chars max)
            $table->string('account_holder_name'); // account holder name
            $table->enum('status', ['active','inactive'])->default('active'); // status
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
