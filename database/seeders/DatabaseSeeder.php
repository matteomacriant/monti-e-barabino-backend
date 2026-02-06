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

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Create random practices for this user
        $activePractices = \App\Models\Practice::factory(20)->create([
            'user_id' => $user->id,
            'year' => 2026,
            'status' => 'active',
        ]);

        $archivedPractices = \App\Models\Practice::factory(15)->create([
            'user_id' => $user->id,
            'year' => 2025,
            'status' => 'archived',
        ]);

        $otherPractices = \App\Models\Practice::factory(10)->create([
            'user_id' => $user->id,
            'year' => 2024,
            'status' => 'active',
        ]);

        // Attach files to practices
        $allPractices = $activePractices->merge($archivedPractices)->merge($otherPractices);

        foreach ($allPractices as $index => $practice) {
            $rand = rand(1, 100);

            if ($rand <= 30) {
                // No attachments
                continue;
            } elseif ($rand <= 70) {
                // 1 attachment
                \App\Models\Attachment::factory()->create(['practice_id' => $practice->id]);
            } else {
                // Multiple attachments
                \App\Models\Attachment::factory(rand(2, 4))->create(['practice_id' => $practice->id]);
            }
        }

        // Create some random practices for other users
        \App\Models\Practice::factory(10)->create();
    }
}
