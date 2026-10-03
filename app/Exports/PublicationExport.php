<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\{FromQuery, WithMapping};

class PublicationExport extends BaseSheet implements FromQuery, WithMapping
{
  public function __construct(private Builder $records, private string $channel) {}

  public function title(): string
  {
    return strtoupper($this->channel);
  }

  public function headings(): array
  {
    $identity = ['Noreg', 'Nama', 'Unit', 'Mapel Nilai 100'];
    if ($this->channel === 'wa') return [...$identity, 'Tanggal Blast', 'Testimoni Terkirim', 'Terkirim', 'Dibaca', 'Respon'];
    return [...$identity, 'Status', 'Tanggal', 'Post', 'View', 'Like', 'Komen', 'Link'];
  }

  public function query(): Builder
  {
    return $this->records;
  }

  public function map($record): array
  {
    $identity = [$record->student->noreg, $record->student->nama_siswa, $record->student->unit->nama_unit, $record->nilai100?->mapelInfo?->nama ?? $record->nilai100?->mapel ?? 'Belum ditautkan'];
    if ($this->channel === 'wa') return [...$identity, $record->tanggal_blast?->format('d/m/Y'), $record->jumlah_testimoni_terkirim, $record->terkirim, $record->dibaca, $record->respon];
    return [...$identity, $this->channel === 'ig' ? $record->status_publikasi : 'SUDAH', $record->tanggal_posting?->format('d/m/Y'), $record->jumlah_postingan, $record->view, $record->like, $record->komen, $record->link_postingan];
  }
}
