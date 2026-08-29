<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Note: this deliberately does NOT use WithoutModelEvents — the
     * HasUuid trait generates every primary key from a `creating` model
     * event, so seeding needs those events to fire.
     */
    public function run(): void
    {
        $this->call(QueueLessSeeder::class);
    }
}
