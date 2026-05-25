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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->decimal('bonus', 12, 2)->default(0);
            $table->decimal('incentives', 12, 2)->default(0);
            $table->decimal('advance_deductions', 12, 2)->default(0);
            $table->decimal('absent_deductions', 12, 2)->default(0);
            $table->decimal('net_payable', 12, 2)->default(0);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->date('payment_date')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'month', 'year']);
        });
        
        // Now add the foreign key to employee_advances
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->foreign('deducted_in_payroll_id')->references('id')->on('payrolls')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
