@extends('layouts.app')
@section('title', 'Pertambahan per periode')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Resume</p>
            <h1>Pertambahan per periode</h1>
            <p class="muted">Pertambahan nilai 100 setiap hari dihitung berdasarkan tanggal validasi PT.</p>
        </div><a class="btn" href="{{ route('dashboard.pertambahan.export', request()->query()) }}"><i
                class="fa-solid fa-file-arrow-down"></i> Export Excel</a>
    </div>
    <form class="filter-panel" method="GET">
        <div class="filter-grid">
            <div class="field"><label class="label" for="from">Tanggal mulai</label><input id="from"
                    type="date" name="from" value="{{ request('from') }}"></div>
            <div class="field"><label class="label" for="to">Tanggal akhir</label><input id="to"
                    type="date" name="to" value="{{ request('to') }}"></div>
            <div class="field"><label class="label" for="kota">Kota</label><select id="kota" name="kota[]"
                    multiple data-filter-select>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(in_array($city, (array) request('kota', []), true))>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="unit_id">Unit</label><select id="unit_id" name="unit_id[]"
                    multiple data-filter-select>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(in_array($unit->id, array_map('intval', (array) request('unit_id', [])), true))>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan filter</button><a
                class="btn-secondary" href="{{ route('dashboard.pertambahan') }}">Reset</a></div>
    </form>
    <div class="stats-grid">
        <div class="stat-card"><span>Jumlah nilai
                100</span><strong>{{ number_format($cards['total'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Jumlah siswa nilai
                100</span><strong>{{ number_format($cards['siswa'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Sudah
                testimoni</span><strong>{{ number_format($cards['sudah'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Belum
                testimoni</span><strong>{{ number_format($cards['belum'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Belum ada tanggal validasi
                PT</span><strong>{{ number_format($nullCount, 0, ',', '.') }}</strong>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal validasi PT</th>
                    <th>Pertambahan nilai 100</th>
                    <th>Jumlah siswa</th>
                    <th>Sudah testimoni</th>
                    <th>Belum testimoni</th>
                </tr>
            </thead>
            <tbody>
                @forelse($days as $i => $day)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td class="whitespace-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($day->day)->translatedFormat('d M Y') }}</td>
                        <td class="text-center"><strong>{{ number_format($day->total, 0, ',', '.') }}</strong></td>
                        <td class="text-center">{{ number_format($day->siswa, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($day->sudah, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($day->belum, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Belum ada data pada filter ini.</td>
                    </tr>
                @endforelse
                @if ($days->isNotEmpty())
                    <tr class="total-row">
                        <td colspan="2">TOTAL</td>
                        <td class="text-center">{{ number_format($days->sum('total'), 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($cards['siswa'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($days->sum('sudah'), 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($days->sum('belum'), 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
