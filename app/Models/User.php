<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \App\Traits\Loggable;

    // All available permission keys
    const PERMISSIONS = [
        'dashboard'          => 'Dashboard',
        'analytics'          => 'Analytics',
        'orders'             => 'Orders',
        'orders.delete'      => 'Delete Orders',
        'orders.bulk_upload'  => 'Bulk Upload Orders',
        'products'           => 'Products',
        'categories'         => 'Categories',
        'customers'          => 'Customers CRM',
        'pathao'             => 'Pathao Manager',
        'accounting'         => 'Accounting',
        'purchases'          => 'Purchases',
        'expenses'           => 'Expenses',
        'pos'                => 'Point of Sale',
        'facebook_inbox'     => 'Facebook Inbox',
        'settings'           => 'System Settings',
        'users'              => 'Staff Management',
    ];

    // Permission groups for UI display
    const PERMISSION_GROUPS = [
        'Overview' => ['dashboard', 'analytics'],
        'E-Commerce' => ['orders', 'orders.delete', 'orders.bulk_upload', 'products', 'categories', 'customers'],
        'Fulfillment' => ['pathao'],
        'Marketing' => ['facebook_inbox'],
        'Finance' => ['accounting', 'purchases', 'expenses', 'pos'],
        'Administration' => ['settings', 'users'],
    ];

    // Default permissions per role preset
    const ROLE_PRESETS = [
        'admin' => [
            'dashboard', 'analytics', 'orders', 'orders.delete', 'orders.bulk_upload',
            'products', 'categories', 'customers', 'pathao', 'accounting',
            'purchases', 'expenses', 'pos', 'facebook_inbox', 'settings', 'users',
        ],
        'manager' => [
            'dashboard', 'orders', 'orders.delete', 'orders.bulk_upload',
            'products', 'categories', 'customers', 'pathao', 'accounting',
            'purchases', 'expenses', 'pos', 'facebook_inbox',
        ],
        'operational_staff' => [
            'dashboard', 'orders', 'orders.bulk_upload',
            'products', 'categories', 'customers', 'pathao', 'pos', 'facebook_inbox',
        ],
        'accountant' => [
            'dashboard', 'accounting', 'purchases', 'expenses',
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
        ];
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $key): bool
    {
        // Admin role always has all permissions as a safeguard
        if ($this->role === 'admin') {
            return true;
        }

        $perms = $this->permissions ?? self::getDefaultPermissions($this->role);
        return in_array($key, $perms);
    }

    /**
     * Get default permissions for a given role.
     */
    public static function getDefaultPermissions(string $role): array
    {
        return self::ROLE_PRESETS[$role] ?? self::ROLE_PRESETS['operational_staff'];
    }
}
