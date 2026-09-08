<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@gopharmacy.ng');
        $name = (string) env('ADMIN_NAME', 'Go Pharmacy Admin');
        $password = env('ADMIN_PASSWORD');

        if (blank($password)) {
            throw new RuntimeException(
                'ADMIN_PASSWORD must be set before the initial admin user can be seeded.'
            );
        }

        User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );
    }
}
