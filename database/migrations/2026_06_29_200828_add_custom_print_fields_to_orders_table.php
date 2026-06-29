<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type', 20)->default('standard')->after('source');
            $table->string('design_file')->nullable()->after('order_type');
            $table->text('design_notes')->nullable()->after('design_file');
            $table->string('print_method', 50)->nullable()->after('design_notes');
            $table->json('print_positions')->nullable()->after('print_method');
            $table->string('production_status', 30)->nullable()->after('print_positions');
            $table->text('production_notes')->nullable()->after('production_status');
            $table->date('estimated_delivery_date')->nullable()->after('production_notes');
            $table->decimal('advance_amount', 10, 2)->default(0)->after('estimated_delivery_date');

            $table->index('order_type');
            $table->index('production_status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['order_type']);
            $table->dropIndex(['production_status']);
            $table->dropColumn([
                'order_type', 'design_file', 'design_notes',
                'print_method', 'print_positions', 'production_status',
                'production_notes', 'estimated_delivery_date', 'advance_amount',
            ]);
        });
    }
};
