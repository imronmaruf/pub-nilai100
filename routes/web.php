<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController, StudentController, NilaiController, PublicationController, DashboardController, AllDataController, UnitController, UserController};

Route::middleware('guest')->group(function () {
  Route::get('/login', [AuthController::class, 'create'])->name('login');
  Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1');
});
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware(['auth', 'account'])->group(function () {
  Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
  Route::get('/export', [DashboardController::class, 'export'])->middleware('throttle:5,1')->name('export');
  Route::get('/all-data', [AllDataController::class, 'index'])->name('all-data.index');
  Route::get('/all-data/export', [AllDataController::class, 'export'])->middleware('throttle:5,1')->name('all-data.export');
  Route::get('/students/lookup', [StudentController::class, 'lookup'])->middleware('throttle:90,1')->name('students.lookup');
  Route::get('/students/import', [StudentController::class, 'importForm'])->name('students.import');
  Route::get('/students/import/template', [StudentController::class, 'template'])->name('students.import.template');
  Route::post('/students/import', [StudentController::class, 'import'])->middleware('throttle:5,1')->name('students.import.store');
  Route::resource('students', StudentController::class)->except('show');
  Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
  Route::get('/nilai/import', [NilaiController::class, 'importForm'])->name('nilai.import');
  Route::get('/nilai/import/template', [NilaiController::class, 'template'])->name('nilai.import.template');
  Route::post('/nilai/import', [NilaiController::class, 'import'])->middleware('throttle:5,1')->name('nilai.import.store');
  Route::get('/nilai/create', [NilaiController::class, 'create'])->name('nilai.create');
  Route::post('/nilai', [NilaiController::class, 'store'])->name('nilai.store');
  Route::get('/nilai/{student}/edit', [NilaiController::class, 'edit'])->name('nilai.edit');
  Route::put('/nilai/{student}', [NilaiController::class, 'update'])->name('nilai.update');
  Route::delete('/nilai/{student}', [NilaiController::class, 'destroy'])->name('nilai.destroy');
  Route::prefix('publications/{channel}')->where(['channel' => 'ig|tiktok|wa'])->name('publications.')->group(function () {
    Route::get('/', [PublicationController::class, 'index'])->name('index');
    Route::get('/import', [PublicationController::class, 'importForm'])->name('import');
    Route::get('/import/template', [PublicationController::class, 'template'])->name('import.template');
    Route::post('/import', [PublicationController::class, 'import'])->middleware('throttle:5,1')->name('import.store');
    Route::get('/create', [PublicationController::class, 'create'])->name('create');
    Route::post('/', [PublicationController::class, 'store'])->name('store');
    Route::get('/{publication}/edit', [PublicationController::class, 'edit'])->name('edit');
    Route::put('/{publication}', [PublicationController::class, 'update'])->name('update');
    Route::delete('/{publication}', [PublicationController::class, 'destroy'])->name('destroy');
  });
  Route::middleware('superadmin')->group(function () {
    Route::resource('units', UnitController::class)->except(['create', 'show']);
    Route::resource('users', UserController::class)->except('show');
  });
});
// aaPanel deployment checklist:
// PHP 8.2+ with OPcache, pdo_mysql, mbstring, xml, zip, gd, bcmath, intl.
// composer install --no-dev --optimize-autoloader; npm ci && npm run build.
// php artisan migrate --force; php artisan db:seed --force; php artisan storage:link.
// storage and bootstrap/cache: owner PHP-FPM user, directories chmod 775, files 664 (never 777).
// Nginx root MUST point to public/; configure try_files; enable Let's Encrypt + force HTTPS.
// APP_DEBUG=false; SESSION_SECURE_COOKIE=true; see DEPLOY_AAPANEL.md.
