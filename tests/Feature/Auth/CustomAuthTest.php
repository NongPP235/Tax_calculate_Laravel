<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

test('registration stores a hashed password and normalizes email', function () {
    $this->post(route('register.store'), [
        'name' => 'Student', 'email' => 'Student@Example.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertSessionHasNoErrors()->assertRedirect('/')
        ->assertSessionHas('success', 'สมัครสมาชิกเรียบร้อยแล้ว');

    $user = User::where('email', 'student@example.com')->firstOrFail();
    expect($user->password)->not->toBe('password123');
    expect(Hash::check('password123', $user->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('registration rejects duplicate emails and mismatched passwords', function () {
    User::factory()->create(['email' => 'student@example.com']);

    $this->from('/register')->post(route('register.store'), [
        'name' => 'Student', 'email' => 'STUDENT@example.com',
        'password' => 'password123', 'password_confirmation' => 'different123',
    ])->assertRedirect('/register')->assertSessionHasErrors(['email', 'password']);

    $this->assertDatabaseCount('users', 1);
    $this->assertGuest();
});

test('registration errors preserve name and email but do not flash passwords', function () {
    $this->followingRedirects()->from('/register')->post(route('register.store'), [
        'name' => 'Student', 'email' => 'student@example.com',
        'password' => 'short', 'password_confirmation' => 'short',
    ])->assertOk()->assertSee('รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร')
        ->assertSee('value="Student"', false)->assertSee('value="student@example.com"', false);

    $this->assertNull(session()->getOldInput('password'));
    $this->assertNull(session()->getOldInput('password_confirmation'));
});

test('required registration and login fields are checked', function () {
    $this->post(route('register.store'), [])->assertSessionHasErrors(['name', 'email', 'password']);
    $this->post(route('login.auth'), [])->assertSessionHasErrors(['email', 'password']);
    $this->assertGuest();
});

test('login accepts normalized email remembers users and changes session id', function () {
    $user = User::factory()->create(['email' => 'student@example.com']);
    $this->get(route('login'));
    $oldSession = session()->getId();

    $this->post(route('login.auth'), [
        'email' => 'STUDENT@example.com', 'password' => 'password', 'remember' => 'on',
    ])->assertRedirect('/')->assertCookie(Auth::guard()->getRecallerName())
        ->assertSessionHas('success', 'เข้าสู่ระบบเรียบร้อยแล้ว');

    $this->assertAuthenticatedAs($user);
    expect(session()->getId())->not->toBe($oldSession);
});

test('login redirects back to the protected page after authentication', function () {
    Route::get('/auth-test-private', function () { return 'private'; })->middleware(['web', 'auth']);
    $user = User::factory()->create();

    $this->get('/auth-test-private')->assertRedirect(route('login'));
    $this->post(route('login.auth'), [
        'email' => $user->email, 'password' => 'password',
    ])->assertRedirect('/auth-test-private');
});

test('sixth login attempt is blocked even when its password is correct', function () {
    $user = User::factory()->create();
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $this->from('/login')->post(route('login.auth'), [
            'email' => $user->email, 'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    $response = $this->from('/login')->post(route('login.auth'), [
        'email' => $user->email, 'password' => 'password',
    ]);
    $response->assertRedirect('/login')->assertSessionHasErrors('email');
    expect(session('errors')->first('email'))->toContain('หลายครั้งเกินไป');
    $this->assertGuest();
});

test('authenticated users cannot revisit login or register', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('login'))->assertRedirect(route('dashboard'));
    $this->get(route('register'))->assertRedirect(route('dashboard'));
    $this->post(route('register.store'), [])->assertRedirect(route('dashboard'));
});

test('logout clears authentication session data and changes csrf token', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->withSession(['private-data' => 'tax-data', '_token' => 'old-token'])
        ->post(route('logout'))->assertRedirect('/')
        ->assertSessionMissing('private-data')
        ->assertSessionHas('success', 'ออกจากระบบเรียบร้อยแล้ว');

    $this->assertGuest();
    expect(session()->token())->not->toBe('old-token');
});

test('guests cannot logout and logout is a post action', function () {
    $this->post(route('logout'))->assertRedirect(route('login'));
    $this->get('/logout')->assertStatus(405);
});

test('confirmed two factor authentication must finish before login', function () {
    $user = User::factory()->withTwoFactor()->create();
    $this->post(route('login.auth'), [
        'email' => $user->email, 'password' => 'password',
    ])->assertRedirect(route('two-factor.login'))->assertSessionHas('login.id', $user->id);
    $this->assertGuest();

    $this->get(route('two-factor.login'))->assertOk();
    $this->post(route('two-factor.login.store'), ['recovery_code' => 'recovery-code-1'])
        ->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('flash messages are displayed once on the home page', function () {
    $this->followingRedirects()->from('/register')->post(route('register.store'), [
        'name' => 'Student', 'email' => 'student@example.com',
        'password' => 'password123', 'password_confirmation' => 'password123',
    ])->assertOk()->assertSee('สมัครสมาชิกเรียบร้อยแล้ว');

    $this->get(route('home'))->assertOk()->assertDontSee('สมัครสมาชิกเรียบร้อยแล้ว');
});
