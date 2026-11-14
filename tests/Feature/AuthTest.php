<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest can view login page.
     */
    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertViewIs('auth.login');
        $response->assertSee('FindBack UNIMUS');
        $response->assertSee('Email Kampus');
        $response->assertSee('Kata Sandi');
    }

    /**
     * Test already authenticated user is redirected away from login page.
     */
    public function test_authenticated_user_visiting_login_page_is_redirected(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect();
    }

    /**
     * Test valid user can log in and session is regenerated.
     */
    public function test_valid_user_can_login_and_session_is_regenerated(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@unimus.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $this->withSession(['pre_login_key' => 'initial_value']);

        $response = $this->post(route('login'), [
            'email' => 'mahasiswa@unimus.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
        $response->assertSessionHas('pre_login_key', 'initial_value');
    }

    /**
     * Test invalid password is rejected without revealing whether email exists.
     */
    public function test_invalid_password_is_rejected_with_clean_generic_message(): void
    {
        $user = User::factory()->create([
            'email' => 'dosen@unimus.ac.id',
            'password' => Hash::make('correct-password'),
        ]);

        // Attempt with existing email but wrong password
        $responseWrongPassword = $this->post(route('login'), [
            'email' => 'dosen@unimus.ac.id',
            'password' => 'wrong-password',
        ]);

        $responseWrongPassword->assertSessionHasErrors('email');
        $this->assertGuest();

        // Attempt with non-existing email
        $responseWrongEmail = $this->post(route('login'), [
            'email' => 'nonexistent@unimus.ac.id',
            'password' => 'any-password',
        ]);

        $responseWrongEmail->assertSessionHasErrors('email');
        $this->assertGuest();

        // Error message must be identical so account existence is not leaked
        $msg1 = session('errors')->getBag('default')->first('email');
        $this->assertEquals(__('auth.failed'), $msg1);
    }

    /**
     * Test email input is retained when validation fails.
     */
    public function test_email_input_is_retained_on_validation_failure(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'seseorang@unimus.ac.id',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionHasInput('email', 'seseorang@unimus.ac.id');
        $response->assertSessionMissingInput('password');
    }

    /**
     * Test repeated login failures trigger rate limiting (max 5 attempts).
     */
    public function test_invalid_login_is_throttled_after_repeated_failures(): void
    {
        Event::fake([Lockout::class]);

        $email = 'satpam@unimus.ac.id';
        $throttleKey = Str::transliterate(Str::lower($email).'|127.0.0.1');
        RateLimiter::clear($throttleKey);

        // Make 5 failed attempts
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('login'), [
                'email' => $email,
                'password' => 'wrong-password',
            ]);
            $response->assertSessionHasErrors('email');
            $this->assertGuest();
        }

        // The 6th attempt should be blocked by RateLimiter and fire Lockout event
        $responseBlocked = $this->post(route('login'), [
            'email' => $email,
            'password' => 'wrong-password',
        ]);

        $responseBlocked->assertSessionHasErrors('email');
        $errorMessage = session('errors')->getBag('default')->first('email');
        $this->assertStringContainsString('Too many login attempts', $errorMessage);
        $this->assertGuest();

        Event::assertDispatched(Lockout::class, function ($event) use ($email) {
            return Str::lower(trim($event->request->input('email'))) === $email;
        });
    }

    /**
     * Test user can log out and session is cleared.
     */
    public function test_user_can_logout_and_session_is_cleared(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test user logout also clears remember me cookie.
     */
    public function test_user_can_logout_and_remember_cookie_is_cleared(): void
    {
        $user = User::factory()->create([
            'email' => 'logout.test@unimus.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $loginResponse = $this->post(route('login'), [
            'email' => 'logout.test@unimus.ac.id',
            'password' => 'password123',
            'remember' => 'on',
        ]);

        $recallerName = Auth::guard()->getRecallerName();
        $loginResponse->assertCookie($recallerName);
        $rememberCookie = $loginResponse->getCookie($recallerName);

        $logoutResponse = $this->withCookie($recallerName, $rememberCookie->getValue())->post(route('logout'));

        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
        $logoutResponse->assertCookieExpired($recallerName);
    }

    /**
     * Test guest attempting to visit protected route is redirected to /login.
     */
    public function test_guest_attempting_to_visit_protected_route_is_redirected_to_login(): void
    {
        $response = $this->get(route('catalog.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test guest attempting to visit returns route is redirected to login.
     */
    public function test_guest_attempting_to_visit_returns_route_is_redirected_to_login(): void
    {
        $response = $this->get(route('returns.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test authenticated user without an allowed role cannot access returns route.
     */
    public function test_authenticated_user_without_returns_role_is_forbidden(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
        ]);

        $response = $this->actingAs($user)->get(route('returns.index'));

        $response->assertForbidden();
    }

    /**
     * Test authenticated user can access protected catalog route.
     */
    public function test_authenticated_user_can_access_protected_catalog_route(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Katalog Barang Temuan UNIMUS (Terlindungi)');
    }

    /**
     * Test empty email and password fields trigger validation errors.
     */
    public function test_login_requires_email_and_password(): void
    {
        $response = $this->post(route('login'), []);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /**
     * Test invalid email format triggers validation error.
     */
    public function test_login_requires_valid_email_format(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'bukan-email-valid',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test user can log in with uppercase email (case-insensitivity).
     */
    public function test_user_can_login_with_uppercase_email(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa.aktif@unimus.ac.id',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'MAHASISWA.AKTIF@UNIMUS.AC.ID',
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test user can log in with whitespace surrounding email (auto-trimmed).
     */
    public function test_user_can_login_with_trimmed_whitespace_email(): void
    {
        $user = User::factory()->create([
            'email' => 'trim.test@unimus.ac.id',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => "  trim.test@unimus.ac.id  \t",
            'password' => 'secret123',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test remember me sets the remember token cookie.
     */
    public function test_user_can_login_with_remember_me(): void
    {
        $user = User::factory()->create([
            'email' => 'satpam.pos1@unimus.ac.id',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'satpam.pos1@unimus.ac.id',
            'password' => 'secret123',
            'remember' => 'on',
        ]);

        $response->assertRedirect(route('catalog.index'));
        $this->assertAuthenticatedAs($user);
        $response->assertCookie(Auth::guard()->getRecallerName());
    }

    /**
     * Test authenticated user sees logout button and username in navigation.
     */
    public function test_authenticated_user_sees_logout_button_and_username_in_navigation(): void
    {
        $user = User::factory()->create([
            'name' => 'Aldo Prawiro',
        ]);

        $response = $this->actingAs($user)->get(route('catalog.index'));

        $response->assertOk();
        $response->assertSee('Aldo Prawiro');
        $response->assertSee('Keluar');
        $response->assertSee(route('logout'));
    }
}
