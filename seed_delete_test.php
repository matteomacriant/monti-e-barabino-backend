<?php

use App\Models\User;
use App\Models\Practice;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

// Ensure users exist
$admin = User::firstOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Admin User', 'password' => Hash::make('password'), 'role' => 'admin']
);

$user = User::firstOrCreate(
    ['email' => 'dbeier@example.com'],
    ['name' => 'David Beier', 'password' => Hash::make('password'), 'role' => 'user']
);

echo "Admin ID: " . $admin->id . "\n";
echo "User ID: " . $user->id . "\n";

// Create practices for testing

// 1. User's specific practice created NOW (deletable by User)
$p1 = Practice::create([
    'code' => 'DEL-USER-NEW',
    'title' => 'User New Practice',
    'user_id' => $user->id,
    'created_at' => Carbon::now(),
    'updated_at' => Carbon::now(),
    'year' => 2026,
    'month' => 2
]);

// 2. User's old practice > 24h (NOT deletable by User)
$p2 = Practice::create([
    'code' => 'DEL-USER-OLD',
    'title' => 'User Old Practice',
    'user_id' => $user->id,
    'created_at' => Carbon::now()->subHours(25),
    'updated_at' => Carbon::now()->subHours(25),
    'year' => 2026,
    'month' => 2
]);

// 3. User's old practice > 24h (Deletable by ADMIN)
$p3 = Practice::create([
    'code' => 'DEL-ADMIN-test',
    'title' => 'User Old Practice For Admin',
    'user_id' => $user->id,
    'created_at' => Carbon::now()->subHours(25),
    'updated_at' => Carbon::now()->subHours(25),
    'year' => 2026,
    'month' => 2
]);

echo "Created Practices:\n";
echo "1. NEW (User): " . $p1->id . " code: " . $p1->code . "\n";
echo "2. OLD (User): " . $p2->id . " code: " . $p2->code . "\n";
echo "3. OLD (Admin): " . $p3->id . " code: " . $p3->code . "\n";
