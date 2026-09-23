@extends('layouts.app')
@section('title', 'Unit')
@section('content')
    <h1 class="mb-5 text-lg font-semibold">
        Unit</h1>
    <form method="POST" action="{{ $record->exists ? route('units.update', $record->id) : route('units.store') }}"
        class="mb-6 flex max-w-3xl flex-wrap items-end gap-3">@csrf @if ($record->exists)
            @method('PUT')
        @endif
        <x-field name="kota" label="Kota" :value="$record->kota" maxlength="255" required /><x-field name="nama_unit"
            label="Nama unit" :value="$record->nama_unit" maxlength="255" required /><button
            class="btn">{{ $record->exists ? 'Simpan' : 'Tambah' }}</button>
        @if ($record->exists)
            <a class="btn-secondary" href="{{ route('units.index') }}">Batal</a>
        @endif
    </form>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kota</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $u)
                    <tr>
                        <td>{{ $u->id }}</td>
                        <td>{{ $u->kota }}</td>
                        <td>{{ $u->nama_unit }}</td>
                        <td class="space-x-3"><a class="link" href="{{ route('units.edit', $u->id) }}">Edit</a><x-delete
                                :action="route('units.destroy', $u->id)" /></td>
                </tr>@empty<tr>
                        <td colspan="4">Belum ada unit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span class="muted">{{ number_format($rows->total(), 0, ',', '.') }}
            data</span>
        <form method="GET"><select name="per_page" class="!w-auto" onchange="this.form.submit()">
                <option value="10" @selected(request('per_page', 10) == 10)>10</option>
                <option value="20" @selected(request('per_page') == 20)>20</option>
                <option value="30" @selected(request('per_page') == 30)>30</option>
            </select></form>
    </div>
    <div class="pagination">{{ $rows->links() }}</div>
@endsection
