<?php

namespace Database\Seeders;

use App\Models\Banco;
use App\Models\ConfiguracionHermandad;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Roles y permisos base
        $roles = [
            'SuperAdmin' => [
                'configuracion_hermandad.gestion',
                'usuarios.gestion',
                'roles.gestion',
                'hermanos.gestion',
                'informes.gestion',
                'contabilidad.gestion',
                'cuotas.gestion',
                'patrimonio.gestion',
                'inventario.gestion',
                'tienda.gestion',
                'cuadrillas.gestion',
            ],
            'Administrador Hermandad' => [
                'hermandad.ver',
                'hermandad.configurar',
                'hermanos.gestion',
                'informes.gestion',
                'contabilidad.gestion',
                'tienda.gestion',
                'cuadrillas.gestion',
            ],
            'Secretaría' => [
                'hermanos.gestion',
                'informes.gestion',
                'tienda.gestion',
                'cuadrillas.gestion',
            ],
            'Mayordomía' => [
                'contabilidad.gestion',
                'cuotas.gestion',
                'tienda.gestion',
            ],
            'Priostía' => [
                'patrimonio.gestion',
                'inventario.gestion',
            ],
        ];

        $permissions = array_unique(array_merge(...array_values($roles)));

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web'],
            );
        }

        foreach ($roles as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web'],
            );

            // Sincronizamos para que el seeder sea repetible.
            $role->syncPermissions(
                Permission::whereIn('name', $permissionNames)
                    ->where('guard_name', 'web')
                    ->pluck('name')
                    ->all()
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1.1) Catalogo de bancos editable (select en hermanos)
        $bancos = [
            ['nombre' => 'CaixaBank', 'codigo' => '2100'],
            ['nombre' => 'Banco Santander', 'codigo' => '0049'],
            ['nombre' => 'BBVA', 'codigo' => '0182'],
            ['nombre' => 'Unicaja', 'codigo' => '2103'],
            ['nombre' => 'Caja Rural del Sur', 'codigo' => '3183'],
        ];
        foreach ($bancos as $banco) {
            Banco::query()->updateOrCreate(['nombre' => $banco['nombre']], $banco);
        }

        // 2) Configuracion de hermandad (unico registro por instalacion)
        ConfiguracionHermandad::query()->updateOrCreate(
            ['id' => 1],
            [
                'nombre_hermandad' => 'Hermandad de la Sed',
                'nombre_corto' => 'La Sed',
                'cif' => 'G00000000',
                'direccion' => 'Calle Oracion 1, Sevilla',
                'localidad' => 'Sevilla',
                'cp' => '41018',
                'provincia' => 'Sevilla',
                'telefono' => '954000000',
                'email_contacto' => 'secretaria@hermandadsed.local',
                'iban_cuotas' => 'ES1200000000000000000000',
                'bic_swift' => 'BSCHESMMXXX',
            ]
        );

        // 3) Usuarios de prueba locales y asignacion de roles
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@test.local', 'role' => 'SuperAdmin'],
            ['name' => 'Admin Hermandad', 'email' => 'admin@test.local', 'role' => 'Administrador Hermandad'],
            ['name' => 'Secretaria', 'email' => 'secretario@test.local', 'role' => 'Secretaría'],
            ['name' => 'Mayordomia', 'email' => 'mayordomia@test.local', 'role' => 'Mayordomía'],
            ['name' => 'Priostia', 'email' => 'priostia@test.local', 'role' => 'Priostía'],
        ];

        foreach ($users as $seedUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $seedUser['email']],
                [
                    'name' => $seedUser['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            $user->syncRoles([$seedUser['role']]);
        }

        // 4) Patrimonio: categorías, estados de conservación y enseres de ejemplo.
        $this->call(CategoriaPatrimonioSeeder::class);
        $this->call(EstadoConservacionPatrimonioSeeder::class);
        $this->call(EnserSeeder::class);

        // 5) Contabilidad: plan de cuentas y ejercicios.
        $this->call(PlanContableSeeder::class);
        $this->call(EjercicioContableSeeder::class);

        // 6) Hermanos de ejemplo para pruebas del modulo.
        $this->call(HermanoSeeder::class);

        // 7) Cuenta portal de prueba (hermano n.º 1): /portal/login
        $this->call(HermanoPortalSeeder::class);
    }
}
