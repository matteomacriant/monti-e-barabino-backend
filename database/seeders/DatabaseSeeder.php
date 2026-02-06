<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password', // Factory default usually uses Hash::make('password') or similar, standard Laravel 11 factory is 'password'
            'role' => 'admin', // If I added 'role' column? Directives mentioned "differenziati in base al livello dell'utente" and code used $user->role.
            // Wait, did I add 'role' column to users table? 
            // I ran standard migrations. Does UserFactory have role? 
            // I need to check migration for users table.
        ]);
    }
}
