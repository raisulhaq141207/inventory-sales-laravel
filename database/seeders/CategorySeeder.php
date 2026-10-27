<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Elektronik', 'Alat Tulis', 'Perabot', 'Makanan & Minuman', 'Kebersihan'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
