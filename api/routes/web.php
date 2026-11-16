<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docker-test', function () {
    abort_unless(app()->isLocal(), 404);

    return response()->json([
        'message' => 'Laravel Docker Sync works.'
        'data' => array([
            'name' => 'John Doe',
        ]),
        'status' => 'success',
    ]);
});
