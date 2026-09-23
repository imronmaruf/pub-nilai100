@extends('layouts.app')
@section('title', 'Mapel')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Administrasi</p>
            <h1>Mapel Nilai 100</h1>
            <p class="muted">Kelola pilihan mata pelajaran yang tersedia untuk Nilai 100.</p>
        </div>
    </div>
    <form method="POST" action="{{ $record->exists ? route('mapels.update', $record->id) : route('mapels.store') }}"
        class="filter-panel max-w-3xl">@csrf @if ($record->exists)
            @method('PUT')
        @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <x-field name="kode" label="Kode mapel" :value="$record->kode" maxlength="20" required /><x-field name="nama"
                label="Nama mapel" :value="$record->nama" maxlength="100" required />
        </div>
        <div class="mt-4 flex gap-2"><button class="btn">{{ $record->exists ? 'Simpan' : 'Tambah' }}</button>
            @if ($record->exists)
                <a class="btn-secondary" href="{{ route('mapels.index') }}">Batal</a>
            @endif
        </div>
    </form>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $mapel)
                    <tr>
                        <td>{{ $mapel->id }}</td>
                        <td>{{ $mapel->kode }}</td>
                        <td>{{ $mapel->nama }}</td>
                        <td class="space-x-3 whitespace-nowrap"><a class="link"
                                href="{{ route('mapels.edit', $mapel->id) }}">Edit</a><x-delete :action="route('mapels.destroy', $mapel->id)" /></td>
                </tr>@empty<tr>
                        <td colspan="4">Belum ada mapel.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span class="muted">{{ number_format($rows->total(), 0, ',', '.') }}
            mapel</span>
        <form method="GET"><select name="per_page" class="!w-auto" onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select></form>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
@endsection
