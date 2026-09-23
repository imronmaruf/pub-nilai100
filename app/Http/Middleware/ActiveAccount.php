<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class ActiveAccount {
 public function handle(Request $r,Closure $next){$u=$r->user();abort_unless($u&&($u->hasRole('Superadmin')||($u->hasRole('Admin Unit')&&$u->unit_id!==null)),403,'Akun belum memiliki akses unit.');return $next($r);}
}
