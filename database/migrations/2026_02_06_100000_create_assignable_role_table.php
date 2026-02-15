<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignable_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assignable_role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['role_id', 'assignable_role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignable_role');
    }
};
