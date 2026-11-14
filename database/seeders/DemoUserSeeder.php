<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Demo accounts can only be seeded in local or testing environments.');
        }

        $password = config('app.demo_user_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set DEMO_USER_PASSWORD before seeding demo accounts.');
        }

        foreach ([
            ['name' => 'Mahasiswa Demo', 'email' => 'mahasiswa@unimus.ac.id', 'role' => 'mahasiswa'],
            ['name' => 'Dosen Demo', 'email' => 'dosen@unimus.ac.id', 'role' => 'dosen'],
            ['name' => 'Admin Demo', 'email' => 'admin@unimus.ac.id', 'role' => 'admin'],
        ] as $demoUser) {
            User::firstOrCreate(
                ['email' => $demoUser['email']],
                [...$demoUser, 'password' => $password]
            );
        }
    }
}