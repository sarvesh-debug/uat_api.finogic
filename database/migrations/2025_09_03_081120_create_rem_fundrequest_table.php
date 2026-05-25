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
         Schema::create('rem_fundrequest', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_by');
            $table->string('phone');
            $table->string('rid');
            $table->unsignedBigInteger('bank_id');
            $table->string('ifsc')->nullable();
            $table->string('account_no')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('openingBalance', 12, 2)->default(0.00);
            $table->decimal('closingBalance', 12, 2)->default(0.00);
            $table->string('utr');
            $table->date('date');
            $table->tinyInteger('status')->default(0);
            $table->string('mode');
            $table->json('slip_images')->nullable(); // ✅ JSON validated column
            $table->string('remark')->nullable();
            $table->text('admin_remark')->default('No Review');
            $table->string('employeeName')->nullable();
            $table->string('employeeId')->nullable();
            $table->timestamps();
            $table->decimal('totalAmount', 10, 2)->nullable();
            $table->decimal('tds', 10, 2)->nullable();
            $table->decimal('charges', 10, 2)->nullable();

            // Optional: add foreign key if banks table exists
            // $table->foreign('bank_id')->references('id')->on('banks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rem_fundrequest');
    }
};
