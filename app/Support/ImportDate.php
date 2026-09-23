<?php

namespace App\Support;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

final class ImportDate
{
  public static function normalize(mixed $value): ?string
  {
    if ($value instanceof \DateTimeInterface) return Carbon::instance($value)->format('Y-m-d');
    if (is_numeric($value) && ((float)$value) > 0 && ((float)$value) < 100000) return Carbon::instance(ExcelDate::excelToDateTimeObject((float)$value))->format('Y-m-d');
    $value = trim((string)$value);
    if ($value === '' || $value === '-') return null;
    foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
      try {
        $date = Carbon::createFromFormat($format, $value);
        if ($date && $date->format($format) === $value) return $date->format('Y-m-d');
      } catch (\Throwable) {
      }
    }
    return $value;
  }
}
