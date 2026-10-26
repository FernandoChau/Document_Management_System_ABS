<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

it('stores recovery codes encrypted and decrypts them for authentication checks', function () {
    if (! extension_loaded('pdo_sqlite')) {
        $this->markTestSkipped('The pdo_sqlite extension is required for database integration tests.');
    }

    $this->artisan('migrate:fresh');

    $recoveryCodes = json_encode([['code' => 'one-time-code', 'used_at' => null]]);
    $user = User::create([
        'name' => 'Recovery Test',
        'email' => 'recovery@example.test',
        'two_factor_recovery_codes' => $recoveryCodes,
    ]);

    $storedValue = DB::table('users')->where('id', $user->id)->value('two_factor_recovery_codes');

    expect($storedValue)->not->toBe($recoveryCodes)
        ->and($user->fresh()->two_factor_recovery_codes)->toBe($recoveryCodes);
});
