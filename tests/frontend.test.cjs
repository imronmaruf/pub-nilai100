const {test} = require('node:test');
const assert = require('node:assert/strict');
const {JSDOM} = require('jsdom');
const fs = require('node:fs');
const source = fs.readFileSync(require('node:path').join(__dirname,'../resources/js/app.js'),'utf8');
const pause = ms => new Promise(resolve=>setTimeout(resolve,ms));
function page(eligible='0',ig=false){
 const dom=new JSDOM(`<form data-publication-form><div data-student-picker data-eligible="${eligible}" data-url="/students/lookup"><input id="student_search"><select id="student_id"><option value="">Pilih</option></select><p data-search-status></p><input data-display="nama_siswa" readonly><input data-display="unit" readonly></div><p data-eligibility-message></p><fieldset data-publication-fields disabled>${ig?'<select data-ig-status><option>BELUM</option><option>SUDAH</option></select>':''}<input name="tanggal_posting"><input name="link_postingan"><input name="jumlah_postingan" value="1"><input name="view" value="0"><input name="like" value="0"><input name="komen" value="0"><button>Simpan</button></fieldset></form>`,{url:'https://nilai.example.test',runScripts:'outside-only'});
 return dom;
}
test('publication form starts locked and edit with eligible student opens',()=>{
 for(const eligible of ['0','1']){const dom=page(eligible);dom.window.eval(source);assert.equal(dom.window.document.querySelector('fieldset').disabled,eligible!=='1');dom.window.close();}
});
test('AJAX selection autofills text, gates eligibility, and typing clears stale selection',async()=>{
 const dom=page();const w=dom.window,d=w.document;
 w.fetch=async()=>({ok:true,json:async()=>[{id:1,noreg:'0001',nama_siswa:'<img src=x>',unit:'Unit A',eligible:true},{id:2,noreg:'0002',nama_siswa:'Siswa 2',unit:'Unit A',eligible:false}]});
 w.eval(source);const search=d.querySelector('#student_search');search.value='00';search.dispatchEvent(new w.Event('input'));await pause(320);
 const select=d.querySelector('#student_id');assert.equal(select.options.length,3);select.value='1';select.dispatchEvent(new w.Event('change'));
 assert.equal(d.querySelector('[data-display="nama_siswa"]').value,'<img src=x>');assert.equal(d.querySelectorAll('img').length,0);assert.equal(d.querySelector('fieldset').disabled,false);
 select.value='2';select.dispatchEvent(new w.Event('change'));assert.equal(d.querySelector('fieldset').disabled,true);
 search.value='x';search.dispatchEvent(new w.Event('input'));assert.equal(select.value,'');assert.equal(d.querySelector('[data-display="nama_siswa"]').value,'');dom.window.close();
});
test('IG draft disables URL/date and zeroes metrics; published requires them',()=>{
 const dom=page('1',true);const w=dom.window,d=w.document;w.eval(source);
 assert.equal(d.querySelector('[name="jumlah_postingan"]').value,'0');assert.equal(d.querySelector('[name="link_postingan"]').disabled,true);
 const status=d.querySelector('[data-ig-status]');status.value='SUDAH';status.dispatchEvent(new w.Event('change'));
 assert.equal(d.querySelector('[name="jumlah_postingan"]').value,'1');assert.equal(d.querySelector('[name="link_postingan"]').required,true);assert.equal(d.querySelector('[name="link_postingan"]').disabled,false);dom.window.close();
});
test('out of order lookup responses cannot restore stale student results',async()=>{
 const dom=page();const w=dom.window,d=w.document;let resolveOld;
 w.fetch=url=>String(url).includes('q=old')?new Promise(resolve=>{resolveOld=resolve}):Promise.resolve({ok:true,json:async()=>[{id:2,noreg:'NEW',nama_siswa:'New',unit:'A',eligible:true}]});
 w.eval(source);const input=d.querySelector('#student_search');input.value='old';input.dispatchEvent(new w.Event('input'));await pause(280);
 input.value='new';input.dispatchEvent(new w.Event('input'));await pause(280);
 resolveOld({ok:true,json:async()=>[{id:1,noreg:'OLD',nama_siswa:'Old',unit:'A',eligible:true}]});await pause(10);
 assert.equal(d.querySelector('#student_id').options.length,2);assert.equal(d.querySelector('#student_id').options[1].value,'2');dom.window.close();
});
