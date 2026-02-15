<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'voice-rewrites.view', 'description' => 'View Voice Rewrites'],
            ['name' => 'voice-rewrites.create', 'description' => 'Create Voice Rewrites'],
            ['name' => 'voice-rewrites.edit', 'description' => 'Edit Voice Rewrites'],
            ['name' => 'voice-rewrites.delete', 'description' => 'Delete Voice Rewrites'],
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
        $permissionNames = ['voice-rewrites.view', 'voice-rewrites.create', 'voice-rewrites.edit', 'voice-rewrites.delete'];
        Permission::whereIn('name', $permissionNames)->delete();
    }
};
