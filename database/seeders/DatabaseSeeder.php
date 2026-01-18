<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $role= Role::findOrCreate( "admin",'web_admin');
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'admin@admin.com',
            'password' => '12345678',
        ])->assignRole($role);
    }
}
