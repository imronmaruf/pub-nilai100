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
    foreach (['kota', 'unit_id'] as $key) {
      if ($request?->filled($key) && !is_array($request->input($key))) $request->merge([$key => [$request->input($key)]]);
    }
    $ids = app(Access::class)->units($actor)->select('id');
    $students = DB::table('students as s')->leftJoin('nilai_100 as n', function ($join) use ($request) {
      $join->on('n.student_id', '=', 's.id')
        ->when($request?->filled('from'), fn($q) => $q->whereDate('n.tanggal_ujian', '>=', $request->input('from')))
        ->when($request?->filled('to'), fn($q) => $q->whereDate('n.tanggal_ujian', '<=', $request->input('to')));
    })->whereIn('s.unit_id', clone $ids)
      ->groupBy('s.unit_id')
      ->selectRaw("s.unit_id, COUNT(DISTINCT s.id) jumsis, COUNT(DISTINCT CASE WHEN n.id IS NOT NULL THEN s.id END) nilai_100_students, COUNT(n.id) nilai_total, COUNT(DISTINCT CASE WHEN n.status_testimoni='SUDAH' THEN s.id END) testimoni, COUNT(DISTINCT CASE WHEN n.status_testimoni='BELUM' THEN s.id END) belum_testimoni")
      ->selectRaw($this->publishedStudentsSql($request)['sql'], $this->publishedStudentsSql($request)['bindings']);
    $q = DB::table('units as u')->whereIn('u.id', clone $ids)->leftJoinSub($students, 's', 's.unit_id', '=', 'u.id')->select('u.id', 'u.kota', 'u.nama_unit')
      ->when($request?->filled('kota'), fn($q) => $q->whereIn('u.kota', $request->input('kota')))
      ->when($request?->filled('unit_id'), fn($q) => $q->whereIn('u.id', $request->input('unit_id')));
    foreach (['jumsis', 'nilai_100_students', 'nilai_total', 'testimoni', 'belum_testimoni', 'dipublikasi'] as $f) $q->selectRaw("COALESCE(s.$f,0) AS $f");
    foreach (['ig' => ['publikasi_ig', ['jumlah_postingan' => 'post', 'view' => 'view', 'like' => 'like', 'komen' => 'komen']], 'tt' => ['publikasi_tiktok', ['jumlah_postingan' => 'post', 'view' => 'view', 'like' => 'like', 'komen' => 'komen']], 'wa' => ['publikasi_wa', ['jumlah_testimoni_terkirim' => 'kirim', 'terkirim' => 'terkirim', 'dibaca' => 'dibaca', 'respon' => 'respon']]] as $alias => [$table, $metrics]) {
      $sub = DB::table($table . ' as p')->join('students as s', 's.id', '=', 'p.student_id')->whereIn('s.unit_id', clone $ids)->groupBy('s.unit_id')->select('s.unit_id');
      if ($alias === 'ig') $sub->where('p.status_publikasi', 'SUDAH');
      $dateColumn = $alias === 'wa' ? 'tanggal_blast' : 'tanggal_posting';
      $sub->when($request?->filled('from'), fn($query) => $query->whereDate("p.$dateColumn", '>=', $request->input('from')))
        ->when($request?->filled('to'), fn($query) => $query->whereDate("p.$dateColumn", '<=', $request->input('to')));
      foreach ($metrics as $column => $metric) $sub->selectRaw("SUM(p.`$column`) AS `{$alias}_{$metric}`");
      $q->leftJoinSub($sub, $alias, "$alias.unit_id", '=', 'u.id');
      foreach ($metrics as $metric) $q->selectRaw("COALESCE($alias.{$alias}_{$metric},0) AS {$alias}_{$metric}");
    }
    return $q->orderBy('u.kota')->orderBy('u.nama_unit')->orderBy('u.id');
  }
  private function publishedStudentsSql(?Request $request): array
  {
    $conditions = [];
    $bindings = [];
    foreach (['i' => ['publikasi_ig', 'jumlah_postingan', 'tanggal_posting'], 't' => ['publikasi_tiktok', 'jumlah_postingan', 'tanggal_posting'], 'w' => ['publikasi_wa', 'jumlah_testimoni_terkirim', 'tanggal_blast']] as $alias => [$table, $metric, $date]) {
      $sql = "EXISTS(SELECT 1 FROM $table $alias WHERE $alias.student_id=s.id AND $alias.$metric>0";
      if ($alias === 'i') $sql .= " AND i.status_publikasi='SUDAH'";
      if ($request?->filled('from')) {
        $sql .= " AND $alias.$date >= ?";
        $bindings[] = $request->input('from');
      }
      if ($request?->filled('to')) {
        $sql .= " AND $alias.$date <= ?";
        $bindings[] = $request->input('to');
      }
      $conditions[] = $sql . ')';
    }
    return ['sql' => 'COUNT(DISTINCT CASE WHEN ' . implode(' OR ', $conditions) . ' THEN s.id END) dipublikasi', 'bindings' => $bindings];
  }
  public function row(object $r, int $i): array
  {
    return array_merge([$i], array_map(fn($f) => in_array($f, ['kota', 'nama_unit']) ? $r->$f : (int)$r->$f, self::FIELDS));
  }
}
