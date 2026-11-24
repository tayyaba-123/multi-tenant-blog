<?php

use Illuminate\Foundation\Testing\DatabaseTruncation;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Feature tests truncate the central database instead of wrapping each test
| in a transaction: creating a tenant runs CREATE DATABASE, which MySQL
| commits implicitly, so a rollback could never undo it.
|
| Tests under Feature/Tenant start inside the context of a fresh tenant.
|
*/

pest()->extend(TestCase::class)
    ->use(DatabaseTruncation::class)
    ->in('Feature');

pest()->beforeEach(function () {
    $this->initializeTenant();
})->in('Feature/Tenant');
