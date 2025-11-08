<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        if (class_exists(Role::class)) {
            Role::firstOrCreate(['name' => 'admin']);
            Role::firstOrCreate(['name' => 'guru']);
        }

        // Assign admin role to the first user (example)
        $user = User::first();
        if ($user && method_exists($user, 'assignRole')) {
            $user->assignRole('admin');
        }
    }
}
