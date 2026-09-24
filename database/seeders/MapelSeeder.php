<?php

namespace Database\Seeders;

use App\Models\Mapel;
use Illuminate\Database\Seeder;

class MapelSeeder extends Seeder
{
  public function run(): void
  {
    foreach (['MAT', 'FIS', 'KIM', 'BIO', 'GEO', 'SOS', 'INFOR', 'MAT-TL', 'ING-TL', 'SEJ', 'IPA', 'IPS', 'PKN', 'EKO', 'B.INDO', 'B.ING'] as $kode) Mapel::updateOrCreate(['kode' => $kode], ['nama' => $kode]);
  }
}
