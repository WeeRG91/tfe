<?php

use App\Models\RestaurantHour;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('can rerun default seeders without duplicating data', function () {
    $this->seed(DatabaseSeeder::class);
    $counts = [User::count(), Role::count(), Permission::count(), RestaurantHour::count()];

    $this->seed(DatabaseSeeder::class);

    expect([User::count(), Role::count(), Permission::count(), RestaurantHour::count()])->toBe($counts);
    expect(User::count())->toBe(2);
    expect(User::where('email', 'admin@kindee.net')->first()->hasRole('Admin'))->toBeTrue();
    expect(User::where('email', 'super_admin@kindee.net')->first()->hasRole('Super Admin'))->toBeTrue();
    $this->assertDatabaseCount('restaurant_hour_periods', 7);
});

it('preserves edited accounts permissions roles and opening hours on reruns', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::where('email', 'admin@kindee.net')->firstOrFail();
    $user->update(['name' => 'Updated admin', 'password' => 'changed-password']);
    $password = $user->fresh()->password;
    $customRole = Role::findOrCreate('Custom role', 'web');
    $user->syncRoles([$customRole]);
    $role = Role::findByName('Admin', 'web');
    $role->syncPermissions(['admin.access']);
    $day = RestaurantHour::firstOrFail();
    $day->periods()->delete();
    $closedDay = RestaurantHour::where('id', '!=', $day->id)->firstOrFail();
    $closedDay->update(['is_open' => false]);
    $period = RestaurantHour::whereNotIn('id', [$day->id, $closedDay->id])->firstOrFail()->periods()->firstOrFail();
    $period->update(['opens_at' => '12:30:00']);

    $this->seed(DatabaseSeeder::class);

    expect($user->fresh()->name)->toBe('Updated admin');
    expect($user->fresh()->password)->toBe($password);
    expect($user->fresh()->getRoleNames()->all())->toBe(['Custom role']);
    expect($role->fresh()->permissions->pluck('name')->all())->toBe(['admin.access']);
    expect($day->periods()->count())->toBe(0);
    expect($closedDay->fresh()->is_open)->toBeFalse();
    expect($period->fresh()->opens_at)->toBe('12:30:00');
});

it('creates missing defaults and permissions without changing existing records', function () {
    $this->seed(DatabaseSeeder::class);
    $day = RestaurantHour::firstOrFail();
    $weekday = $day->weekday->value;
    $day->periods()->delete();
    $day->delete();
    Permission::findByName('admin.access', 'web')->delete();

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseHas('restaurant_hours', ['weekday' => $weekday]);
    $this->assertDatabaseHas('permissions', ['name' => 'admin.access', 'guard_name' => 'web']);
    $this->assertDatabaseCount('restaurant_hour_periods', 7);
});

it('does not recreate or restore deleted seeded accounts', function () {
    $this->seed(DatabaseSeeder::class);
    User::where('email', 'admin@kindee.net')->firstOrFail()->delete();

    $this->seed(DatabaseSeeder::class);

    expect(User::withTrashed()->count())->toBe(2);
    expect(User::where('email', 'admin@kindee.net')->count())->toBe(0);
});
