@extends('layouts.app')

@section('title', 'Katalog Barang Temuan - FindBack')

@section('content')
    <div class="container">

        <h1>Katalog Barang Temuan UNIMUS (Terlindungi)</h1>
        <p>Temukan barang yang hilang di lingkungan UNIMUS.</p>

        {{-- Form Pencarian dan Filter --}}
        <form action="{{ route('catalog.index') }}" method="GET">

            <div>
                <label for="search">Cari Barang</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Masukkan nama atau deskripsi barang..."
                >
            </div>

            <div>
                <label for="category">Kategori</label>
                <select name="category" id="category">
                    <option value="">Semua Kategori</option>

                    @foreach ($categories as $category)
                        <option
                            value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit">Cari</button>
            <a href="{{ route('catalog.index') }}">Reset</a>

        </form>

        <hr>

        {{-- Daftar Barang --}}
        <h2>Daftar Barang</h2>

        @forelse ($items as $item)

            <article>
                <h3>{{ $item->title }}</h3>

                <p>
                    Kategori:
                    {{ $item->category?->name ?? 'Tidak ada kategori' }}
                </p>

                <p>
                    Deskripsi:
                    {{ $item->description ?? 'Tidak ada deskripsi' }}
                </p>

                <p>
                    Ditemukan pada:
                    {{ $item->found_at?->format('d-m-Y') ?? '-' }}
                </p>

                <p>
                    Status: {{ $item->status }}
                </p>
            </article>

            <hr>

        @empty

            <p>Belum ada barang temuan yang tersedia.</p>

        @endforelse

        {{-- Navigasi Halaman --}}
        {{ $items->links() }}

    </div>
@endsection