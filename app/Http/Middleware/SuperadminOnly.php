<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class SuperadminOnly {public function handle(Request $r,Closure $next){abort_unless($r->user()?->hasRole('Superadmin'),403);return $next($r);}}
