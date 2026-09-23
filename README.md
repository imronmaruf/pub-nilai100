# Pendataan Nilai 100 & Publikasi Sosmed

Laravel 11 / PHP 8.2+, MySQL 8 atau MariaDB modern, Spatie Permission 6, Laravel Excel 3.1, Blade + Tailwind 3 + Vite. UI langsung berupa tabel dan form; tanpa widget, grafik, atau data siswa dummy.

## Status paket

Paket source aplikasi lengkap dengan entrypoint Laravel, konfigurasi, migration, model, request, service, controller, Blade, aset, dan feature tests. **Bukan instalasi vendor siap jalan:** PHP/Composer tidak tersedia di lingkungan pembuatan, sehingga dependency PHP belum terpasang dan test Laravel/database belum dijalankan. Ikuti `DEPLOY_AAPANEL.md` dan jalankan test di staging sebelum production. Lihat `VERIFICATION.md` untuk hasil pemeriksaan yang benar-benar dijalankan.

`composer.lock` belum dapat dihasilkan; Composer akan menyelesaikan versi pertama kali di mesin build/staging. Simpan lock yang dihasilkan, lalu gunakan `composer install` dari lock itu untuk production. Jangan menonaktifkan audit keamanan untuk memaksa instalasi. Versi framework mengikuti permintaan Laravel 11; periksa status dukungan dan advisori sebelum dipakai di server publik.

## Urutan implementasi

1. `database/migrations`: units → users → students → nilai_100 → publikasi IG/TikTok/WA → tabel Spatie. `app/Models`: seluruh relasi belongsTo, hasMany dan hasOne.
2. `app/Services/Access.php`: satu pintu query berdasarkan actor. `ActiveAccount` menolak akun tanpa role/unit. Semua controller, lookup, import dan export memakai akses ini. Query CLI menggunakan actor eksplisit, bukan global auth tersembunyi.
3. `app/Http/Requests`: validasi input dan eligibility; controller mengunci baris siswa dalam transaksi untuk mencegah perubahan testimoni dan publikasi saling mendahului.
4. `StudentsWorkbook` / `StudentsImport`: import file pertama, validasi header/role/unit/duplikat, tanpa overwrite; rollback satu file penuh jika gagal.
5. `ResumeService`: agregasi per kanal dahulu, baru join hasil per unit. Tidak ada perkalian baris hasMany.
6. `app/Exports`: WithMultipleSheets, sheet Resume, All Data, lalu per unit.
7. `resources/views` dan `resources/js`: tabel minimalis, pencarian AJAX, readonly autofill, form publikasi terkunci, feedback validasi.
8. `DEPLOY_AAPANEL.md` dan `deploy/nginx.conf.example`.

## Aturan data

- `students.noreg` unik global, disimpan string. Noreg boleh huruf/angka/titik/underscore/tanda minus. Indeks unique sudah merupakan indeks pencarian.
- Satu siswa mempunyai **satu** baris nilai_100, sesuai unique student_id yang diminta. Input nilai kedua ditolak; gunakan Edit. Riwayat multi-ujian belum menjadi model data aplikasi ini.
- Satu siswa boleh mempunyai beberapa catatan per kanal publikasi. Edit catatan untuk memperbarui statistik; jangan menambahkan snapshot statistik sebagai catatan baru karena resume menjumlahkan semua catatan.
- Publikasi hanya boleh dibuat/diedit jika Nilai 100 ada dan testimoni SUDAH. Aturan berlaku untuk IG BELUM sekalipun. Testimoni tidak bisa diturunkan dan nilai tidak bisa dihapus selama ada publikasi. Hapus catatan publikasi terlebih dahulu.
- IG BELUM: jumlah post/view/like/komen = 0, tanggal/link kosong. IG SUDAH dan TikTok: post minimal 1, tanggal dan URL HTTP(S) wajib. WA: testimoni minimal 1, `respon ≤ dibaca ≤ terkirim`.
- Tanggal tidak di masa depan; validasi PT tidak mendahului tanggal ujian.
- Hapus siswa menghapus nilai dan publikasinya lewat FK cascade, dengan dialog konfirmasi. Unit yang masih berisi siswa/akun tidak dapat dihapus.
- Superadmin mengelola unit dan akun melalui menu khusus. Tidak ada registrasi publik atau password default. Reset password akun dilakukan Superadmin; command `app:create-superadmin` untuk bootstrap/recovery.

