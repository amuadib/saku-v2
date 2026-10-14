<?php

use App\Livewire\Siswa\Profil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('magic link token is 16 characters and backwards compatible without salt', function () {
    $user = User::factory()->create([
        'magic_token_salt' => null,
    ]);

    $legacyToken = substr(hash_hmac('sha256', "magic-login:{$user->id}:{$user->username}", config('custom.api_akademik.token') ?: config('app.key')), 0, 16);

    expect(strlen($user->getMagicToken()))->toBe(16);
    expect($user->getMagicToken())->toBe($legacyToken);
    expect($user->isValidMagicToken($legacyToken))->toBeTrue();
});

test('magic link token changes after regeneration and invalidates old token', function () {
    $user = User::factory()->create([
        'magic_token_salt' => null,
    ]);

    $oldToken = $user->getMagicToken();

    $newToken = $user->regenerateMagicToken();

    expect(strlen($newToken))->toBe(16);
    expect($newToken)->not->toBe($oldToken);
    expect($user->isValidMagicToken($oldToken))->toBeFalse();
    expect($user->isValidMagicToken($newToken))->toBeTrue();
});

test('user can login using valid magic link route', function () {
    $user = User::factory()->create();

    $token = $user->getMagicToken();

    $response = $this->get("/m/{$user->username}/{$token}");

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('login fails with invalid or old magic link token', function () {
    $user = User::factory()->create();

    $oldToken = $user->getMagicToken();
    $user->regenerateMagicToken();

    $response = $this->get("/m/{$user->username}/{$oldToken}");

    $response->assertStatus(401);
    $this->assertGuest();
});

test('siswa can regenerate magic link from profil and get redirected to new link', function () {
    $user = User::factory()->create();

    $oldToken = $user->getMagicToken();

    Livewire::actingAs($user)
        ->test(Profil::class)
        ->call('confirmRegenerate')
        ->assertSet('showConfirmModal', true)
        ->call('regenerateAndRedirect')
        ->assertRedirect($user->fresh()->getMagicLoginUrl());

    expect($user->fresh()->getMagicToken())->not->toBe($oldToken);
});
