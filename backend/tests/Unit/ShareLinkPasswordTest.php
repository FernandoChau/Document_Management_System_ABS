<?php

use App\Models\ShareLink;
use Illuminate\Support\Facades\Hash;

it('checks share passwords using Laravel password hashes', function () {
    $shareLink = new ShareLink;
    $shareLink->password = 'correct horse battery staple';
    $shareLink->syncOriginal();

    expect($shareLink->checkPassword('correct horse battery staple'))->toBeTrue()
        ->and($shareLink->checkPassword('incorrect'))->toBeFalse()
        ->and(Hash::check('correct horse battery staple', $shareLink->getRawOriginal('password')))->toBeTrue();
});

it('accepts existing SHA-256 share passwords and upgrades them', function () {
    $shareLink = new ShareLink;
    $legacyHash = hash('sha256', 'legacy-share-password');
    $shareLink->setRawAttributes(['password' => $legacyHash]);
    $shareLink->syncOriginal();

    expect($shareLink->checkPassword('legacy-share-password'))->toBeTrue()
        ->and(Hash::check('legacy-share-password', $shareLink->password))->toBeTrue();
});
