<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@gopharmacy.ng'],
            [
                'name' => 'Go Pharmacy Admin',
                'password' => 'ChangeMe123!',
                'is_admin' => true,
            ]
        );

        $this->call([
            SettingsSeeder::class,
        ]);
    }
}
