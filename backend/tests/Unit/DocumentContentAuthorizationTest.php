<?php

use App\Exceptions\PermissionDeniedException;
use App\Http\Controllers\Api\DocumentContentController;
use App\Models\Document;
use App\Models\User;
use App\Services\PermissionValidator;
use Illuminate\Http\Request;

it('denies reading extracted document content without view permission', function () {
    $permissionValidator = Mockery::mock(PermissionValidator::class);
    $permissionValidator
        ->shouldReceive('validateDocumentAction')
        ->once()
        ->andThrow(new PermissionDeniedException('Forbidden'));

    $controller = new DocumentContentController($permissionValidator);
    $request = Request::create('/');
    $request->setUserResolver(fn () => new User);
    $response = $controller->show($request, new Document);

    expect($response->getStatusCode())->toBe(403);
});

it('denies modifying extracted document content without update permission', function () {
    $permissionValidator = Mockery::mock(PermissionValidator::class);
    $permissionValidator
        ->shouldReceive('validateDocumentAction')
        ->once()
        ->andThrow(new PermissionDeniedException('Forbidden'));

    $controller = new DocumentContentController($permissionValidator);
    $request = Request::create('/');
    $request->setUserResolver(fn () => new User);
    $response = $controller->destroy($request, new Document);

    expect($response->getStatusCode())->toBe(403);
});
