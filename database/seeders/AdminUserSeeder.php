<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gopharmacy.ng'],
            [
                'name' => 'Go Pharmacy Admin',
                'password' => Hash::make('ChangeMe123!'),
                'is_admin' => true,
            ]
        );
    }
}