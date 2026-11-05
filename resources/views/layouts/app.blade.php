
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
