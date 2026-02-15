<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $permission = Permission::create([
            'name' => 'activity-logs.view',
            'description' => 'View Activity Logs',
        ]);

        // Attach to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->permissions()->attach($permission->id);
        }
    }

    public function down(): void
    {
        Permission::where('name', 'activity-logs.view')->delete();
    }
};
