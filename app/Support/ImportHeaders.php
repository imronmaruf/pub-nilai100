<?php

namespace App\Support;

use Illuminate\Support\Collection;

final class ImportHeaders
{
  public static function match(?Collection $head, array $expected): bool
  {
    $normalized = collect($head ?? [])
      ->take(count($expected))
      ->map(fn($v) => self::normalize($v))
      ->values()
      ->all();

    return $normalized === $expected;
  }

  public static function normalize(mixed $value): string
  {
    $value = preg_replace('/^\x{FEFF}/u', '', (string)$value) ?? (string)$value;
    $value = str_replace("\u{00A0}", ' ', $value);
    $value = strtolower(trim($value));

    return preg_replace('/\s+/u', ' ', $value) ?? $value;
  }
}
