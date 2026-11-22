# FindBack UNIMUS

**Sistem Informasi Barang Temuan Universitas Muhammadiyah Semarang (UNIMUS)**

FindBack merupakan aplikasi berbasis web untuk membantu pengelolaan informasi barang temuan di lingkungan Universitas Muhammadiyah Semarang.

## 1. Fitur Utama

- Formulir input barang temuan.
- Penyimpanan data barang temuan sebagai Draft.
- Pengelolaan status publikasi barang.
- Pengarsipan barang temuan.
- Penyimpanan foto barang secara privat.
- Validasi data barang dan foto.
- Katalog barang temuan dengan pencarian dan filter kategori.

## 2. Teknologi

- Laravel 13
- PHP 8.3
- MySQL
- Blade
- Tailwind CSS
- Vite
## Demo Accounts

Demo accounts can only be seeded in local or testing environments. Set a local `DEMO_USER_PASSWORD` value with at least eight characters in `.env`, then run:

```bash
php artisan db:seed --class="Database\Seeders\DemoUserSeeder"
```

The default `php artisan db:seed` command does not create demo accounts. Re-running the demo seeder will not replace passwords for accounts that already exist. Do not commit real passwords.


## 3. Struktur Data Barang Temuan

Data barang temuan disimpan pada tabel `found_items`.

| Kolom | Keterangan |
|---|---|
| `id` | Primary key |
| `title` | Nama barang temuan |
| `description` | Deskripsi barang, opsional |
| `category_id` | Relasi ke tabel kategori |
| `found_at` | Waktu barang ditemukan |
| `status` | Status barang, default `Draft` |
| `published_at` | Waktu publikasi, boleh kosong |
| `photo_path` | Lokasi file foto, boleh kosong |
| `created_at` | Waktu data dibuat |
| `updated_at` | Waktu data diperbarui |

### Relasi

Model `FoundItem` memiliki relasi `belongsTo` dengan model `Category` melalui `category_id`.

Penghapusan kategori dibatasi apabila masih digunakan oleh barang temuan (`restrictOnDelete`).

## 4. Alur Status Barang

Barang temuan memiliki tiga status:

| Status | Keterangan |
|---|---|
| `Draft` | Barang baru disimpan dan belum dipublikasikan |
| `Published` | Barang telah dipublikasikan |
| `Archived` | Barang telah diarsipkan |

Transisi status:

- `Draft` → `Published`
- `Published` → `Archived`

Pengelolaan publikasi dan pengarsipan dibatasi untuk Admin.

Transisi yang tidak sesuai dengan aturan status ditolak oleh sistem dengan respons HTTP 409.

## 5. Validasi Barang Temuan

Aturan validasi pada proses penyimpanan:

| Field | Aturan |
|---|---|
| `title` | Wajib diisi, maksimal 255 karakter |
| `description` | Opsional, maksimal 2.000 karakter |
| `category_id` | Wajib berupa integer dan harus tersedia di tabel kategori |
| `found_at` | Wajib berupa tanggal yang valid dan berada dalam rentang MySQL TIMESTAMP |
| `photo` | Opsional, harus berupa gambar dengan format yang diizinkan dan maksimal 2 MB |

Barang baru disimpan dengan status awal `Draft`.

## 6. Penyimpanan Foto Privat

Foto barang temuan disimpan pada disk `local`, bukan pada disk `public`.

Lokasi penyimpanan:

`storage/app/private/found-items/`

Informasi file disimpan pada kolom `photo_path` di tabel `found_items`.

Ketentuan foto:

- JPG
- JPEG
- PNG
- WebP
- Ukuran maksimal 2 MB
- Foto bersifat opsional

Jika proses penyimpanan data barang gagal setelah foto tersimpan, sistem menjalankan mekanisme penghapusan foto untuk mencegah file tertinggal tanpa data barang terkait.

## 7. Pengujian

Pengujian fitur barang temuan berada pada:

`tests/Feature/FoundItemPublicationTest.php`

Jalankan pengujian dengan:

```bash
php artisan test --filter=FoundItemPublicationTest
```

Pengujian mencakup:

- Akses halaman input barang temuan.
- Validasi data barang.
- Penyimpanan barang sebagai Draft.
- Penyimpanan foto JPG dan PNG secara privat.
- Penolakan file bukan gambar.
- Penolakan foto berukuran lebih dari 2 MB.
- Validasi kategori dan deskripsi.
- Validasi tanggal penemuan.
- Pembatasan publikasi dan pengarsipan berdasarkan role.
- Penolakan transisi status yang tidak valid.
- Pembersihan foto privat ketika penyimpanan data barang gagal.

Hasil pengujian terakhir yang telah dijalankan:

24 tests passed, 83 assertions.

## 8. Struktur Folder Penting

| Folder atau file | Fungsi |
|---|---|
| `app/Models/` | Model aplikasi |
| `app/Http/Controllers/` | Controller aplikasi |
| `database/migrations/` | Struktur dan perubahan database |
| `database/seeders/` | Data awal aplikasi |
| `resources/views/` | Tampilan Blade |
| `routes/web.php` | Definisi route aplikasi |
| `tests/Feature/` | Pengujian fitur |
| `storage/app/private/` | Penyimpanan file privat |

## 9. Kontribusi

Proyek FindBack dikembangkan oleh Kelompok 5 Universitas Muhammadiyah Semarang (UNIMUS).

Setiap anggota bertanggung jawab atas modul, backend, halaman, pengujian, dokumentasi, Pull Request, dan demonstrasi sesuai pembagian tugas kelompok.