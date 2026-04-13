<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $perm = Permission::firstOrCreate(
            ['name' => 'tienda.gestion', 'guard_name' => 'web'],
            ['name' => 'tienda.gestion', 'guard_name' => 'web']
        );

        foreach (['SuperAdmin', 'Administrador Hermandad', 'Secretaría', 'Mayordomía'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role && ! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
    }

    public function down(): void
    {
        Permission::query()->where('name', 'tienda.gestion')->where('guard_name', 'web')->delete();
    }
};
