<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Dennise Mcshine',
            'email' => 'dennise@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Astrid Chunaco',
            'email' => 'astrid@example.com',
            'password' => Hash::make('password'),
        ]);
    }
}
