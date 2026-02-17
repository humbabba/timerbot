<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create all permissions
        $this->createPermissionsForModel('users', 'Users');
        $this->createPermissionsForModel('roles', 'Roles');
        Permission::create(['name' => 'timers.create', 'description' => 'Create Timers']);
        Permission::create(['name' => 'trash.view', 'description' => 'View Trash']);
        Permission::create(['name' => 'trash.restore', 'description' => 'Restore Trash']);
        Permission::create(['name' => 'trash.delete', 'description' => 'Delete Trash']);
        Permission::create(['name' => 'settings.manage', 'description' => 'Manage Settings']);
        Permission::create(['name' => 'activity-logs.view', 'description' => 'View Activity Logs']);

        // Admin role — all permissions
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Administrator with full access.',
        ]);
        $adminRole->permissions()->attach(Permission::all());

        // User role — can create timers
        $userRole = Role::create([
            'name' => 'user',
            'description' => 'Standard user with timer access.',
        ]);
        $userRole->permissions()->attach(
            Permission::where('name', 'timers.create')->pluck('id')
        );

        // Admin can assign all roles (including itself)
        $adminRole->assignableRoles()->attach(Role::all());

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'humbabba@gmail.com'],
            ['name' => 'Charles Gray']
        );
        $adminUser->roles()->attach($adminRole);

        // Seed default app settings
        AppSetting::create([
            'key' => 'trash_retention_days',
            'value' => '30',
            'type' => 'integer',
            'group' => 'trash',
            'description' => 'Number of days to retain items in trash before automatic cleanup',
        ]);
        AppSetting::create([
            'key' => 'trash_auto_cleanup_enabled',
            'value' => 'true',
            'type' => 'boolean',
            'group' => 'trash',
            'description' => 'Enable automatic cleanup of old trash items',
        ]);
        AppSetting::create([
            'key' => 'news',
            'value' => '',
            'type' => 'richtext',
            'group' => 'general',
            'description' => 'News or announcements to display to users (supports HTML)',
        ]);
    }

    protected function createPermissionsForModel(string $model, string $label): void
    {
        $actions = [
            'view' => "View {$label}",
            'create' => "Create {$label}",
            'edit' => "Edit {$label}",
            'delete' => "Delete {$label}",
        ];

        foreach ($actions as $action => $description) {
            Permission::create([
                'name' => "{$model}.{$action}",
                'description' => $description,
            ]);
        }
    }
}
