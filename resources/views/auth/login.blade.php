<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Masuk - {{ config('app.name', 'FindBack UNIMUS') }}</title>

    <!-- Fonts -->
    @fonts

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full text-slate-800 antialiased flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <!-- Header & Branding -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-600 text-white shadow-lg shadow-blue-500/30 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">FindBack UNIMUS</h1>
            <p class="mt-2 text-sm text-slate-600">
                Sistem Informasi Temuan Barang Kampus Universitas Muhammadiyah Semarang
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-2xl sm:px-10 border border-slate-100">
            <!-- Notice: Demo UTS -->
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-100 p-3 text-xs text-blue-700 flex items-start gap-2.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span>
                    <strong>Demo Proyek UTS:</strong> Masuk menggunakan akun terdaftar (Mahasiswa, Dosen, atau Admin/Satpam).
                </span>
            </div>

            <!-- Error Banner -->
            @if ($errors->any())
                <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-3 text-xs text-red-700" role="alert">
                    <div class="font-semibold mb-1">Gagal masuk ke sistem:</div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                        Email Kampus
                    </label>
                    <div class="relative">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            aria-describedby="{{ $errors->has('email') ? 'email-error' : '' }}"
                            class="block w-full rounded-lg border {{ $errors->has('email') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-blue-500 focus:ring-blue-200' }} px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:ring-3 transition duration-150"
                            placeholder="nama@unimus.ac.id"
                        >
                    </div>
                    @error('email')
                        <p id="email-error" class="mt-1.5 text-xs text-red-600" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            aria-required="true"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            aria-describedby="{{ $errors->has('password') ? 'password-error' : '' }}"
                            class="block w-full rounded-lg border {{ $errors->has('password') ? 'border-red-400 focus:border-red-500 focus:ring-red-200' : 'border-slate-300 focus:border-blue-500 focus:ring-blue-200' }} px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:outline-none focus:ring-3 transition duration-150"
                            placeholder="••••••••"
                        >
                    </div>
                    @error('password')
                        <p id="password-error" class="mt-1.5 text-xs text-red-600" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center gap-2 cursor-pointer">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            {{ old('remember') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 transition duration-150"
                        >
                        <span class="text-xs text-slate-600">Ingat sesi saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 cursor-pointer"
                    >
                        Masuk ke FindBack
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <p class="text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} FindBack UNIMUS. Modul Fondasi & Autentikasi.
        </p>
    </div>
</body>
</html>
