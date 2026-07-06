<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE-2.1: customer logos become first-class records — named, tied to
     * a customer (by phone, the app's customer identity), reusable across
     * orders and mockups. Existing per-mockup logo files are backfilled.
     */
    public function up(): void
    {
        Schema::create('customer_logos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 30)->nullable()->index();
            $table->string('file_path');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('mockups', function (Blueprint $table) {
            $table->foreignId('customer_logo_id')->nullable()->after('logo_path')
                ->constrained('customer_logos')->nullOnDelete();
        });

        // Backfill: one logo record per distinct file, enriched from the
        // most recent order-linked mockup that used it.
        $paths = DB::table('mockups')->whereNotNull('logo_path')->distinct()->pluck('logo_path');

        foreach ($paths as $path) {
            $mockup = DB::table('mockups')
                ->leftJoin('orders', 'orders.id', '=', 'mockups.order_id')
                ->where('mockups.logo_path', $path)
                ->orderByDesc('mockups.id')
                ->select('orders.customer_name', 'orders.customer_phone', 'mockups.order_id', 'mockups.title')
                ->first();

            $logoId = DB::table('customer_logos')->insertGetId([
                'name' => $mockup && $mockup->customer_name
                    ? $mockup->customer_name . ' logo'
                    : 'Logo — ' . basename($path),
                'customer_name' => $mockup->customer_name ?? null,
                'customer_phone' => $mockup->customer_phone ?? null,
                'file_path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('mockups')->where('logo_path', $path)->update(['customer_logo_id' => $logoId]);
        }
    }

    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_logo_id');
        });
        Schema::dropIfExists('customer_logos');
    }
};
