<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\{WithMultipleSheets, FromArray, WithHeadings, WithTitle};

class ActivityTemplateExport implements WithMultipleSheets
{
  public function __construct(private string $channel) {}
  public function sheets(): array
  {
    return [new ActivityDataSheet($this->channel), new ActivityGuideSheet($this->channel)];
  }
}

class ActivityDataSheet implements FromArray, WithHeadings, WithTitle
{
  public function __construct(private string $channel) {}
  public function title(): string
  {
    return strtoupper($this->channel);
  }
  public function headings(): array
  {
    return match ($this->channel) {
      'nilai' => ['Noreg', 'Mapel', 'Jenis Nilai', 'Tanggal Ujian', 'Tanggal Validasi PT', 'Status Testimoni'],
      'ig' => ['Noreg', 'Status Publikasi', 'Jumlah Postingan', 'Tanggal Posting', 'Link Postingan', 'View', 'Like', 'Komen'],
      'tiktok' => ['Noreg', 'Jumlah Postingan', 'Tanggal Posting', 'Link Postingan', 'View', 'Like', 'Komen'],
      'wa' => ['Noreg', 'Jumlah Testimoni Terkirim', 'Tanggal Blast', 'Terkirim', 'Dibaca', 'Respon']
    };
  }
  public function array(): array
  {
    return [];
  }
}

class ActivityGuideSheet implements FromArray, WithTitle
{
  public function __construct(private string $channel) {}
  public function title(): string
  {
    return 'Panduan';
  }
  public function array(): array
  {
    $common = [['PANDUAN PENGISIAN TEMPLATE ' . strtoupper($this->channel)], ['Gunakan sheet ' . strtoupper($this->channel) . ' untuk data yang akan diimport.'], ['Tanggal wajib menggunakan format DD/MM/YYYY.'], ['Noreg harus sudah terdaftar pada menu Data Siswa.']];
    return match ($this->channel) {
      'nilai' => array_merge($common, [['Mapel yang boleh diisi: MAT, FIS, KIM, BIO, GEO, SOS, INFOR, MAT-TL, ING-TL, SEJ, IPA, IPS, PKN, EKO, B.INDO, B.ING.'], ['Jenis Nilai yang boleh diisi: UH, PTS, PAS, PAT, US.'], ['Status Testimoni yang boleh diisi: SUDAH atau BELUM.'], ['Satu baris adalah satu catatan Nilai 100. Noreg yang sama boleh muncul untuk mapel atau tanggal berbeda.']]),
      'ig' => array_merge($common, [['Status Publikasi yang boleh diisi: SUDAH atau BELUM.'], ['Jika SUDAH, isi jumlah postingan, tanggal, link HTTP/HTTPS, view, like, dan komen.'], ['Jumlah postingan minimal 1 untuk status SUDAH.']]),
      'tiktok' => array_merge($common, [['Isi jumlah postingan minimal 1, tanggal posting, link HTTP/HTTPS, view, like, dan komen.']]),
      'wa' => array_merge($common, [['Jumlah Testimoni Terkirim minimal 1.'], ['Nilai Dibaca tidak boleh lebih besar dari Terkirim.'], ['Nilai Respon tidak boleh lebih besar dari Dibaca.']])
    };
  }
}
