<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB,Hash};
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use App\Models\{User,Unit,Student,Nilai100,PublikasiIg,PublikasiTiktok,PublikasiWa};
use App\Services\ResumeService;
use App\Exports\{ReportExport,ResumeSheet,StudentSheet,AllDataSheet,ActivityTemplateExport};
use App\Imports\StudentsImport;
use Database\Seeders\DatabaseSeeder;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\{Spreadsheet,Writer\Xlsx,IOFactory};
class SystemTest extends TestCase {
 use RefreshDatabase;
 private Unit $a;private Unit $b;private User $admin;private User $root;private Student $own;private Student $other;
 protected function setUp():void {
  parent::setUp();$this->seed(DatabaseSeeder::class);
  User::query()->update(['unit_id'=>null]);Unit::query()->delete();$this->a=Unit::create(['id'=>900001,'kota'=>'Kota A','nama_unit'=>'Unit A']);$this->b=Unit::create(['id'=>900002,'kota'=>'Kota B','nama_unit'=>'Unit B']);
  $this->admin=User::create(['name'=>'Admin','email'=>'admin@example.test','password'=>'long-password','unit_id'=>$this->a->id]);$this->admin->assignRole('Admin Unit');
  $this->root=User::create(['name'=>'Root','email'=>'root@example.test','password'=>'long-password']);$this->root->assignRole('Superadmin');
  $this->own=$this->student($this->a,'0001');$this->other=$this->student($this->b,'0002');
 }
 private function student(Unit $u,string $n):Student{return Student::create(['noreg'=>$n,'nama_siswa'=>'Siswa '.$n,'asal_sekolah'=>'Sekolah','kelas_di_go'=>'12','tingkat_kelas'=>'12','level'=>'SMA','unit_id'=>$u->id]);}
 private function grade(Student $s,string $status='SUDAH'):Nilai100{return $s->nilai100()->create(['mapel'=>'MAT','jenis_nilai'=>'UH','tanggal_ujian'=>'2026-01-01','tanggal_validasi_pt'=>'2026-01-02','status_testimoni'=>$status]);}
 private function postData(Student $s):array{return ['student_id'=>$s->id,'status_publikasi'=>'SUDAH','jumlah_postingan'=>2,'tanggal_posting'=>'2026-01-03','link_postingan'=>'https://instagram.com/p/example','view'=>10,'like'=>3,'komen'=>1];}
 public function test_login_and_logout():void {$this->get('/')->assertRedirect('/login');$this->post('/login',['email'=>$this->admin->email,'password'=>'wrong'])->assertSessionHasErrors('email');$this->post('/login',['email'=>$this->admin->email,'password'=>'long-password'])->assertRedirect('/');$this->assertAuthenticatedAs($this->admin);$this->post('/logout')->assertRedirect('/login');$this->assertGuest();}
 public function test_admin_cannot_read_search_update_or_delete_other_unit():void {
  $this->actingAs($this->admin)->get('/students')->assertOk()->assertSee('0001')->assertDontSee('0002');
  $this->getJson('/students/lookup?q=0002')->assertOk()->assertExactJson([]);
  $this->get('/students/'.$this->other->id.'/edit')->assertNotFound();
  $this->put('/students/'.$this->other->id,$this->other->toArray())->assertNotFound();
  $this->delete('/students/'.$this->other->id)->assertNotFound();
  $this->assertDatabaseHas('students',['id'=>$this->other->id]);
 }
 public function test_admin_cannot_forge_unit_on_create():void {$this->actingAs($this->admin)->post('/students',['noreg'=>'0099','nama_siswa'=>'X','asal_sekolah'=>'X','kelas_di_go'=>'12','tingkat_kelas'=>'12','level'=>'SMA','unit_id'=>$this->b->id])->assertSessionHasErrors('unit_id');$this->assertDatabaseMissing('students',['noreg'=>'0099']);}
 public function test_account_without_unit_fails_closed():void {$this->admin->update(['unit_id'=>null]);$this->actingAs($this->admin)->get('/')->assertForbidden();}
 public function test_roleless_account_fails_closed():void {$this->admin->syncRoles([]);$this->actingAs($this->admin)->get('/students')->assertForbidden();}
 public function test_superadmin_sees_all_units_and_pages_render():void {
  $this->grade($this->own);$p=PublikasiIg::create($this->postData($this->own));
  $this->actingAs($this->root);
  foreach(['/','/students','/students/create','/students/import','/students/'.$this->own->id.'/edit','/nilai','/nilai/create','/nilai/'.$this->own->id.'/edit','/publications/ig','/publications/ig/create','/publications/ig/'.$p->id.'/edit','/publications/wa/create','/publications/tiktok/create','/units','/units/'.$this->a->id.'/edit','/users','/users/create','/users/'.$this->admin->id.'/edit'] as $url)$this->get($url)->assertOk();
  $this->get('/students')->assertSee('0001')->assertSee('0002');
 }
 public function test_unit_admin_cannot_manage_accounts_or_units():void {$this->actingAs($this->admin)->get('/users')->assertForbidden();$this->get('/units')->assertForbidden();$this->post('/users',[])->assertForbidden();}
 public function test_grade_is_unique_and_other_unit_grade_rejected():void {
  $data=['student_id'=>$this->own->id,'mapel'=>'MAT','jenis_nilai'=>'UH','tanggal_ujian'=>'2026-01-01','tanggal_validasi_pt'=>'2026-01-02','status_testimoni'=>'SUDAH'];
  $this->actingAs($this->admin)->post('/nilai',$data)->assertSessionHasNoErrors();$this->post('/nilai',$data)->assertSessionHasErrors('student_id');
  $data['student_id']=$this->other->id;$this->post('/nilai',$data)->assertNotFound();$this->assertDatabaseCount('nilai_100',1);
 }
 public function test_grade_date_order_rejected():void {$this->actingAs($this->admin)->post('/nilai',['student_id'=>$this->own->id,'mapel'=>'MAT','jenis_nilai'=>'UH','tanggal_ujian'=>'2026-01-03','tanggal_validasi_pt'=>'2026-01-02','status_testimoni'=>'SUDAH'])->assertSessionHasErrors('tanggal_validasi_pt');}
 public function test_publication_requires_grade_and_completed_testimonial():void {
  $this->actingAs($this->admin)->post('/publications/ig',$this->postData($this->own))->assertSessionHasErrors('student_id');
  $n=$this->grade($this->own,'BELUM');$this->post('/publications/ig',$this->postData($this->own))->assertSessionHasErrors('student_id');
  $n->update(['status_testimoni'=>'SUDAH']);$this->post('/publications/ig',$this->postData($this->own))->assertSessionHasNoErrors();$this->assertDatabaseCount('publikasi_ig',1);
 }
 public function test_all_channels_and_edits_recheck_eligibility():void {
  $n=$this->grade($this->own);$p=PublikasiIg::create($this->postData($this->own));$n->update(['status_testimoni'=>'BELUM']);
  $data=$this->postData($this->own);unset($data['student_id']);
  $this->actingAs($this->admin)->put('/publications/ig/'.$p->id,$data)->assertSessionHasErrors('student_id');
  $this->post('/publications/tiktok',$this->postData($this->own))->assertSessionHasErrors('student_id');
  $this->post('/publications/wa',['student_id'=>$this->own->id,'jumlah_testimoni_terkirim'=>1,'tanggal_blast'=>'2026-01-03','terkirim'=>10,'dibaca'=>5,'respon'=>1])->assertSessionHasErrors('student_id');
 }
 public function test_foreign_publication_is_inaccessible():void {
  $this->grade($this->other);$p=PublikasiIg::create($this->postData($this->other));$this->actingAs($this->admin);
  $this->get('/publications/ig/'.$p->id.'/edit')->assertNotFound();$this->post('/publications/ig',$this->postData($this->other))->assertNotFound();
  $data=$this->postData($this->other);unset($data['student_id']);$this->put('/publications/ig/'.$p->id,$data)->assertNotFound();$this->delete('/publications/ig/'.$p->id)->assertNotFound();
 }
 public function test_publication_student_cannot_be_changed_on_update():void {$this->grade($this->own);$p=PublikasiIg::create($this->postData($this->own));$this->actingAs($this->admin)->put('/publications/ig/'.$p->id,$this->postData($this->other))->assertSessionHasErrors('student_id');}
 public function test_grade_cannot_be_removed_or_downgraded_with_publications():void {
  $this->grade($this->own);PublikasiIg::create($this->postData($this->own));$this->actingAs($this->admin)->delete('/nilai/'.$this->own->id)->assertSessionHasErrors('nilai');
  $this->put('/nilai/'.$this->own->id,['mapel'=>'MAT','jenis_nilai'=>'UH','tanggal_ujian'=>'2026-01-01','tanggal_validasi_pt'=>'2026-01-02','status_testimoni'=>'BELUM'])->assertSessionHasErrors('status_testimoni');
 }
 public function test_negative_metrics_and_invalid_url_are_rejected():void {$this->grade($this->own);$data=$this->postData($this->own);$data['view']=-1;$data['link_postingan']='javascript:alert(1)';$this->actingAs($this->admin)->post('/publications/ig',$data)->assertSessionHasErrors(['view','link_postingan']);}
 public function test_wa_read_and_response_counts_cannot_exceed_delivery():void {$this->grade($this->own);$this->actingAs($this->admin)->post('/publications/wa',['student_id'=>$this->own->id,'jumlah_testimoni_terkirim'=>1,'tanggal_blast'=>'2026-01-03','terkirim'=>2,'dibaca'=>3,'respon'=>4])->assertSessionHasErrors(['dibaca','respon']);}
 public function test_draft_ig_requires_zero_metrics():void {$this->grade($this->own);$data=$this->postData($this->own);$data['status_publikasi']='BELUM';$this->actingAs($this->admin)->post('/publications/ig',$data)->assertSessionHasErrors(['jumlah_postingan','tanggal_posting','link_postingan']);}
 public function test_resume_does_not_multiply_metrics_and_counts_unique_students():void {
  $this->grade($this->own);foreach([1,2]as$i)PublikasiIg::create($this->postData($this->own));PublikasiTiktok::create($this->postData($this->own));
  PublikasiWa::create(['student_id'=>$this->own->id,'jumlah_testimoni_terkirim'=>3,'tanggal_blast'=>'2026-01-03','terkirim'=>10,'dibaca'=>5,'respon'=>1]);
  $rows=app(ResumeService::class)->query($this->admin)->get();$this->assertCount(1,$rows);$r=$rows->first();
  $this->assertEquals(1,$r->jumsis);$this->assertEquals(1,$r->dipublikasi);$this->assertEquals(4,$r->ig_post);$this->assertEquals(20,$r->ig_view);$this->assertEquals(2,$r->tt_post);$this->assertEquals(3,$r->wa_kirim);
  $this->assertCount(2,app(ResumeService::class)->query($this->root)->get());
 }
 public function test_export_scope_sheet_names_text_and_raw_event_rows():void {
  $this->grade($this->own);PublikasiIg::create($this->postData($this->own));PublikasiTiktok::create($this->postData($this->own));
  $sheets=(new ReportExport($this->admin))->sheets();$this->assertCount(3,$sheets);$this->assertSame('Resume',$sheets[0]->title());$this->assertSame('All Data',$sheets[1]->title());
  $raw=iterator_to_array($sheets[1]->generator());$this->assertCount(2,$raw);$this->assertSame('0001',$raw[0][1]);$this->assertCount(32,$raw[0]);
  $bytes=Excel::raw(new ReportExport($this->admin),\Maatwebsite\Excel\Excel::XLSX);$path=tempnam(sys_get_temp_dir(),'export');file_put_contents($path,$bytes);
  try{$book=IOFactory::load($path);$this->assertSame('0001',$book->getSheet(1)->getCell('B2')->getValue());$this->assertSame('s',$book->getSheet(1)->getCell('B2')->getDataType());$this->assertEquals(1,$book->getSheet(0)->getCell('D2')->getValue());}finally{unlink($path);}
 }
 public function test_import_rejects_cross_unit_duplicate_and_has_atomic_rollback():void {
  $rows=collect([collect(['Noreg','Nama','Asal Sekolah','Tingkat Kelas','Kelas di GO','Level','Unit']),collect(['0010','Valid','School','12','12','SMA',$this->a->id]),collect(['0011','Foreign','School','12','12','SMA',$this->b->id])]);
  try{DB::transaction(fn()=>(new StudentsImport($this->admin))->collection($rows));$this->fail('Must reject foreign unit');}catch(ValidationException $e){$this->assertDatabaseMissing('students',['noreg'=>'0010']);}
  $rows=collect([collect(['Noreg','Nama','Asal Sekolah','Tingkat Kelas','Kelas di GO','Level','Unit']),collect(['0001','Duplicate','School','12','12','SMA',$this->a->id])]);
  $this->expectException(ValidationException::class);(new StudentsImport($this->admin))->collection($rows);
 }
 public function test_real_xlsx_upload_preserves_leading_zero_noreg():void {
  $book=new Spreadsheet;$sheet=$book->getActiveSheet();$sheet->fromArray([['Noreg','Nama','Asal Sekolah','Tingkat Kelas','Kelas di GO','Level','Unit'],['','Imported','School','12','12','SMA',(string)$this->a->id]]);$sheet->setCellValueExplicit('A2','000010',\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
  $book->createSheet()->setTitle('Referensi');
  $path=tempnam(sys_get_temp_dir(),'import').'.xlsx';(new Xlsx($book))->save($path);
   try{$response=$this->actingAs($this->admin)->post('/students/import',['file'=>new UploadedFile($path,'students.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',null,true)]);$response->assertSessionHasNoErrors();$this->assertDatabaseHas('students',['noreg'=>'000010','unit_id'=>$this->a->id]);}finally{if(file_exists($path))unlink($path);}
 }
 public function test_nilai_template_with_guide_sheet_imports_successfully():void {
  $path=tempnam(sys_get_temp_dir(),'nilai').'.xlsx';
  file_put_contents($path,Excel::raw(new ActivityTemplateExport('nilai'),\Maatwebsite\Excel\Excel::XLSX));
  $book=IOFactory::load($path);
  $sheet=$book->getSheet(0);
  $sheet->fromArray([['0001','MAT','UH','01/01/2026','02/01/2026','SUDAH','ok']],null,'A2');
  (new Xlsx($book))->save($path);
  try{
    $this->actingAs($this->admin)->post('/nilai/import',['file'=>new UploadedFile($path,'template_import_nilai_100.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',null,true)])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('nilai_100',['student_id'=>$this->own->id,'mapel'=>'MAT','jenis_nilai'=>'UH','status_testimoni'=>'SUDAH']);
  }finally{if(file_exists($path))unlink($path);}
 }
 public function test_publication_template_with_guide_sheet_imports_successfully():void {
  $this->grade($this->own);
  $path=tempnam(sys_get_temp_dir(),'ig').'.xlsx';
  file_put_contents($path,Excel::raw(new ActivityTemplateExport('ig'),\Maatwebsite\Excel\Excel::XLSX));
  $book=IOFactory::load($path);
  $book->getSheet(0)->fromArray([['0001','SUDAH',2,'03/01/2026','https://instagram.com/p/example',10,3,1]],null,'A2');
  (new Xlsx($book))->save($path);
  try{
    $this->actingAs($this->admin)->post('/publications/ig/import',['file'=>new UploadedFile($path,'template_import_ig.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',null,true)])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('publikasi_ig',['student_id'=>$this->own->id,'jumlah_postingan'=>2]);
  }finally{if(file_exists($path))unlink($path);}
 }
 public function test_student_lookup_returns_json_for_own_unit():void {
  $this->actingAs($this->admin)->getJson('/students/lookup?q=0001')->assertOk()->assertJsonFragment(['noreg'=>'0001','nama_siswa'=>'Siswa 0001']);
  $this->get('/nilai/create')->assertOk()->assertSee('data-url="/students/lookup"',false);
 }
 public function test_exported_user_text_is_not_an_excel_formula():void {
  $this->own->update(['nama_siswa'=>'=1+1']);$sheet=new StudentSheet($this->admin,$this->a->id,'Unit');$book=new Spreadsheet;$cell=$book->getActiveSheet()->getCell('A1');$sheet->bindValue($cell,'=1+1');$this->assertSame('s',$cell->getDataType());$this->assertSame('=1+1',$cell->getValue());
 }
 public function test_student_delete_cascades_data():void {$this->grade($this->own);PublikasiIg::create($this->postData($this->own));$this->actingAs($this->admin)->delete('/students/'.$this->own->id)->assertSessionHasNoErrors();$this->assertDatabaseMissing('nilai_100',['student_id'=>$this->own->id]);$this->assertDatabaseMissing('publikasi_ig',['student_id'=>$this->own->id]);}
}
