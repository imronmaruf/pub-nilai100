<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'Nilai 100') · Nilai 100</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @auth
        <div class="app-shell">
            <aside class="sidebar" data-sidebar>
                <div class="brand"><img
                        src="https://images.seeklogo.com/logo-png/61/2/ganesha-operation-logo-png_seeklogo-613829.png"
                        alt="Ganesha Operation">
                    <div><strong>Publikasi Nilai 100</strong><span>Ganesha Operation</span></div>
                </div>
                <div class="sidebar-label">Menu utama</div>
                <nav class="side-nav" aria-label="Navigasi utama">
                    <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i
                            class="fa-solid fa-chart-pie"></i> Resume</a><a
                        class="sub {{ request()->routeIs('dashboard.pertambahan') ? 'active' : '' }}"
                        href="{{ route('dashboard.pertambahan') }}"><i
                            class="fa-solid fa-arrow-trend-up"></i> Pertambahan per Periode</a><a
                        class="{{ request()->routeIs('all-data.*') ? 'active' : '' }}"
                        href="{{ route('all-data.index') }}"><i class="fa-solid fa-table-list"></i> All Data</a><a
                        class="{{ request()->routeIs('students.*') ? 'active' : '' }}"
                        href="{{ route('students.index') }}"><i class="fa-solid fa-user-group"></i> Siswa</a><a
                        class="{{ request()->routeIs('nilai.*') ? 'active' : '' }}" href="{{ route('nilai.index') }}"><i
                            class="fa-solid fa-award"></i> Nilai 100</a><a
                        class="{{ request()->routeIs('publications.*') ? 'active' : '' }}"
                        href="{{ route('publications.index', 'ig') }}"><i class="fa-solid fa-bullhorn"></i> Publikasi</a>
                    @if (auth()->user()->hasRole('Superadmin'))
                        <div class="sidebar-label">Superadmin</div><a
                            class="{{ request()->routeIs('units.*') ? 'active' : '' }}"
                            href="{{ route('units.index') }}"><i class="fa-solid fa-building"></i> Unit</a><a
                            class="{{ request()->routeIs('mapels.*') ? 'active' : '' }}"
                            href="{{ route('mapels.index') }}"><i class="fa-solid fa-book"></i> Mapel</a><a
                            class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
                            href="{{ route('users.index') }}"><i class="fa-solid fa-users-gear"></i> Akun</a>
                    @endif
                </nav>
                <div class="sidebar-footer">
                    <div class="user-chip"><span>{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <div>
                            <strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->getRoleNames()->first() }}</small>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout"><i
                                class="fa-solid fa-arrow-right-from-bracket"></i> Keluar</button></form>
                </div>
            </aside>
            <div class="main-shell">
                <header class="topbar"><button class="icon-button menu-button" data-menu-toggle aria-label="Buka menu"><i
                            class="fa-solid fa-bars"></i></button><span class="topbar-title">@yield('title', 'Nilai 100')</span><span
                        class="topbar-date"><i class="fa-regular fa-calendar"></i>
                        {{ now()->translatedFormat('d F Y') }}</span></header>
                <main class="content">
                    @if (session('status'))
                        <div role="status" class="alert alert-success"><i
                                class="fa-solid fa-circle-check"></i>{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div role="alert" class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="content">@yield('content')</main>
    @endauth
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>

</html>
