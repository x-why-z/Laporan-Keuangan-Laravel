<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $ownerRole = Role::firstOrCreate(['name' => 'owner']);

        // Create owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@mutiararzki.com'],
            [
                'name' => 'Owner Mutiara Rizki',
                'password' => Hash::make('password'),
            ]
        );
        $owner->roles()->syncWithoutDetaching([$ownerRole->id]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@mutiararzki.com'],
            [
                'name' => 'Admin Mutiara Rizki',
                'password' => Hash::make('password'),
            ]
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }
}
