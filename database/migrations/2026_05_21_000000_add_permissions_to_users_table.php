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
        Schema::table('users', function (Blueprint $table) {
            $table->json('permissions')->nullable()->after('role');
        });

        // Seed default permissions for existing users based on their role
        $defaultPerms = [
            'admin' => [
                'dashboard', 'analytics', 'orders', 'orders.delete', 'orders.bulk_upload',
                'products', 'categories', 'customers', 'pathao', 'accounting',
                'purchases', 'expenses', 'pos', 'settings', 'users',
            ],
            'manager' => [
                'dashboard', 'orders', 'orders.delete', 'orders.bulk_upload',
                'products', 'categories', 'customers', 'pathao', 'accounting',
                'purchases', 'expenses', 'pos',
            ],
            'operational_staff' => [
                'dashboard', 'orders', 'orders.bulk_upload',
                'products', 'categories', 'customers', 'pathao', 'pos',
            ],
            'accountant' => [
                'dashboard', 'accounting', 'purchases', 'expenses',
            ],
        ];

        foreach (\App\Models\User::all() as $user) {
            $role = $user->role ?? 'operational_staff';
            $perms = $defaultPerms[$role] ?? $defaultPerms['operational_staff'];
            $user->update(['permissions' => $perms]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
