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
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('status');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->foreignId('party_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('status');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('payment_status');
            $table->string('attachment_path')->nullable()->after('notes');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->string('attachment_path')->nullable()->after('reference_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_amount']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['party_id']);
            $table->dropColumn(['party_id', 'payment_status', 'paid_amount', 'attachment_path']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn(['expense_category_id', 'attachment_path']);
        });
    }
};
