<?php

namespace Tests\Feature;

use App\Models\{Nilai100, Student, Unit, User};
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFiltersTest extends TestCase
{
    use RefreshDatabase;

    private User $root;
    private User $admin;
    private Unit $a;
    private Unit $b;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Unit::query()->delete();
        $this->a = Unit::create(['id' => 900001, 'kota' => 'Kota A', 'nama_unit' => 'Unit A']);
        $this->b = Unit::create(['id' => 900002, 'kota' => 'Kota B', 'nama_unit' => 'Unit B']);
        $this->root = User::create(['name' => 'Root', 'email' => 'root@report.test', 'password' => 'password']);
        $this->root->assignRole('Superadmin');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@report.test', 'password' => 'password', 'unit_id' => $this->a->id]);
        $this->admin->assignRole('Admin Unit');
    }

    private function student(Unit $unit, int $i, string $status = 'SUDAH'): void
    {
        $s = Student::create(['noreg' => sprintf('%05d', $i), 'nama_siswa' => 'Siswa ' . $i, 'asal_sekolah' => 'Sekolah', 'kelas_di_go' => '12', 'tingkat_kelas' => '12', 'level' => 'SMA', 'unit_id' => $unit->id]);
        Nilai100::create(['student_id' => $s->id, 'mapel' => 'MAT', 'jenis_nilai' => 'UH', 'tanggal_ujian' => $i % 2 ? null : '2026-01-01', 'tanggal_validasi_pt' => null, 'status_testimoni' => $status]);
    }

    public function test_nilais_with_null_dates_render_on_all_pages_and_filtered_summaries(): void
    {
        for ($i = 1; $i <= 31; $i++) $this->student($this->a, $i, $i <= 20 ? 'SUDAH' : 'BELUM');
        $this->student($this->b, 40);
        foreach ([$this->root, $this->admin] as $actor) {
            $this->actingAs($actor);
            foreach (['/nilai', '/nilai?page=2', '/nilai?per_page=20', '/nilai?per_page=30&page=2'] as $url) {
                $this->get($url)->assertOk()->assertSee('Siswa Nilai')->assertSee('Jumlah Nilai');
            }
            $url = '/nilai?unit_id[]=' . $this->a->id . '&status_testimoni[]=BELUM&per_page=20';
            $response = $this->get($url)->assertOk()->assertSee('Belum')->assertDontSee('Siswa 40');
            $this->assertSame(11, $response->viewData('rows')->total());
            $this->assertEquals(11, $response->viewData('summary')->total_values);
            $this->assertEquals(11, $response->viewData('summary')->pending);
        }
    }

    public function test_resume_links_multi_filters_and_totals_work_without_dates(): void
    {
        $this->student($this->a, 1, 'BELUM');
        $this->student($this->a, 2);
        $this->student($this->b, 3);
        $this->actingAs($this->root);
        $url = '/?kota[]=Kota+A&unit_id[]=' . $this->a->id;
        $response = $this->get($url)->assertOk()->assertSee('total-row')->assertSee('Total unit');
        $this->assertEquals(1, $response->viewData('totals')['units']);
        $this->assertEquals(2, $response->viewData('totals')['nilai_100_students']);
        $this->assertEquals(1, $response->viewData('totals')['belum_testimoni']);
        $this->get('/?kota[]=Kota+A&kota[]=Kota+B&per_page=20')->assertOk()->assertSee('name="kota[]" value="Kota A"', false)->assertSee('name="kota[]" value="Kota B"', false);
        $this->get('/nilai?unit_id[]=' . $this->a->id . '&status_testimoni[]=BELUM')->assertOk()->assertSee('Siswa 1')->assertDontSee('Siswa 2');
    }

    public function test_all_data_multi_filters_keep_full_totals_and_pagination_values(): void
    {
        for ($i = 1; $i <= 22; $i++) $this->student($this->a, $i);
        $this->student($this->b, 30, 'BELUM');
        $this->actingAs($this->root);
        $url = '/all-data?kota[]=Kota+A&asal_sekolah[]=Sekolah&level[]=SMA&status_testimoni[]=SUDAH&per_page=20';
        $response = $this->get($url)->assertOk()->assertSee('total-row')->assertSee('name="kota[]" value="Kota A"', false);
        $this->assertEquals(22, $response->viewData('totals')['nilai100']);
        $pageTwo = $this->get($url . '&page=2')->assertOk()->assertDontSee('Siswa 30');
        $this->assertEquals(22, $pageTwo->viewData('totals')['nilai100']);
        $this->assertCount(2, $pageTwo->viewData('rows')->items());
    }

    public function test_student_multi_filters_and_status_selection(): void
    {
        $this->student($this->a, 1);
        $this->student($this->b, 2);
        Student::create(['noreg' => '00003', 'nama_siswa' => 'Tanpa nilai', 'asal_sekolah' => 'Sekolah', 'kelas_di_go' => '12', 'tingkat_kelas' => '12', 'level' => 'SMA', 'unit_id' => $this->a->id]);
        $this->actingAs($this->root);
        $response = $this->get('/students?kota[]=Kota+A&unit_id[]=' . $this->a->id . '&nilai_status[]=belum')->assertOk()->assertSee('Tanpa nilai');
        $this->assertEquals(1, $response->viewData('students')->total());
        $response = $this->get('/students?kota[]=Kota+A&kota[]=Kota+B&nilai_status[]=sudah&nilai_status[]=belum')->assertOk();
        $this->assertEquals(3, $response->viewData('students')->total());
        $this->get('/students?unit_id=' . $this->a->id)->assertOk()->assertDontSee('Siswa 2');
    }
}
