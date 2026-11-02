<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');

        if (blank($email)) {
            throw new RuntimeException('ADMIN_EMAIL belum diisi di .env.');
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($this->password()),
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Password admin dari .env; fallback hanya diizinkan pada environment lokal.
     */
    private function password(): string
    {
        $password = env('ADMIN_PASSWORD');

        if (filled($password)) {
            return $password;
        }

        if (! app()->isLocal()) {
            throw new RuntimeException(
                'ADMIN_PASSWORD belum diisi. Fallback password hanya tersedia saat app()->isLocal().'
            );
        }

        return 'password';
    }
}
