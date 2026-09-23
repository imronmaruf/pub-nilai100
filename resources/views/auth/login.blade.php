@extends('layouts.app')
@section('title','Masuk')
@section('content')
<form method="POST" action="{{ route('login') }}" class="mx-auto mt-12 max-w-sm space-y-5 rounded border border-slate-200 p-6">@csrf
<h1 class="text-lg font-semibold">Masuk</h1><x-field name="email" label="Email" type="email" required autofocus autocomplete="username"/><x-field name="password" label="Password" type="password" required autocomplete="current-password"/><button class="btn w-full">Masuk</button>
</form>
@endsection
