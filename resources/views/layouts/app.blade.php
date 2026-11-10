
<!DOCTYPE html>
<html lang="id">

<head>
    {{-- Metadata --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Page Title --}}
    <title>@yield('title', 'FindBack - UNIMUS')</title>

    {{-- Application Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    {{-- Header --}}
    <header>
        <nav aria-label="Navigasi utama">
    <a href="{{ url('/') }}">
        FindBack
    </a>

    @auth
        <a href="{{ route('catalog.index') }}">
            Katalog
        </a>

        @if (in_array(auth()->user()->role, ['admin', 'security'], true))
            <a href="{{ route('returns.create') }}">
                Form Pengembalian
            </a>

            <a href="{{ route('returns.index') }}">
                Riwayat Pengembalian
            </a>
        @endif

        <span>{{ auth()->user()->name }}</span>
        <span>({{ ucfirst(auth()->user()->role ?? 'mahasiswa') }})</span>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit">Keluar</button>
        </form>
    @endauth
</nav>

    </header>

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer>
        <p>
            &copy; {{ date('Y') }} FindBack - UNIMUS.
            All rights reserved.
        </p>
    </footer>

</body>

</html>
