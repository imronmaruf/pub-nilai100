@extends('layouts.app')
@section('title', 'Akun')
@section('content')
    <div class="toolbar">
        <h1 class="text-lg font-semibold">Akun</h1><a class="btn" href="{{ route('users.create') }}">Tambah akun</a>
    </div>
    <div class="table-wrap">
        <table class="w-full">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->getRoleNames()->join(', ') }}</td>
                        <td>{{ $u->unit?->nama_unit ?? 'Semua unit' }}</td>
                        <td class="space-x-3"><a class="link" href="{{ route('users.edit', $u->id) }}">Edit</a>
                            @if ($u->id !== auth()->id())
                                <x-delete :action="route('users.destroy', $u->id)" />
                            @endif
                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada akun.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $rows->links() }}</div>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Unit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->getRoleNames()->join(', ') }}</td>
                        <td>{{ $u->unit?->nama_unit ?? 'Semua unit' }}</td>
                        <td class="space-x-3"><a class="link" href="{{ route('users.edit', $u->id) }}">Edit</a>
                            @if ($u->id !== auth()->id())
                                <x-delete :action="route('users.destroy', $u->id)" />
                            @endif
                        </td>
                </tr>@empty<tr>
                        <td colspan="5">Belum ada akun.</td>
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
