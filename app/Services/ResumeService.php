<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class ResumeService
{
  public const HEADINGS = ['NO', 'KOTA', 'UNIT', 'JUMSIS', 'JUMLAH SISWA NILAI 100', 'NILAI 100 UH/PTS/PAS/PAT/US', 'SUDAH TESTIMONI', 'BELUM TESTIMONI', 'SUDAH DIPUBLIKASI', 'IG Post', 'IG View', 'IG Like', 'IG Komen', 'TikTok Post', 'TikTok View', 'TikTok Like', 'TikTok Komen', 'WA Kirim', 'WA Terkirim', 'WA Dibaca', 'WA Respon'];
  public const FIELDS = ['kota', 'nama_unit', 'jumsis', 'nilai_100_students', 'nilai_total', 'testimoni', 'belum_testimoni', 'dipublikasi', 'ig_post', 'ig_view', 'ig_like', 'ig_komen', 'tt_post', 'tt_view', 'tt_like', 'tt_komen', 'wa_kirim', 'wa_terkirim', 'wa_dibaca', 'wa_respon'];
  public function query(User $actor, ?Request $request = null): Builder
  {
    $ids = app(Access::class)->units($actor)->select('id');
    $students = DB::table('students as s')->leftJoin('nilai_100 as n', function ($join) use ($request) {
      $join->on('n.student_id', '=', 's.id')
        ->when($request?->filled('from'), fn($q) => $q->whereDate('n.tanggal_ujian', '>=', $request->input('from')))
        ->when($request?->filled('to'), fn($q) => $q->whereDate('n.tanggal_ujian', '<=', $request->input('to')));
    })->whereIn('s.unit_id', clone $ids)
      ->groupBy('s.unit_id')
      ->selectRaw("s.unit_id, COUNT(DISTINCT s.id) jumsis, COUNT(DISTINCT CASE WHEN n.id IS NOT NULL THEN s.id END) nilai_100_students, COUNT(n.id) nilai_total, COUNT(DISTINCT CASE WHEN n.status_testimoni='SUDAH' THEN s.id END) testimoni, COUNT(DISTINCT CASE WHEN n.status_testimoni='BELUM' THEN s.id END) belum_testimoni")
      ->selectRaw("COUNT(DISTINCT CASE WHEN EXISTS(SELECT 1 FROM publikasi_ig i WHERE i.student_id=s.id AND i.status_publikasi='SUDAH' AND i.jumlah_postingan>0) OR EXISTS(SELECT 1 FROM publikasi_tiktok t WHERE t.student_id=s.id AND t.jumlah_postingan>0) OR EXISTS(SELECT 1 FROM publikasi_wa w WHERE w.student_id=s.id AND w.jumlah_testimoni_terkirim>0) THEN s.id END) dipublikasi");
    $q = DB::table('units as u')->whereIn('u.id', clone $ids)->leftJoinSub($students, 's', 's.unit_id', '=', 'u.id')->select('u.id', 'u.kota', 'u.nama_unit')
      ->when($request?->filled('kota'), fn($q) => $q->where('u.kota', $request->input('kota')))
      ->when($request?->filled('unit_id'), fn($q) => $q->where('u.id', $request->input('unit_id')));
    foreach (['jumsis', 'nilai_100_students', 'nilai_total', 'testimoni', 'belum_testimoni', 'dipublikasi'] as $f) $q->selectRaw("COALESCE(s.$f,0) AS $f");
    foreach (['ig' => ['publikasi_ig', ['jumlah_postingan' => 'post', 'view' => 'view', 'like' => 'like', 'komen' => 'komen']], 'tt' => ['publikasi_tiktok', ['jumlah_postingan' => 'post', 'view' => 'view', 'like' => 'like', 'komen' => 'komen']], 'wa' => ['publikasi_wa', ['jumlah_testimoni_terkirim' => 'kirim', 'terkirim' => 'terkirim', 'dibaca' => 'dibaca', 'respon' => 'respon']]] as $alias => [$table, $metrics]) {
      $sub = DB::table($table . ' as p')->join('students as s', 's.id', '=', 'p.student_id')->whereIn('s.unit_id', clone $ids)->groupBy('s.unit_id')->select('s.unit_id');
      if ($alias === 'ig') $sub->where('p.status_publikasi', 'SUDAH');
      foreach ($metrics as $column => $metric) $sub->selectRaw("SUM(p.`$column`) AS `{$alias}_{$metric}`");
      $q->leftJoinSub($sub, $alias, "$alias.unit_id", '=', 'u.id');
      foreach ($metrics as $metric) $q->selectRaw("COALESCE($alias.{$alias}_{$metric},0) AS {$alias}_{$metric}");
    }
    return $q->orderBy('u.kota')->orderBy('u.nama_unit')->orderBy('u.id');
  }
  public function row(object $r, int $i): array
  {
    return array_merge([$i], array_map(fn($f) => in_array($f, ['kota', 'nama_unit']) ? $r->$f : (int)$r->$f, self::FIELDS));
  }
}
