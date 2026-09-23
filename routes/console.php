<?php
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Spatie\Permission\Models\Role;
Artisan::command('app:create-superadmin',function(){
 $name=$this->ask('Nama');$email=$this->ask('Email');$password=$this->secret('Password (minimal 12 karakter)');
 $data=validator(compact('name','email','password'),['name'=>'required|max:255','email'=>'required|email|max:255|unique:users,email','password'=>'required|min:12'])->validate();
 \Illuminate\Support\Facades\DB::transaction(function()use($data){$u=User::create($data);$u->assignRole(Role::findOrCreate('Superadmin','web'));});
 $this->info('Superadmin dibuat.');
})->purpose('Buat superadmin tanpa password bawaan');
