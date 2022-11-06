<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create(['email' => 'user@email.com']);

        \App\Models\File::factory(12)->create(['user_id' => 1]);

        \App\Models\User::factory()->create(['email' => 'user2@email.com']);

        \App\Models\File::factory(6)->create(['user_id' => 2]);
    }
}
