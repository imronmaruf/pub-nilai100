@extends('layouts.app')
@section('title', 'All Data')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Pusat data</p>
            <h1>All Data</h1>
            <p class="muted">Satu baris per siswa, dengan seluruh Nilai 100 dan publikasi terakumulasi.</p>
        </div><a class="btn" href="{{ route('all-data.export', request()->query()) }}"><i
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
            @foreach ([['asal_sekolah', 'Asal sekolah', $schools], ['kelas_di_go', 'Kelas di GO', $goClasses], ['tingkat_kelas', 'Tingkat kelas', $grades]] as [$name, $label, $values])
                <div class="field"><label class="label" for="{{ $name }}">{{ $label }}</label><select
                        id="{{ $name }}" name="{{ $name }}[]" multiple data-filter-select>
                        @foreach ($values as $value)
                            <option value="{{ $value }}" @selected(in_array($value, (array) request($name, []), true))>{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
            <div class="field"><label class="label" for="level">Level</label><select id="level" name="level[]"
                    multiple data-filter-select>
                    @foreach (['SD', 'SMP', 'SMA'] as $value)
                        <option value="{{ $value }}" @selected(in_array($value, (array) request('level', []), true))>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field"><label class="label" for="status_testimoni">Status testimoni</label><select
                    id="status_testimoni" name="status_testimoni[]" multiple data-filter-select>
                    @foreach (['SUDAH', 'BELUM'] as $value)
                        <option value="{{ $value }}" @selected(in_array($value, (array) request('status_testimoni', []), true))>{{ $value }}</option>
                    @endforeach
                </select></div>
        </div>
        <div class="filter-actions"><button class="btn"><i class="fa-solid fa-filter"></i> Terapkan</button><a
                class="btn-secondary" href="{{ route('all-data.index') }}">Reset</a></div>
    </form>
    <div class="stats-grid">
        <div class="stat-card"><span>Siswa tampil</span><strong>{{ number_format($rows->total(), 0, ',', '.') }}</strong>
        </div>
        <div class="stat-card"><span>Rentang data</span><strong>{{ request('from') ?: 'Semua' }} <small>hingga</small>
                {{ request('to') ?: 'hari ini' }}</strong></div>
    </div>
    <div class="table-wrap">
        <table class="data-table w-full" data-table>
            <thead>
                <tr>
                    <th>Noreg</th>
                    <th>Unit</th>
                    <th>Nama siswa</th>
                    <th>Sekolah</th>
                    <th>Kelas</th>
                    <th>Jumlah Nilai 100</th>
                    <th>IG Post</th>
                    <th>IG View</th>
                    <th>IG Like</th>
                    <th>IG Komen</th>
                    <th>TikTok Post</th>
                    <th>TikTok View</th>
                    <th>TikTok Like</th>
                    <th>TikTok Komen</th>
                    <th>WA Kirim</th>
                    <th>WA Terkirim</th>
                    <th>WA Dibaca</th>
                    <th>WA Respon</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $s)
                    <tr>
                        <td>{{ $s->noreg }}</td>
                        <td>{{ $s->unit->nama_unit }}</td>
                        <td>{{ $s->nama_siswa }}</td>
                        <td>{{ $s->asal_sekolah }}</td>
                        <td>{{ $s->kelas_di_go }}</td>
                        @foreach (['nilai100_count', 'instagram_sum_jumlah_postingan', 'instagram_sum_view', 'instagram_sum_like', 'instagram_sum_komen', 'tiktok_sum_jumlah_postingan', 'tiktok_sum_view', 'tiktok_sum_like', 'tiktok_sum_komen', 'whatsapp_sum_jumlah_testimoni_terkirim', 'whatsapp_sum_terkirim', 'whatsapp_sum_dibaca', 'whatsapp_sum_respon'] as $field)
                            <td class="text-right">{{ number_format($s->$field ?? 0, 0, ',', '.') }}</td>
                        @endforeach
                    </tr>
                @empty<tr>
                        <td colspan="18">Tidak ada data pada filter ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="5">TOTAL</td>
                    <td>{{ number_format($totals['nilai100'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['ig_post'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['ig_view'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['ig_like'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['ig_komen'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['tt_post'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['tt_view'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['tt_like'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['tt_komen'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['wa_kirim'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['wa_terkirim'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['wa_dibaca'], 0, ',', '.') }}</td>
                    <td>{{ number_format($totals['wa_respon'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex items-center justify-between"><span
            class="muted">{{ number_format($rows->total(), 0, ',', '.') }} siswa</span>
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
