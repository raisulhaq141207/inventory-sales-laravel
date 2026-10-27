<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['PT Sinar Elektronik', '0651-700111', 'sales@sinar-elektronik.test'],
            ['CV Mitra ATK', '0651-700222', 'order@mitra-atk.test'],
            ['UD Gayo Jaya', '0651-700333', 'gayojaya@example.test'],
        ];

        foreach ($suppliers as [$name, $phone, $email]) {
            Supplier::firstOrCreate(['name' => $name], ['phone' => $phone, 'email' => $email]);
        }
    }
}