## Definisi resume (kumulatif, tanpa filter periode)

| Kolom                       | Definisi                                                                                                                            |
| --------------------------- | ----------------------------------------------------------------------------------------------------------------------------------- |
| JUMSIS                      | Semua siswa dalam unit                                                                                                              |
| NILAI 100 UH/PTS            | Siswa dengan jenis nilai UH atau PTS; PAS/PAT/US tetap disimpan tetapi tidak masuk kolom ini                                        |
| SUDAH TESTIMONI             | Siswa dengan status testimoni SUDAH, seluruh jenis nilai                                                                            |
| SUDAH DIPUBLIKASI           | Siswa unik dengan IG SUDAH & post > 0, atau TikTok post > 0, atau WA testimoni terkirim > 0; seorang siswa maksimal dihitung sekali |
| IG Post/View/Like/Komen     | Jumlah metrik dari catatan IG SUDAH                                                                                                 |
| TikTok Post/View/Like/Komen | Jumlah metrik seluruh catatan TikTok                                                                                                |
| WA Kirim                    | Jumlah `jumlah_testimoni_terkirim`, berbeda dengan jumlah penerima                                                                  |
| WA Terkirim/Dibaca/Respon   | Jumlah masing-masing metrik WA                                                                                                      |

Unit kosong tetap ditampilkan dengan angka 0. Admin Unit hanya mendapat baris unitnya. Ekspor Resume menggunakan query dan urutan kolom yang sama dengan dashboard, tanpa batas pagination.

## Bentuk Excel

- Resume: sama dengan tabel dashboard.
- All Data: format panjang, **satu baris per catatan publikasi**, dengan data siswa/nilai diulang sebagai identitas. Siswa tanpa publikasi tetap satu baris dengan kanal kosong. Ini mempertahankan seluruh catatan tanpa perkalian kombinasi IG × TikTok × WA. Jangan menjumlahkan data siswa dari sheet ini; gunakan Resume.
- Sheet per unit: satu baris per siswa dengan nilai. Nama sheet diawali ID agar unik, karakter terlarang dihapus, panjang maksimum 31 karakter.
- Teks diekspor dengan tipe string eksplisit untuk mempertahankan nol awal noreg dan menghindari formula injection.
- Import: `.xlsx` maksimal 5 MB / 5.000 baris, header `Noreg | Nama | Asal Sekolah | Kelas | Unit`. Gunakan ID Unit dari halaman import. Nama unit juga diterima hanya jika cocok tepat (abaikan kapital) dan tidak ambigu di unit yang dapat diakses. Unit asing ditolak, tidak dipindahkan diam-diam ke unit akun.

## Pengembangan lokal

```bash
cp .env.example .env
composer install
php artisan key:generate
# Isi DB dan ubah APP_ENV=local, APP_DEBUG=true,
# APP_URL=http://127.0.0.1:8000, SESSION_SECURE_COOKIE=false.
php artisan migrate --seed
php artisan app:create-superadmin
npm ci
npm run build
php artisan serve
```

Pengujian: `php artisan test` (membutuhkan pdo_sqlite, DB in-memory). Jalankan juga integrasi pada MySQL/MariaDB staging terpisah. Jangan jalankan RefreshDatabase pada database production.

Export sinkron memakai PhpSpreadsheet; generator mengurangi pemuatan data Eloquent, tetapi workbook Excel tetap memakai memori. Ukur `memory_limit` dan waktu respons dengan ukuran data nyata sebelum memperbesar penggunaan; export antrean belum diterapkan.

## Referensi API

- https://laravel.com/docs/11.x/deployment
- https://laravel.com/docs/11.x/validation
- https://spatie.be/docs/laravel-permission/v6/introduction
- https://docs.laravel-excel.com/3.1/exports/multiple-sheets.html
- https://docs.laravel-excel.com/3.1/imports/validation.html
