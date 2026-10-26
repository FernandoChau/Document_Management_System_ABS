<?php

it('allows credentialed CORS preflight from a configured origin', function () {
    $this->withHeader('Origin', 'http://localhost:5173')
        ->options('/api/entrar')
        ->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
        ->assertHeader('Access-Control-Allow-Credentials', 'true');
});

it('rejects preflight from an unconfigured origin', function () {
    $this->withHeader('Origin', 'https://untrusted.example')
        ->options('/api/entrar')
        ->assertForbidden()
        ->assertJsonPath('message', 'Origin not allowed');
});

it('rejects state-changing requests from an unconfigured origin', function () {
    $this->withHeader('Origin', 'https://untrusted.example')
        ->post('/api/entrar')
        ->assertForbidden()
        ->assertJsonPath('message', 'Origin not allowed');
});
