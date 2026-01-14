<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
    // return response()->json("ola mundo");
    // return view('welcome');
})->middleware('auth:sanctum');
