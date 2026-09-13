<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Creates or repairs the administrator account. A fresh database has no
        // admin at all, which makes signing in as one impossible — see
        // AdminUserSeeder for the optional ADMIN_NAME / ADMIN_EMAIL /
        // ADMIN_PASSWORD overrides.
        $this->call([
            AdminUserSeeder::class,
        ]);
    }
}
