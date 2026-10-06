<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RestaurantHoursSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);

        foreach ([
            'admin@kindee.net' => ['Admin Restaurant', 'Admin'],
            'super_admin@kindee.net' => ['Super Admin', 'Super Admin'],
        ] as $email => [$name, $role]) {
            $user = User::withTrashed()->firstOrCreate(
                ['email' => $email],
                User::factory()->withoutTwoFactor()->raw([
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('PQJTt2zEX#tr'),
                ]),
            );

            if ($user->wasRecentlyCreated) {
                $user->assignRole($role);
            }
        }
    }
}
