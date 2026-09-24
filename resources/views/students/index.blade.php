@extends('layouts.app')
@section('title', 'Data Siswa')
@section('content')
    <div class="toolbar">
        <h1 class="text-lg font-semibold">Data Siswa</h1>
        <div class="flex gap-2"><a class="btn-secondary" href="{{ route('students.import') }}">Import Excel</a><a class="btn"
                href="{{ route('students.create') }}">Tambah siswa</a></div>
    </div>
    <form class="filter-panel" method="GET">
        <div class="filter-grid">
            <div class="field"><label class="label" for="q">Cari siswa</label><input id="q" name="q"
                    value="{{ request('q') }}" placeholder="Noreg atau nama" maxlength="100"></div>
            <div class="field"><label class="label" for="kota">Kota</label><select id="kota" name="kota[]" multiple data-filter-select>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(in_array($city, (array) request('kota', []), true))>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="unit_id">Unit</label><select id="unit_id" name="unit_id[]" multiple data-filter-select>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(in_array($unit->id, array_map('intval', (array) request('unit_id', [])), true))>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="asal_sekolah">Asal sekolah</label><select id="asal_sekolah"
                    name="asal_sekolah[]" multiple data-filter-select>
                    @foreach ($schools as $school)
                        <option value="{{ $school }}" @selected(in_array($school, (array) request('asal_sekolah', []), true))>{{ $school }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="kelas_di_go">Kelas di GO</label><select id="kelas_di_go"
                    name="kelas_di_go[]" multiple data-filter-select>
                    @foreach ($goClasses as $class)
                        <option value="{{ $class }}" @selected(in_array($class, (array) request('kelas_di_go', []), true))>{{ $class }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="tingkat_kelas">Tingkat kelas</label><select id="tingkat_kelas"
                    name="tingkat_kelas[]" multiple data-filter-select>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade }}" @selected(in_array($grade, (array) request('tingkat_kelas', []), true))>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="level">Level</label><select id="level" name="level[]" multiple data-filter-select>
                    @foreach (['SD', 'SMP', 'SMA'] as $level)
                        <option value="{{ $level }}" @selected(in_array($level, (array) request('level', []), true))>{{ $level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="nilai_status">Status Nilai 100</label><select id="nilai_status"
                    name="nilai_status[]" multiple data-filter-select>
                    <option value="sudah" @selected(in_array('sudah', (array) request('nilai_status', []), true))>Sudah - memiliki nilai</option>
                    <option value="belum" @selected(in_array('belum', (array) request('nilai_status', []), true))>Belum - belum memiliki nilai</option>
                </select>
            </div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan</button><a
                class="btn-secondary" href="{{ route('students.index') }}">Reset</a></div>
    </form>
    <div class="mb-4 flex items-center justify-between"><span
            class="muted">{{ number_format($students->total(), 0, ',', '.') }} siswa ditemukan</span>
        <form method="GET" class="flex items-center gap-2">
            @foreach (request()->except('per_page', 'page') as $key => $value)
                @foreach ((array) $value as $item)
                    <input type="hidden" name="{{ is_array($value) ? $key . '[]' : $key }}" value="{{ $item }}">
                @endforeach
            @endforeach
            <label class="text-sm" for="per_page">Tampilkan</label><select id="per_page" name="per_page" class="!w-auto"
                onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select>
        </form>
    </div>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>Noreg</th>
                    <th>Nama</th>
                    <th>Asal Sekolah</th>
                    <th>Unit</th>
                    <th>Kelas di GO</th>
                    <th>Tingkat</th>
                    <th>Level</th>
                    <th>Status Nilai 100</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $s)
                    <tr>
                        <td>{{ $s->noreg }}</td>
                        <td>{{ $s->nama_siswa }}</td>
                        <td>{{ $s->asal_sekolah }}</td>
                        <td>{{ $s->unit->nama_unit }}</td>
                        <td>{{ $s->kelas_di_go }}</td>
                        <td>{{ $s->tingkat_kelas }}</td>
                        <td>{{ $s->level }}</td>
                        <td><span
                                class="status-badge {{ $s->nilai100_count > 0 ? 'status-done' : 'status-pending' }}">{{ $s->nilai100_count > 0 ? number_format($s->nilai100_count, 0, ',', '.') : 'BELUM' }}</span>
                        </td>
                        <td class="space-x-3 whitespace-nowrap"><a class="link"
                                href="{{ route('students.edit', $s->id) }}">Edit</a><x-delete :action="route('students.destroy', $s->id)"
                                message="Hapus siswa beserta seluruh nilai dan publikasinya?" /></td>
                </tr>@empty<tr>
                        <td colspan="9">Tidak ada siswa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $students->links() }}</div>
@endsection
