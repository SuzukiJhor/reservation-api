<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::create([
            'name' => 'Jhordinha ADM',
            'email' => 'admin2@teste.com',
            'password' => Hash::make('senha123'),
            'clerk_user_id' => 'user_35uSVnx2zDqXqLHwdAE9psSAxRB',
        ]);
    }
}
