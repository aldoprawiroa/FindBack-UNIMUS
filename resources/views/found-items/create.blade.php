<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Barang Temuan - FindBack</title>
</head>
<body>
    <h1>Input Barang Temuan</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form
        action="{{ route('found-items.store') }}"
        method="POST"
        enctype="multipart/form-data"
    >
        @csrf

        <div>
            <label for="title">Nama Barang</label>
            <input
                type="text"
                id="title"
                name="title"
                value="{{ old('title') }}"
                required
            >
        </div>

        <div>
            <label for="description">Deskripsi</label>
            <textarea
                id="description"
                name="description"
            >{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih kategori</option>

                @foreach ($categories as $category)
                    <option
                        value="{{ $category->id }}"
                        @selected(old('category_id') == $category->id)
                    >
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="found_at">Tanggal dan Waktu Ditemukan</label>
            <input
                type="datetime-local"
                id="found_at"
                name="found_at"
                value="{{ old('found_at') }}"
                required
            >
        </div>

        <div>
            <label for="photo">Foto Barang (Opsional)</label>
            <input
                type="file"
                id="photo"
                name="photo"
                accept=".jpg,.jpeg,.png,.webp"
            >
            <small>
                Format JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
            </small>
        </div>

        <button type="submit">Simpan sebagai Draft</button>
    </form>
</body>
</html>
