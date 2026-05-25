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
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('deducted_in_payroll_id')->nullable();
            $table->timestamps();
            
            // We cannot add a foreign key to payrolls yet if payrolls table is not created, 
            // but the migration order is attendances -> advances -> payrolls. 
            // Wait, I will just leave it as unsignedBigInteger and add the foreign key constraint later or ignore it for now.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
