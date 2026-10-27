<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    // Akun DEMO saja (bukan kredensial asli).
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@inventory.test'], [
            'name' => 'Admin Demo', 'password' => 'password', 'role' => 'admin',
        ]);

        User::updateOrCreate(['email' => 'staff@inventory.test'], [
            'name' => 'Staff Demo', 'password' => 'password', 'role' => 'staff',
        ]);
    }
}
