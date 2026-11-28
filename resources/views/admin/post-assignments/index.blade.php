@extends('layouts.app')

@section('title', 'Penugasan Pos Admin & Satpam - FindBack')

@section('content')
<div class="container">
    <h1>Penugasan Pos Admin &amp; Satpam</h1>
    <p>Atur penugasan pos keamanan aktif untuk akun admin dan petugas satpam.</p>

    <form method="GET" action="{{ route('admin.post-assignments.index') }}">
        <label for="filter_role">Filter Peran:</label>
        <select name="role" id="filter_role">
            <option value="">Semua Staf</option>
            @foreach (\App\Models\User::STAFF_ROLES as $roleOption)
                <option value="{{ $roleOption }}" {{ ($selectedRole ?? null) === $roleOption ? 'selected' : '' }}>
                    {{ ucfirst($roleOption) }}
                </option>
            @endforeach
        </select>
        <button type="submit">Filter</button>
    </form>

    @if (session('success'))
        <p role="status">{{ session('success') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    @if ($staffUsers->isEmpty())
        <p>Belum ada akun admin atau petugas keamanan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Pos Saat Ini</th>
                    <th>Ubah Penugasan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($staffUsers as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>{{ ucfirst($staff->role) }}</td>
                        <td>
                            @if ($staff->securityPost)
                                {{ $staff->securityPost->name }}@if (! $staff->securityPost->is_active) (Nonaktif)@endif
                            @else
                                Belum ditugaskan
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.post-assignments.update', $staff) }}">
                                @csrf
                                @method('PATCH')

                                <label for="security_post_id_{{ $staff->id }}" class="sr-only">
                                    Pos untuk {{ $staff->name }}
                                </label>
                                <select name="security_post_id" id="security_post_id_{{ $staff->id }}">
                                    <option value="">-- Belum Ditugaskan --</option>
                                    @foreach ($securityPosts as $post)
                                        <option value="{{ $post->id }}"
                                            {{ $staff->isAssignedTo($post) ? 'selected' : '' }}>
                                            {{ $post->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
