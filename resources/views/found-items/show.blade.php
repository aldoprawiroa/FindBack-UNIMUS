
@extends('layouts.app')

@section('title', 'Detail Barang Temuan - FindBack')

@section('content')
    <div class="container">
        <h1>Detail Barang Temuan</h1>

        <article>
            <h2>{{ $item->title }}</h2>

            <p>
                <strong>Kategori:</strong>
                {{ $item->category?->name ?? 'Tidak ada kategori' }}
            </p>

            <p>
                <strong>Deskripsi:</strong>
                {{ $item->description ?? 'Tidak ada deskripsi' }}
            </p>

            <p>
                <strong>Tanggal ditemukan:</strong>
                {{ $item->found_at?->copy()->setTimezone('Asia/Jakarta')->format('d-m-Y H:i') ?? '-' }}
            </p>

            <p>
                <strong>Status:</strong>
                {{ $item->status }}
            </p>
        </article>

        <p>
            <a href="{{ route('catalog.index') }}">Kembali ke Katalog</a>
        </p>
    </div>
@endsection
