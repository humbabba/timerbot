<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            ['name' => 'voice-profiles.view', 'description' => 'View Voice Profiles'],
            ['name' => 'voice-profiles.create', 'description' => 'Create Voice Profiles'],
            ['name' => 'voice-profiles.edit', 'description' => 'Edit Voice Profiles'],
            ['name' => 'voice-profiles.delete', 'description' => 'Delete Voice Profiles'],
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
        $permissionNames = ['voice-profiles.view', 'voice-profiles.create', 'voice-profiles.edit', 'voice-profiles.delete'];
        Permission::whereIn('name', $permissionNames)->delete();
    }
};
