<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
  public function run(): void
  {
    foreach (
      [
        [835, 'BANDA ACEH', 'LUENG BATA 2A'],
        [968, 'BANDA ACEH', 'T. NYAK ARIF 6'],
        [901, 'BANDA ACEH', 'MERDUATI 5'],
        [1375, 'KUALA SIMPANG', 'H. Juanda 1 A'],
        [427, 'LANGSA', 'AHMAD YANI 10 (LANGSA)'],
        [1401, 'LHOKSEUMAWE', 'DARUSSALAM 7'],
        [967, 'SIGLI', 'JL. PROF. A. MAJID IBRAHIM 16'],
        [658, 'TAKENGON', 'Soekarno Hatta No 57']
      ] as [$id, $kota, $nama]
    ) {
      $unit = Unit::where('kota', $kota)->where('nama_unit', $nama)->first();
      if ($unit && $unit->id !== $id) $unit->update(['id' => $id]);
      Unit::updateOrCreate(['id' => $id], ['kota' => $kota, 'nama_unit' => $nama]);
    }
  }
}
