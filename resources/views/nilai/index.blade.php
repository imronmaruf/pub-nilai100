@extends('layouts.app')
@section('title', 'Nilai 100')
@section('content')
    <div class="toolbar">
        <h1 class="text-lg font-semibold">Nilai 100</h1>
        <div class="flex gap-2"><a class="btn-secondary" href="{{ route('nilai.import') }}"><i
                    class="fa-solid fa-file-import"></i> Import Excel</a><a class="btn"
                href="{{ route('nilai.create') }}">Input nilai</a></div>
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
                        <td>{{ $n->tanggal_ujian->format('d/m/Y') }}</td>
                        <td>{{ $n->tanggal_validasi_pt->format('d/m/Y') }}</td>
                        <td>{{ $n->status_testimoni }}</td>
                        <td class="space-x-3 whitespace-nowrap"><a class="link"
                                href="{{ route('nilai.edit', $n->student_id) }}">Edit</a><x-delete :action="route('nilai.destroy', $n->student_id)" />
                        </td>
                </tr>@empty<tr>
                        <td colspan="9">Belum ada nilai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span
            class="muted">{{ number_format($rows->total(), 0, ',', '.') }}
            data</span>
        <form method="GET" class="flex items-center gap-2"><label class="text-sm" for="per_page">Tampilkan</label><select
                id="per_page" name="per_page" class="!w-auto" onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select></form>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
@endsection
