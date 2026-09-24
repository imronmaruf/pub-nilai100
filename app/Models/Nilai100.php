<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai100 extends Model
{
  public const VALIDASI_NOTE = 'cek tanggal validasi pt';
  protected $table = 'nilai_100';
  protected $fillable = ['student_id', 'mapel', 'jenis_nilai', 'tanggal_ujian', 'tanggal_validasi_pt', 'status_testimoni', 'keterangan'];
  protected function casts(): array
  {
    return ['tanggal_ujian' => 'date', 'tanggal_validasi_pt' => 'date'];
  }
  // Tanggal validasi PT boleh kosong: tandai keterangan agar diverifikasi, dan bersihkan catatan bila tanggal sudah diisi.
  public static function withValidasiNote(array $data): array
  {
    $ket = trim((string)($data['keterangan'] ?? ''));
    $note = self::VALIDASI_NOTE;
    if (empty($data['tanggal_validasi_pt'])) {
      if (!str_contains(mb_strtolower($ket), $note)) $ket = trim($ket . ($ket !== '' ? ' | ' : '') . $note);
      $data['keterangan'] = $ket !== '' ? $ket : null;
    } elseif (str_contains(mb_strtolower($ket), $note)) {
      $clean = trim(str_ireplace($note, '', $ket));
      $clean = trim(preg_replace('/\s*\|\s*/', ' | ', $clean) ?? '', " |");
      $data['keterangan'] = $clean !== '' ? $clean : null;
    }
    return $data;
  }
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }
}
