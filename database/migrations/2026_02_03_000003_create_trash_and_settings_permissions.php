<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'trash.view', 'description' => 'View Trash'],
            ['name' => 'trash.restore', 'description' => 'Restore items from Trash'],
            ['name' => 'trash.delete', 'description' => 'Permanently delete items from Trash'],
            ['name' => 'settings.manage', 'description' => 'Manage App Settings'],
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
        $permissionNames = ['trash.view', 'trash.restore', 'trash.delete', 'settings.manage'];
        Permission::whereIn('name', $permissionNames)->delete();
    }
};
