<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits(): array
    {
        $this->ensureSafeTestingEnvironment();

        return parent::setUpTraits();
    }

    private function ensureSafeTestingEnvironment(): void
    {
        if (! $this->app->environment('testing')) {
            throw new RuntimeException(
                'Unsafe testing environment: APP_ENV must be testing.'
            );
        }

        if ($this->app->configurationIsCached()) {
            throw new RuntimeException(
                'Unsafe testing environment: configuration must not be cached.'
            );
        }

        if (config('database.default') !== 'pgsql') {
            throw new RuntimeException(
                'Testing requires a PostgreSQL connection.'
            );
        }

        if (
            config('database.connections.pgsql.database') !== 'university_test'
            || config('database.connections.pgsql.username') !== 'university_test'
            || ! empty(config('database.connections.pgsql.url'))
        ) {
            throw new RuntimeException(
                'Unsafe database configuration: tests must use university_test.'
            );
        }

        $connectionIdentity = DB::connection()->selectOne(
            'SELECT current_database() AS database_name,
                    current_user AS username'
        );

        if (
            $connectionIdentity->database_name !== 'university_test'
            || $connectionIdentity->username !== 'university_test'
        ) {
            throw new RuntimeException(
                'Unsafe database connection: unexpected database or username.'
            );
        }
    }
}
