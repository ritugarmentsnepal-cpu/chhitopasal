<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PHASE-2.3: customer approval links. A mockup gets a public share token
     * (wa.me-friendly URL); the customer approves or requests changes and the
     * response is recorded here + on the order timeline.
     */
    public function up(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->string('share_token', 32)->nullable()->unique()->after('tags');
            $table->string('approval_status', 30)->nullable()->after('share_token'); // pending | approved | changes_requested
            $table->text('approval_feedback')->nullable()->after('approval_status');
            $table->timestamp('approval_responded_at')->nullable()->after('approval_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('mockups', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'approval_status', 'approval_feedback', 'approval_responded_at']);
        });
    }
};
