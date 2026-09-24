@extends('layouts.app')
@section('title', 'Nilai 100')
@section('content')
    <div class="toolbar">
        <h1 class="text-lg font-semibold">Nilai 100</h1>
        <div class="flex gap-2"><a class="btn-secondary" href="{{ route('nilai.import') }}"><i
                    class="fa-solid fa-file-import"></i> Import Excel</a><a class="btn"
                href="{{ route('nilai.create') }}">Input nilai</a></div>
    </div>
    <form class="filter-panel" method="GET">
        <div class="filter-grid">
            <div class="field"><label class="label">Kota</label><select name="kota[]" multiple data-filter-select>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(in_array($city, (array) request('kota', []), true))>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label">Unit</label><select name="unit_id[]" multiple data-filter-select>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(in_array($unit->id, array_map('intval', (array) request('unit_id', [])), true))>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label">Mapel</label><select name="mapel[]" multiple data-filter-select>
                    @foreach ($mapels as $mapel)
                        <option value="{{ $mapel->kode }}" @selected(in_array($mapel->kode, (array) request('mapel', []), true))>{{ $mapel->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label">Jenis Nilai</label><select name="jenis_nilai[]" multiple
                    data-filter-select>
                    @foreach (['UH', 'PTS', 'PAS', 'PAT', 'US'] as $jenis)
                        <option value="{{ $jenis }}" @selected(in_array($jenis, (array) request('jenis_nilai', []), true))>{{ $jenis }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label">Status Testimoni</label><select name="status_testimoni[]" multiple
                    data-filter-select>
                    @foreach (['SUDAH', 'BELUM'] as $status)
                        <option value="{{ $status }}" @selected(in_array($status, (array) request('status_testimoni', []), true))>{{ $status }}</option>
                    @endforeach
                </select></div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan</button><a
                class="btn-secondary" href="{{ route('nilai.index') }}">Reset</a></div>
    </form>
    <div class="stats-grid">
        <div class="stat-card"><span>Siswa Nilai
                100</span><strong>{{ number_format($summary->unique_students ?? 0, 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Jumlah Nilai
                100</span><strong>{{ number_format($summary->total_values ?? 0, 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Sudah
                Testimoni</span><strong>{{ number_format($summary->testified ?? 0, 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Belum
                Testimoni</span><strong>{{ number_format($summary->pending ?? 0, 0, ',', '.') }}</strong></div>
    </div>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>Noreg</th>
                    <th>Nama</th>
                    <th>Unit</th>
                    <th>Mapel</th>
                    <th>Jenis</th>
                    <th>Ujian</th>
                    <th>Validasi PT</th>
                    <th>Testimoni</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $n)
                    <tr>
                        <td>{{ $n->student->noreg }}</td>
                        <td>{{ $n->student->nama_siswa }}</td>
                        <td>{{ $n->student->unit->nama_unit }}</td>
                        <td>{{ $n->mapel }}</td>
                        <td>{{ $n->jenis_nilai }}</td>
                        <td>{{ $n->tanggal_ujian?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $n->tanggal_validasi_pt?->format('d/m/Y') ?? '-' }}</td>
                        <td>{{ $n->status_testimoni }}</td>
                        <td>{{ $n->keterangan ?: '-' }}</td>
                        <td class="space-x-3 whitespace-nowrap"><a class="link"
                                href="{{ route('nilai.edit', $n->student_id) }}">Edit</a><x-delete :action="route('nilai.destroy', $n->student_id)" />
                        </td>
                </tr>@empty<tr>
                        <td colspan="10">Belum ada nilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span
            class="muted">{{ number_format($rows->total(), 0, ',', '.') }}
            data</span>
        <form method="GET" class="flex items-center gap-2">
            @foreach (request()->except('per_page', 'page') as $key => $value)
                @foreach ((array) $value as $item)
                    <input type="hidden" name="{{ is_array($value) ? $key . '[]' : $key }}" value="{{ $item }}">
                @endforeach
            @endforeach
            <label class="text-sm" for="per_page">Tampilkan</label><select
                id="per_page" name="per_page" class="!w-auto" onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select></form>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
@endsection
