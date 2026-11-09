
@extends('layouts.app')

@section('title', 'Riwayat Pengembalian')

@section('content')
<div class="container">
    <h1>Riwayat Pengembalian Barang</h1>
    <p>Daftar barang temuan yang sudah dikembalikan kepada pemiliknya.</p>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    <p>
        <a href="{{ route('returns.create') }}">
            + Tambah Pengembalian
        </a>
    </p>

    <hr>

    @forelse ($returns as $record)
        <article>
            <h3>{{ $record->foundItem->title ?? 'Barang tidak tersedia' }}</h3>

            <p>
                Nama Penerima:
                {{ $record->recipient_name }}
            </p>

            <p>
                Pos Keamanan:
                {{ $record->securityPost->name ?? '-' }}
            </p>

            <p>
                Petugas:
                {{ $record->processor->name ?? '-' }}
            </p>

            <p>
                Tanggal Pengembalian:
                {{ $record->returned_at?->format('d-m-Y H:i') ?? '-' }}
            </p>

            <p>
                Catatan:
                {{ $record->notes ?? '-' }}
            </p>

            <p>Status: Returned</p>
        </article>

        <hr>
    @empty
        <p>Belum ada riwayat pengembalian barang.</p>
    @endforelse

    {{ $returns->links() }}
</div>
@endsection
