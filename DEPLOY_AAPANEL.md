# Deploy aaPanel

## 1. Siapkan runtime dan database

1. Pasang Nginx, PHP 8.2+ dan MySQL 8/MariaDB via aaPanel. Samakan versi PHP CLI dengan PHP-FPM situs.
2. Aktifkan OPcache dan ekstensi: pdo_mysql, mbstring, openssl, tokenizer, ctype, fileinfo, xml/dom/simplexml/xmlreader/xmlwriter, zip, gd, bcmath; intl disarankan. Untuk test, pdo_sqlite diperlukan.
3. PHP: mulai dengan memory_limit 512M, upload_max_filesize 8M, post_max_size 10M, max_execution_time 120; sesuaikan setelah uji data nyata. Nginx client_max_body_size 10m.
4. Buat database `nilai100` dan user khusus yang hanya memiliki akses ke database tersebut. Jangan memakai root database.

## 2. Build dan uji di staging

Ekstrak folder proyek. Gunakan Composer 2 dan Node.js 20+.

```bash
cd /path/staging/nilai100
cp .env.example .env
composer install
php artisan key:generate
```

Isi koneksi database staging. `.env` tidak boleh ikut Git atau dibagikan. APP_KEY harus tetap sama setelah sistem terpakai.

```bash
php artisan migrate --seed
npm ci
npm run build
php artisan test
composer audit
php artisan route:list
```

Paket ini belum menyertakan composer.lock karena runtime Composer tidak tersedia saat dibuat. Instalasi pertama menghasilkan lock; review dan simpan lock tersebut. Bila Composer menolak dependency karena advisori, selesaikan versi yang bermasalah; jangan mematikan blokir keamanan. Laravel 11 dipertahankan sesuai spesifikasi, tetapi kelayakan dukungan versi wajib diperiksa saat deploy.

Jika test tidak lulus, perbaiki sebelum lanjut. Uji login dua Admin Unit dan satu Superadmin, unggah Excel asli, unduh hasil multi-sheet, dan periksa angka resume pada database MySQL/MariaDB staging. Test default memakai SQLite agar aman dan terisolasi.

## 3. Pasang aplikasi production

Upload source + composer.lock hasil staging + public/build ke `/www/wwwroot/nilai100`. Jangan upload node_modules, .env staging, log, atau database test. Tambahkan site pada aaPanel; root aplikasi berada di folder tersebut, **running directory/document root harus `/public`**. Nonaktifkan pilihan open_basedir aaPanel hanya bila aturan panel mencegah Laravel membaca vendor/storage; lebih baik konfigurasikan batas direktori ke root aplikasi.

```bash
cd /www/wwwroot/nilai100
cp .env.example .env
composer install --no-dev --optimize-autoloader
php artisan key:generate
```

Isi APP_URL domain HTTPS, APP_ENV=production, APP_DEBUG=false, SESSION_SECURE_COOKIE=true, DB_HOST/DB_DATABASE/DB_USERNAME/DB_PASSWORD. Jangan jalankan key:generate lagi pada deployment pembaruan.

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan app:create-superadmin
php artisan storage:link
```

Seeder hanya membuat dua role; tidak membuat akun atau siswa dummy. Command terakhir akun meminta password tersembunyi, minimum 12 karakter. Login Superadmin → buat unit → buat Admin Unit → import siswa → input nilai → publikasi.

## 4. Permission

Sesuaikan `www` jika PHP-FPM aaPanel menggunakan user lain. Source idealnya dimiliki user deploy; hanya direktori runtime yang writable oleh PHP-FPM.

```bash
chown -R www:www storage bootstrap/cache
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
chown DEPLOY_USER:www .env
chmod 640 .env
```

Ganti DEPLOY_USER dengan user deploy sebenarnya. Jangan menggunakan chmod 777. Cek juga direktori induk memberi izin traverse kepada PHP-FPM. Pastikan symlink public/storage dapat dibaca.

## 5. Nginx dan HTTPS

- Contoh blok: `deploy/nginx.conf.example`. Sesuaikan server_name, root, dan socket PHP-FPM. aaPanel umumnya menyediakan `enable-php-82.conf`; gunakan satu handler PHP saja, jangan menduplikasinya dengan blok contoh.
- Rewrite utama: `try_files $uri $uri/ /index.php?$query_string;`.
- Jangan expose root aplikasi, `.env`, vendor, storage/private, Git atau file konfigurasi.
- Di menu SSL aaPanel, terbitkan Let's Encrypt untuk domain yang DNS-nya sudah menuju VPS. Aktifkan Force HTTPS setelah sertifikat berhasil terbit. Pastikan HTTP challenge `.well-known/acme-challenge/` tetap dapat diakses dan renewal aktif.
- Jika ada reverse proxy/CDN, atur trusted proxies Laravel hanya untuk proxy yang benar-benar digunakan, agar scheme HTTPS terdeteksi dengan benar.

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
nginx -t
```

Reload Nginx dan PHP-FPM lewat aaPanel setelah konfigurasi benar; restart PHP-FPM setelah deploy bila OPcache menyimpan kode lama. Buka `/up` untuk health endpoint dan `/login` untuk login.

## 6. Pembaruan dan backup

Backup database dan `.env` secara terjadwal ke lokasi privat di luar public/. Sebelum migration production, ambil backup terverifikasi. Saat upgrade: maintenance singkat → upload versi/lock/build → composer install → migrate --force → rebuild cache → keluar maintenance. Gunakan `php artisan down` / `php artisan up` pada jendela deploy.

Aplikasi memakai session/cache file serta queue sync, jadi tidak membutuhkan Redis, queue worker atau cron scheduler untuk fitur saat ini. Batasi akses aaPanel dan database ke alamat yang diperlukan.

## Checklist penerimaan

- Login salah dibatasi; logout menghapus session.
- Admin A tidak dapat membaca/edit/hapus siswa, nilai atau publikasi B, termasuk lewat ID manual dan endpoint lookup.
- Akun tanpa unit ditolak, bukan dianggap Superadmin.
- Import unit asing/duplikat gagal tanpa insert parsial.
- Publikasi BELUM testimoni ditolak di UI dan POST/PUT langsung.
- Testimoni SUDAH membuka form; statistik negatif ditolak.
- Dua IG dan satu TikTok milik satu siswa tidak melipatgandakan JUMSIS atau metrik resume.
- Export Admin Unit tidak mengandung data unit lain; noreg nol awal tidak hilang.
- Route/view cache berhasil; document root public/, HTTPS dan APP_DEBUG=false benar.
