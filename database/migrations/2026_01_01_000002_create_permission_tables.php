<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('permissions',function(Blueprint $t){$t->id();$t->string('name');$t->string('guard_name');$t->timestamps();$t->unique(['name','guard_name']);});
  Schema::create('roles',function(Blueprint $t){$t->id();$t->string('name');$t->string('guard_name');$t->timestamps();$t->unique(['name','guard_name']);});
  foreach(['permissions'=>'permission','roles'=>'role']as$table=>$singular){Schema::create('model_has_'.$table,function(Blueprint $t)use($table,$singular){$t->foreignId($singular.'_id')->constrained($table)->cascadeOnDelete();$t->string('model_type');$t->unsignedBigInteger('model_id');$t->index(['model_id','model_type']);$t->primary([$singular.'_id','model_id','model_type']);});}
  Schema::create('role_has_permissions',function(Blueprint $t){$t->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();$t->foreignId('role_id')->constrained('roles')->cascadeOnDelete();$t->primary(['permission_id','role_id']);});
 }
 public function down():void {foreach(['role_has_permissions','model_has_roles','model_has_permissions','roles','permissions']as$t)Schema::dropIfExists($t);}
};
