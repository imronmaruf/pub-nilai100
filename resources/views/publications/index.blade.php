@extends('layouts.app')
@section('title', 'Publikasi')
@section('content')
    <div class="toolbar">
        <h1 class="text-lg font-semibold">Publikasi</h1>
        <div class="flex gap-2"><a class="btn-secondary"
                href="{{ route('publications.export', ['channel' => $channel] + request()->query()) }}"><i
                    class="fa-solid fa-file-arrow-down"></i> Export Excel</a><a class="btn-secondary"
                href="{{ route('publications.import', $channel) }}"><i class="fa-solid fa-file-import"></i> Import Excel</a><a
                class="btn" href="{{ route('publications.create', $channel) }}">Tambah publikasi</a></div>
    </div>
    @include('partials.publication-tabs')
    <form class="filter-panel" method="GET">
        <div class="filter-grid">
            <div class="field"><label class="label" for="q">Cari siswa</label><input id="q" name="q"
                    value="{{ request('q') }}" placeholder="Noreg atau nama" maxlength="100"></div>
            <div class="field"><label class="label" for="kota">Kota</label><select id="kota" name="kota[]"
                    multiple data-filter-select>
                    @foreach ($cities as $city)
                        <option value="{{ $city }}" @selected(in_array($city, (array) request('kota', []), true))>{{ $city }}</option>
                    @endforeach
                </select></div>
            <div class="field"><label class="label" for="unit_id">Unit</label><select id="unit_id" name="unit_id[]"
                    multiple data-filter-select>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}" @selected(in_array($unit->id, array_map('intval', (array) request('unit_id', [])), true))>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select></div>
            @if ($channel === 'ig')
                <div class="field"><label class="label" for="status_publikasi">Status</label><select id="status_publikasi"
                        name="status_publikasi">
                        <option value="">Semua</option>
                        @foreach (['SUDAH', 'BELUM'] as $status)
                            <option value="{{ $status }}" @selected(request('status_publikasi') === $status)>{{ $status }}</option>
                        @endforeach
                    </select></div>
            @endif
            <div class="field"><label class="label" for="from">Tanggal mulai</label><input id="from"
                    type="date" name="from" value="{{ request('from') }}"></div>
            <div class="field"><label class="label" for="to">Tanggal akhir</label><input id="to"
                    type="date" name="to" value="{{ request('to') }}"></div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan</button><a
                class="btn-secondary" href="{{ route('publications.index', $channel) }}">Reset</a></div>
    </form>
    <div class="table-wrap">
        <table class="data-table w-full tabular-nums" data-table>
            <thead>
                <tr>
                    <th>Noreg</th>
                    <th>Nama</th>
                    <th>Unit</th>
                    <th>Mapel Nilai 100</th>
                    @if ($channel === 'wa')
                        <th>Tanggal blast</th>
                        <th>Testimoni kirim</th>
                        <th>Terkirim</th>
                        <th>Dibaca</th>
                    <th>Respon</th>@else<th>Status</th>
                        <th>Tanggal</th>
                        <th>Post</th>
                        <th>View</th>
                        <th>Like</th>
                        <th>Komen</th>
                        <th>Link</th>
                    @endif
                    <th>
                        Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $p)
                    <tr>
                        <td>{{ $p->student->noreg }}</td>
                        <td>{{ $p->student->nama_siswa }}</td>
                        <td>{{ $p->student->unit->nama_unit }}</td>
                        <td>{{ $p->nilai100?->mapelInfo?->nama ?? ($p->nilai100?->mapel ?? 'Belum ditautkan') }}</td>
                        @if ($channel === 'wa')
                            <td>{{ $p->tanggal_blast->format('d/m/Y') }}</td>
                            @foreach (['jumlah_testimoni_terkirim', 'terkirim', 'dibaca', 'respon'] as $f)
                                <td>{{ number_format($p->$f, 0, ',', '.') }}</td>
                            @endforeach
                        @else<td>{{ $channel === 'ig' ? $p->status_publikasi : 'SUDAH' }}</td>
                            <td>{{ $p->tanggal_posting?->format('d/m/Y') ?? '—' }}</td>
                            @foreach (['jumlah_postingan', 'view', 'like', 'komen'] as $f)
                                <td>{{ number_format($p->$f, 0, ',', '.') }}</td>
                            @endforeach
                            <td>
                                @if ($p->link_postingan)
                                    <a class="link" href="{{ $p->link_postingan }}" target="_blank"
                                        rel="noopener noreferrer">Buka</a>
                                @else
                                    —
                                @endif
                            </td>
                        @endif
                        <td class="space-x-3 whitespace-nowrap"><a class="link"
                                href="{{ route('publications.edit', [$channel, $p->id]) }}">Edit</a><x-delete
                                :action="route('publications.destroy', [$channel, $p->id])" /></td>
                    </tr>
                @empty<tr>
                        <td colspan="{{ $channel === 'wa' ? 10 : 12 }}">Belum ada publikasi.</td>
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
