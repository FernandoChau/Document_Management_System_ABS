<?php

use App\Models\User;

it('never serializes password or two-factor secrets', function () {
    $user = new User;
    $user->forceFill([
        'id' => '4bd579ad-3055-45b6-983c-e670e8a76b64',
        'name' => 'Example User',
        'email' => 'example@example.com',
        'password' => 'secret-password',
        'remember_token' => 'remember-token',
        'two_factor_secret' => 'encrypted-secret',
        'two_factor_recovery_codes' => json_encode([['code' => 'recovery-code', 'used_at' => null]]),
    ]);

    expect($user->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token')
        ->not->toHaveKey('two_factor_secret')
        ->not->toHaveKey('two_factor_recovery_codes');
});

it('encrypts recovery codes when stored on the user model', function () {
    $recoveryCodes = json_encode([['code' => 'recovery-code', 'used_at' => null]]);
    $user = new User;
    $user->two_factor_recovery_codes = $recoveryCodes;

    expect($user->getAttributes()['two_factor_recovery_codes'])->not->toBe($recoveryCodes)
        ->and($user->two_factor_recovery_codes)->toBe($recoveryCodes);
});
