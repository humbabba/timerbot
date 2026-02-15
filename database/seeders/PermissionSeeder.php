<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create User model permissions
        $this->createPermissionsForModel('users', 'Users');

        // Create Role model permissions
        $this->createPermissionsForModel('roles', 'Roles');

        // Create Node model permissions
        $this->createPermissionsForModel('nodes', 'Nodes');
        Permission::create(['name' => 'nodes.run', 'description' => 'Run Nodes']);

        // Create Wave model permissions
        $this->createPermissionsForModel('waves', 'Waves');
        Permission::create(['name' => 'waves.run', 'description' => 'Run Waves']);

        // Create admin role with all permissions
        $adminRole = Role::create([
            'name' => 'admin',
            'description' => 'Administrator with full access.',
        ]);

        $adminRole->permissions()->attach(Permission::all());

        // Admin can assign all roles (including itself)
        $adminRole->assignableRoles()->attach(Role::all());

        // Create admin user with admin role
        $adminUser = User::firstOrCreate(
            ['email' => 'humbabba@gmail.com'],
            ['name' => 'Charles Gray']
        );

        $adminUser->roles()->attach($adminRole);
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
