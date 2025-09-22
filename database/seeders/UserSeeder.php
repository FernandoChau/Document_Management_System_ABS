<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create(attributes: [
            'name' => 'Conta Admin',
            'email' => 'admin@gmail.com',
            'phone' => '840000001',
            'role' => 'admin',
            'is_activated' => true,
            'activated_by' => 1,
            'created_by' => 1,
            'profession' => 'Administrador',
            'activated_at' => now(),
            'password' => bcrypt('1234567890'),
        ]);
        User::create(attributes: [
            'name' => 'Conta User',
            'email' => 'user@gmail.com',
            'phone' => '840000002',
            'role' => 'user',
            'is_activated' => true,
            'activated_by' => 1,
            'created_by' => 1,
            'profession' => 'Utilizador',
            'activated_at' => now(),
            'password' => bcrypt('1234567890'),
        ]);
    }
}
