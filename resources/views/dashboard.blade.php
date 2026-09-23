@extends('layouts.app')
@section('title', 'Resume')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Ringkasan performa</p>
            <h1>Resume Nilai 100</h1>
            <p class="muted">Pantau capaian nilai dan publikasi berdasarkan periode yang kamu pilih.</p>
        </div><a class="btn" href="{{ route('export', request()->query()) }}"><i class="fa-solid fa-file-arrow-down"></i>
            Export Excel</a>
    </div>
    <form class="filter-panel" method="GET">
        <div class="filter-grid">
            <div class="field"><label class="label" for="from">Tanggal mulai</label><input id="from"
                    type="date" name="from" value="{{ request('from') }}"></div>
            <div class="field"><label class="label" for="to">Tanggal akhir</label><input id="to"
                    type="date" name="to" value="{{ request('to') }}"></div>
            <div class="field"><label class="label" for="kota">Kota</label><select id="kota" name="kota">
                    <option value="">Semua kota</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(request('kota') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="unit_id">Unit</label><select id="unit_id" name="unit_id">
                    <option value="">Semua unit</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan filter</button><a
                class="btn-secondary" href="{{ route('dashboard') }}">Reset</a></div>
    </form>
    <div class="stats-grid">
        <div class="stat-card"><span>Total unit</span><strong>{{ number_format($totals['units'], 0, ',', '.') }}</strong>
        </div>
        <div class="stat-card"><span>Jumlah siswa Nilai
                100</span><strong>{{ number_format($totals['nilai_100_students'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Sudah
                testimoni</span><strong>{{ number_format($totals['testimoni'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Belum
                testimoni</span><strong>{{ number_format($totals['belum_testimoni'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Sudah
                publikasi</span><strong>{{ number_format($totals['dipublikasi'], 0, ',', '.') }}</strong></div>
    </div>
    <div class="table-wrap">
        <table class="data-table resume-table w-full" data-table>
            <thead>
                <tr>
                    <th class="group-base" colspan="9">DATA NILAI 100</th>
                    <th class="group-ig" colspan="4">MEDIA PUBLIKASI IG</th>
                    <th class="group-tt" colspan="4">MEDIA PUBLIKASI TIKTOK</th>
                    <th class="group-wa" colspan="4">MEDIA PUBLIKASI WA</th>
                </tr>
                <tr>
                    @foreach ($headings as $h)
                        <th class="metric-head">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr>
                        <td>{{ $rows->firstItem() + $loop->index }}</td>
                        @foreach ($fields as $field)
                            <td
                                class="{{ in_array($field, ['kota', 'nama_unit']) ? 'whitespace-nowrap' : 'text-center' }}">
                                @if ($field === 'nilai_100_students')
                                    <a class="resume-number"
                                        href="{{ route('nilai.index', array_filter(['unit_id' => $row->id, 'from' => request('from'), 'to' => request('to')])) }}">{{ number_format($row->$field, 0, ',', '.') }}</a>
                                @elseif ($field === 'testimoni')
                                    <a class="resume-number"
                                        href="{{ route('nilai.index', array_filter(['unit_id' => $row->id, 'status_testimoni' => 'SUDAH', 'from' => request('from'), 'to' => request('to')])) }}">{{ number_format($row->$field, 0, ',', '.') }}</a>
                                @elseif ($field === 'belum_testimoni')
                                    <a class="resume-number"
                                        href="{{ route('nilai.index', array_filter(['unit_id' => $row->id, 'status_testimoni' => 'BELUM', 'from' => request('from'), 'to' => request('to')])) }}">{{ number_format($row->$field, 0, ',', '.') }}</a>
                                @elseif (in_array($field, ['kota', 'nama_unit']))
                                    {{ $row->$field }}
                                @else
                                    {{ number_format($row->$field, 0, ',', '.') }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty<tr>
                        <td colspan="21">Belum ada data pada filter ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="3">TOTAL</td>
                    <td>{{ number_format($totals['jumsis'], 0, ',', '.') }}</td>
                    @foreach (array_slice($fields, 3) as $field)
                        <td>{{ in_array($field, ['kota', 'nama_unit']) ? '' : number_format($totals[$field] ?? 0, 0, ',', '.') }}
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span
            class="muted">{{ number_format($rows->total(), 0, ',', '.') }} unit</span>
        <form method="GET" class="flex items-center gap-2">
            @foreach (request()->except('per_page', 'page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <label class="text-sm" for="per_page">Tampilkan</label><select id="per_page" name="per_page" class="!w-auto"
                onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select>
        </form>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
@endsection
