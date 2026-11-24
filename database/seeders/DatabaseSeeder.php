<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the central database and the demo tenants.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@blogs.test'],
            ['name' => 'Platform Admin', 'password' => 'password'],
        );

        $this->call(DemoTenantsSeeder::class);
    }
}
