
@extends('layouts.app')

@section('title', 'Form Pengembalian Barang')

@section('content')
<div class="container">
    <h1>Form Pengembalian Barang</h1>
    <p>Catat barang temuan yang telah diambil kembali pemiliknya.</p>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('returns.store') }}" method="POST">
        @csrf

        <div>
            <label for="found_item_id">Pilih Barang</label>
            <select name="found_item_id" id="found_item_id" required>
                <option value="">-- Pilih Barang --</option>
                @foreach ($items as $item)
                    <option value="{{ $item->id }}"
                        {{ old('found_item_id') == $item->id ? 'selected' : '' }}>
                        {{ $item->title }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="recipient_name">Nama Penerima</label>
            <input
                type="text"
                name="recipient_name"
                id="recipient_name"
                value="{{ old('recipient_name') }}"
                required
            >
        </div>

        <div>
            <label for="security_post_id">Pos Keamanan</label>
            <select name="security_post_id" id="security_post_id" required>
                <option value="">-- Pilih Pos Keamanan --</option>
                @foreach ($securityPosts as $post)
                    <option value="{{ $post->id }}"
                        {{ old('security_post_id') == $post->id ? 'selected' : '' }}>
                        {{ $post->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="returned_at">Tanggal dan Waktu Pengembalian</label>
            <input
                type="datetime-local"
                name="returned_at"
                id="returned_at"
                value="{{ old('returned_at') }}"
                required
            >
        </div>

        <div>
            <label for="notes">Catatan</label>
            <textarea name="notes" id="notes">{{ old('notes') }}</textarea>
        </div>

        <button type="submit">Simpan Pengembalian</button>
    </form>
</div>
@endsection
