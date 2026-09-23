<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
  public function run(): void
  {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
    $this->call(UnitSeeder::class);
    $this->call(MapelSeeder::class);

    $superadminRole = Role::findOrCreate('Superadmin', 'web');
    $adminRole = Role::findOrCreate('Admin Unit', 'web');

    $superadmin = User::updateOrCreate(
      ['email' => env('SEED_SUPERADMIN_EMAIL', 'superadmin@example.com')],
      ['name' => 'Superadmin', 'password' => env('SEED_SUPERADMIN_PASSWORD', 'Superadmin123!')]
    );
    $superadmin->syncRoles($superadminRole);

    $admin = User::updateOrCreate(
      ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.com')],
      ['name' => 'Admin Unit', 'password' => env('SEED_ADMIN_PASSWORD', 'Admin123!')]
    );
    $admin->syncRoles($adminRole);
  }
}
