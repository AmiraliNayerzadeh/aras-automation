<?php

uses(Tests\TestCase::class);

test('tests run against the in-memory database, never a real one', function () {
    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:')
        ->and(app()->environment())->toBe('testing');
});
