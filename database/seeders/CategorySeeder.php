<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Salary', 'type' => Category::TYPE_INCOME],
            ['name' => 'Other Income', 'type' => Category::TYPE_INCOME],
            ['name' => 'Family Expense', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Transport Expense', 'type' => Category::TYPE_EXPENSE],
            ['name' => 'Meal Expense', 'type' => Category::TYPE_EXPENSE],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category['name']], $category);
        }
    }
}
