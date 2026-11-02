<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_register_tidak_tersedia(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_route_register_tidak_terdaftar(): void
    {
        $this->assertFalse(Route::has('register'));
    }

    public function test_post_register_tidak_tersedia(): void
    {
        $this->post('/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertNotFound();

        $this->assertDatabaseCount('users', 0);
    }
}
