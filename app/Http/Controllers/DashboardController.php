<?php

namespace App\Http\Controllers;

use App\Services\ResumeService;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
}
