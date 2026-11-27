<?php


use Illuminate\Support\Facades\DB;

test('application runs in the testing environment', function () {
    expect(app()->environment())->toBe('testing');
});

test('testing uses the dedicated PostgreSQL database', function () {
    expect(config('database.default'))->toBe('pgsql');

    $connectionIdentity = DB::connection()->selectOne(
        'SELECT current_database() AS database_name,
                current_user AS username'
    );

    expect($connectionIdentity->database_name)->toBe('university_test');

    expect($connectionIdentity->username)->toBe('university_test');
});
