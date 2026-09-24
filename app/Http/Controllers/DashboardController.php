<?php

namespace App\Http\Controllers;

use App\Exports\PertambahanSheet;
use App\Services\ResumeService;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
  public function index(Request $r, ResumeService $resume)
  {
    $units = app(\App\Services\Access::class)->units($r->user())->orderBy('nama_unit')->get();
    $perPage = in_array($r->integer('per_page'), [10, 20, 30], true) ? $r->integer('per_page') : 10;
    $query = $resume->query($r->user(), $r);
    $all = (clone $query)->get();
    $totals = collect(ResumeService::FIELDS)->mapWithKeys(fn($field) => [$field => in_array($field, ['kota', 'nama_unit']) ? null : $all->sum($field)])->all();
    $totals['units'] = $all->count();
    $rows = $query->paginate($perPage)->withQueryString();
    return view('dashboard', ['rows' => $rows, 'totals' => $totals, 'headings' => ResumeService::HEADINGS, 'fields' => ResumeService::FIELDS, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values()]);
  }
  public function export(Request $r)
  {
    return \Illuminate\Support\Facades\DB::transaction(fn() => Excel::download(new ReportExport($r->user()), 'Nilai100_' . now()->format('Ymd_His') . '.xlsx'));
  }
  // Sub menu "Pertambahan per periode": pertambahan nilai 100 harian dihitung dari tanggal_validasi_pt.
  public function pertambahan(Request $r)
  {
    $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'kota' => 'nullable|array', 'kota.*' => 'string|max:255', 'unit_id' => 'nullable|array', 'unit_id.*' => 'integer']);
    $data = $this->pertambahanData($r);
    return view('dashboard.pertambahan', ['days' => $data['days'], 'cards' => $data['cards'], 'nullCount' => $data['nullCount'], 'units' => $data['units'], 'cities' => $data['cities']]);
  }
  public function pertambahanExport(Request $r)
  {
    $r->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'kota' => 'nullable|array', 'kota.*' => 'string|max:255', 'unit_id' => 'nullable|array', 'unit_id.*' => 'integer']);
    $data = $this->pertambahanData($r);
    $sheet = new PertambahanSheet($data['dates'], $data['unitRows']);
    return DB::transaction(fn() => Excel::download($sheet, 'Pertambahan_Nilai100_' . now()->format('Ymd_His') . '.xlsx'));
  }
  private function pertambahanData(Request $r): array
  {
    $from = $r->filled('from') ? $r->input('from') : null;
    $to = $r->filled('to') ? $r->input('to') : null;
    $kota = array_values(array_filter((array) $r->input('kota', []), fn($v) => $v !== null && $v !== ''));
    $unitIdsInput = array_values(array_filter((array) $r->input('unit_id', []), fn($v) => $v !== null && $v !== ''));
    $units = app(\App\Services\Access::class)->units($r->user())
      ->when($kota, fn($q) => $q->whereIn('kota', $kota))
      ->when($unitIdsInput, fn($q) => $q->whereIn('id', $unitIdsInput))
      ->orderBy('kota')->orderBy('nama_unit')->get();
    $ids = $units->pluck('id');
    $base = DB::table('nilai_100 as n')->join('students as s', 's.id', '=', 'n.student_id')->whereIn('s.unit_id', $ids);
    $daily = (clone $base)->whereNotNull('n.tanggal_validasi_pt')
      ->when($from, fn($q) => $q->whereDate('n.tanggal_validasi_pt', '>=', $from))
      ->when($to, fn($q) => $q->whereDate('n.tanggal_validasi_pt', '<=', $to))
      ->groupBy('day')
      ->selectRaw("DATE(n.tanggal_validasi_pt) as day, COUNT(*) total, COUNT(DISTINCT n.student_id) siswa, SUM(CASE WHEN n.status_testimoni = 'SUDAH' THEN 1 ELSE 0 END) sudah, SUM(CASE WHEN n.status_testimoni = 'BELUM' THEN 1 ELSE 0 END) belum")
      ->orderBy('day', 'desc')->get()->keyBy('day');
    $makeRow = fn($row, $day) => (object) ['day' => $day, 'total' => (int) ($row->total ?? 0), 'siswa' => (int) ($row->siswa ?? 0), 'sudah' => (int) ($row->sudah ?? 0), 'belum' => (int) ($row->belum ?? 0)];
    if ($from && $to) {
      $days = collect(CarbonPeriod::create($from, $to))->map(fn($d) => $makeRow($daily->get($d->toDateString()), $d))->sortByDesc('day')->values();
    } else {
      $days = $daily->map(fn($row) => $makeRow($row, Carbon::parse($row->day)))->values();
    }
    $inPeriod = fn($q) => $q->whereNotNull('n.tanggal_validasi_pt')
      ->when($from, fn($q2) => $q2->whereDate('n.tanggal_validasi_pt', '>=', $from))
      ->when($to, fn($q2) => $q2->whereDate('n.tanggal_validasi_pt', '<=', $to));
    $cards = [
      'total' => $inPeriod((clone $base))->count(),
      'siswa' => $inPeriod((clone $base))->distinct()->count('n.student_id'),
      'sudah' => $inPeriod((clone $base))->where('n.status_testimoni', 'SUDAH')->count(),
      'belum' => $inPeriod((clone $base))->where('n.status_testimoni', 'BELUM')->count(),
    ];
    $nullCount = (clone $base)->whereNull('n.tanggal_validasi_pt')->count();
    $perUnit = (clone $base)->whereNotNull('n.tanggal_validasi_pt')
      ->when($from, fn($q) => $q->whereDate('n.tanggal_validasi_pt', '>=', $from))
      ->when($to, fn($q) => $q->whereDate('n.tanggal_validasi_pt', '<=', $to))
      ->groupBy('s.unit_id', 'day')
      ->selectRaw('s.unit_id, DATE(n.tanggal_validasi_pt) as day, COUNT(*) total')->get()->groupBy('s.unit_id');
    $dates = ($from && $to)
      ? collect(CarbonPeriod::create($from, $to))->values()
      : $daily->keys()->sortDesc()->values()->map(fn($d) => Carbon::parse($d));
    $unitRows = $units->map(function ($u) use ($perUnit, $dates) {
      $byDay = $perUnit->get($u->id, collect())->keyBy(fn($row) => Carbon::parse($row->day)->toDateString());
      $counts = $dates->map(fn($d) => (int) ($byDay->get($d->toDateString())->total ?? 0))->all();
      return ['kota' => $u->kota, 'unit' => $u->nama_unit, 'counts' => $counts, 'total' => array_sum($counts)];
    })->values()->all();
    return ['days' => $days, 'cards' => $cards, 'nullCount' => $nullCount, 'units' => $units, 'cities' => $units->pluck('kota')->unique()->sort()->values(), 'dates' => $dates, 'unitRows' => $unitRows];
  }
}
