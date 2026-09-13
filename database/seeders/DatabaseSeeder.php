<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@tafses.local',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);
    }
}
