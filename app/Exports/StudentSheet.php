<?php

namespace App\Exports;

use App\Models\User;
use App\Services\Access;
use Maatwebsite\Excel\Concerns\FromGenerator;

class StudentSheet extends BaseSheet implements FromGenerator
{
  public function __construct(protected User $actor, protected ?int $unitId = null, protected string $name = 'All Data') {}
  public function title(): string
  {
    return $this->name;
  }
  public function headings(): array
  {
    return ['Student ID', 'Noreg', 'Nama', 'Asal Sekolah', 'Tingkat Kelas', 'Kelas di GO', 'Level', 'Unit ID', 'Kota', 'Unit', 'Nilai ID', 'Mapel', 'Jenis Nilai', 'Tanggal Ujian', 'Validasi PT', 'Testimoni', 'STATUS PUBLIKASI (SUDAH/BELUM)'];
  }
  protected function base($s): array
  {
    $n = $s->nilai100->sortByDesc('tanggal_ujian')->first();
    $publicationStatus = ($s->has_ig_publication || $s->has_tiktok_publication || $s->has_wa_publication) ? 'SUDAH' : 'BELUM';
    return [$s->id, $s->noreg, $s->nama_siswa, $s->asal_sekolah, $s->tingkat_kelas, $s->kelas_di_go, $s->level, $s->unit_id, $s->unit->kota, $s->unit->nama_unit, $n?->id, $n?->mapel, $n?->jenis_nilai, $n?->tanggal_ujian?->format('d/m/Y'), $n?->tanggal_validasi_pt?->format('d/m/Y'), $n?->status_testimoni, $publicationStatus];
  }
  protected function query()
  {
    return app(Access::class)->students($this->actor)->when($this->unitId !== null, fn($q) => $q->where('unit_id', $this->unitId))->with('unit', 'nilai100')
      ->withExists(['instagram as has_ig_publication' => fn($q) => $q->where('status_publikasi', 'SUDAH')->where('jumlah_postingan', '>', 0)])
      ->withExists(['tiktok as has_tiktok_publication' => fn($q) => $q->where('jumlah_postingan', '>', 0)])
      ->withExists(['whatsapp as has_wa_publication' => fn($q) => $q->where('jumlah_testimoni_terkirim', '>', 0)])
      ->orderBy('id');
  }
  public function generator(): \Generator
  {
    foreach ($this->query()->lazyById(500) as $s) yield $this->base($s);
  }
}
