<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'timers.view', 'description' => 'View Timers'],
            ['name' => 'timers.create', 'description' => 'Create Timers'],
            ['name' => 'timers.edit', 'description' => 'Edit Timers'],
            ['name' => 'timers.delete', 'description' => 'Delete Timers'],
            ['name' => 'timers.run', 'description' => 'Run Timers'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $p = Permission::create($permission);
            $permissionIds[] = $p->id;
        }

        // Attach to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->attach($permissionIds);
        }
    }

    public function down(): void
    {
        $permissionNames = ['timers.view', 'timers.create', 'timers.edit', 'timers.delete', 'timers.run'];
        Permission::whereIn('name', $permissionNames)->delete();
    }
};
