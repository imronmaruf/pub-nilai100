<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
  public function run(): void
  {
    $superadminRole = Role::findOrCreate('Superadmin', 'web');
    $adminRole = Role::findOrCreate('Admin Unit', 'web');

    $password = env('SEED_USER_PASSWORD', 'Password123!');

    $users = [
      ['name' => 'Ashri', 'email' => 'ksp@go.id', 'role' => 'Admin Unit', 'kota' => 'KUALA SIMPANG', 'unit' => 'H. Juanda 1 A'],
      ['name' => 'Dina', 'email' => 'tkg@go.id', 'role' => 'Admin Unit', 'kota' => 'TAKENGON', 'unit' => 'Soekarno Hatta No 57'],
      ['name' => "Imron Ma'ruf", 'email' => 'imronmaruff@gmail.com', 'role' => 'Superadmin', 'kota' => null, 'unit' => 'Semua unit'],
      ['name' => 'Mita', 'email' => 'lb@go.id', 'role' => 'Admin Unit', 'kota' => 'BANDA ACEH', 'unit' => 'LUENG BATA 2A'],
      ['name' => 'Nefo', 'email' => 'lsm@go.id', 'role' => 'Admin Unit', 'kota' => 'LHOKSEUMAWE', 'unit' => 'DARUSSALAM 7'],
      ['name' => 'Nurul', 'email' => 'md@go.id', 'role' => 'Admin Unit', 'kota' => 'BANDA ACEH', 'unit' => 'MERDUATI 5'],
      ['name' => 'Rifka', 'email' => 'slg@co.id', 'role' => 'Admin Unit', 'kota' => 'SIGLI', 'unit' => 'JL. PROF. A. MAJID IBRAHIM 16'],
      ['name' => 'Sarah', 'email' => 'sarah@go.id', 'role' => 'Admin Unit', 'kota' => 'BANDA ACEH', 'unit' => 'LUENG BATA 2A'],
      ['name' => 'Superadmin', 'email' => 'superadmin@example.com', 'role' => 'Superadmin', 'kota' => null, 'unit' => 'Semua unit'],
      ['name' => 'Wirda', 'email' => 'lgs@go.id', 'role' => 'Admin Unit', 'kota' => 'LANGSA', 'unit' => 'AHMAD YANI 10 (LANGSA)'],
    ];

    foreach ($users as $u) {
      $unitId = null;
      if ($u['role'] === 'Admin Unit') {
        $unit = Unit::where('kota', $u['kota'])
          ->whereRaw('LOWER(nama_unit) = ?', [strtolower($u['unit'])])
          ->first();
        if (! $unit) {
          $this->command?->warn("Unit tidak ditemukan untuk {$u['email']} ({$u['kota']} / {$u['unit']}) — user dibuat tanpa unit.");
        }
        $unitId = $unit?->id;
      }

      $user = User::updateOrCreate(
        ['email' => $u['email']],
        ['name' => $u['name'], 'password' => $password, 'unit_id' => $unitId]
      );
      $user->syncRoles($u['role'] === 'Superadmin' ? $superadminRole : $adminRole);
    }
  }
}
