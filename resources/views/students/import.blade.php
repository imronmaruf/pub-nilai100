@extends('layouts.app')
@section('title', 'Import Siswa')
@section('content')
    <div class="page-heading">
        <div>
            <p class="eyebrow">Data siswa</p>
            <h1>Import Siswa</h1>
        </div><a class="btn-secondary" href="{{ route('students.import.template') }}"><i class="fa-solid fa-download"></i>
            Download template</a>
    </div>
    <form method="POST" action="{{ route('students.import.store') }}" enctype="multipart/form-data"
        class="max-w-2xl space-y-4">@csrf
        <p class="text-sm">File .xlsx, maksimal 5 MB dan 5.000 baris. Sheet 1 digunakan untuk data siswa, sheet 2 berisi
            panduan pengisian, dan sheet 3 berisi referensi ID unit.</p>
        <p class="text-sm">Kolom sheet 1: <strong>Noreg, Nama, Asal Sekolah, Tingkat Kelas, Kelas di GO, Level,
                Unit</strong>. Format Noreg sebagai Text agar nol di depan tetap tersimpan.</p>
        <div class="field"><label class="label" for="file">File Excel</label><input type="file" id="file"
                name="file" accept=".xlsx" required></div>
        <button class="btn">Import</button><a class="btn-secondary" href="{{ route('students.index') }}">Batal</a>
        <div class="table-wrap">
            <table class="data-table w-full" data-table>
                <thead>
                    <tr>
                        <th>ID Unit</th>
                        <th>Kota</th>
                        <th>Unit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($units as $u)
                        <tr>
                            <td>{{ $u->id }}</td>
                            <td>{{ $u->kota }}</td>
                            <td>{{ $u->nama_unit }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>
@endsection
