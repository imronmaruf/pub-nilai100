<?php

namespace App\Support;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

final class ImportDate
{
  public static function normalize(mixed $value): ?string
  {
    // 1. Jika membaca format objek DateTime bawaan
    if ($value instanceof \DateTimeInterface) {
      return Carbon::instance($value)->format('Y-m-d');
    }

    // 2. Jika membaca format angka/serial dari cell Excel Date
    if (is_numeric($value) && ((float)$value) > 0 && ((float)$value) < 100000) {
      return Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value))->format('Y-m-d');
    }

    $value = trim((string)$value);
    if ($value === '' || $value === '-') {
      return null;
    }

    // 3. Daftar variasi format ketikan manual yang sering terjadi di Excel
    $formats = [
      'Y/m/d', // 2026/09/25
      'd/m/Y', // 25/09/2026
      'Y-m-d', // 2026-09-25
      'd-m-Y', // 25-09-2026
      'Y.m.d', // 2026.09.25
      'd.m.Y', // 25.09.2026
    ];

    foreach ($formats as $format) {
      try {
        $date = Carbon::createFromFormat($format, $value);
        // Pastikan format cocok persis untuk menghindari kesalahan konversi
        if ($date && $date->format($format) === $value) {
          return $date->format('Y-m-d'); // Tetap return Y-m-d untuk kebutuhan DB
        }
      } catch (\Throwable) {
        // Abaikan error, lanjut cek format berikutnya
      }
    }

    // 4. Fallback: biarkan Carbon menebak otomatis (contoh: "25 Sep 2026")
    try {
      return Carbon::parse($value)->format('Y-m-d');
    } catch (\Throwable) {
      return $value; // Kembalikan nilai asli agar ditangkap oleh Validator jika gagal semua
    }
  }
}
