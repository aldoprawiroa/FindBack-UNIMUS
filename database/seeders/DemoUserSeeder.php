<?php

namespace Database\Seeders;

use App\Models\SecurityPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }

        $password = config('app.demo_user_password');

        if (! is_string($password) || trim($password) === '') {
            throw new RuntimeException('Set DEMO_USER_PASSWORD before seeding demo accounts.');
        }

        if (Str::length($password) < 8) {
            throw new RuntimeException('DEMO_USER_PASSWORD must be at least 8 characters.');
        }

        $demoPost = SecurityPost::firstOrCreate(
            ['name' => 'Pos Gerbang Utama'],
            ['is_active' => true]
        );

        foreach ([
            ['name' => 'Mahasiswa Demo', 'email' => 'mahasiswa@unimus.ac.id', 'role' => 'mahasiswa'],
            ['name' => 'Dosen Demo', 'email' => 'dosen@unimus.ac.id', 'role' => 'dosen'],
            ['name' => 'Admin Demo', 'email' => 'admin@unimus.ac.id', 'role' => 'admin'],
            ['name' => 'Satpam Demo', 'email' => 'security@unimus.ac.id', 'role' => 'security', 'security_post_id' => $demoPost->id],
        ] as $demoUser) {
            User::firstOrCreate(
                ['email' => $demoUser['email']],
                [...$demoUser, 'password' => $password]
            );
        }
    }
}
