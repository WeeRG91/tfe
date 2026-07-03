<?php

namespace Database\Seeders;

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
        User::factory()
            ->withoutTwoFactor()
            ->create([
            'name' => 'Admin Restaurant',
            'email' => 'admin@example.com',
        ]);

        User::factory()
            ->withoutTwoFactor()
            ->create([
            'name' => 'Super Admin',
            'email' => 'super_admin@example.com',
        ]);

        $this->call([RolesAndPermissionsSeeder::class]);
    }
}
