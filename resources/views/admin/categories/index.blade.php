@extends('layouts.app')

@section('title', 'Daftar Kategori - FindBack UNIMUS')

@section('content')
<div class="container">
    <h1>Daftar Kategori Barang</h1>

    <p>
        Halaman pengelolaan kategori barang temuan FindBack UNIMUS.
    </p>

    @if ($categories->isEmpty())
        <p>Belum ada kategori yang tersedia.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kategori</th>
                    <th>Slug</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->slug }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
