<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Deportes',
                'color' => '#3b82f6', // blue-500
                'icon' => 'TbTrophy',
            ],
            [
                'name' => 'Música',
                'color' => '#8b5cf6', // violet-500
                'icon' => 'TbMusic',
            ],
            [
                'name' => 'Teatro',
                'color' => '#ec4899', // pink-500
                'icon' => 'TbMasksTheater',
            ],
            [
                'name' => 'Educación',
                'color' => '#10b981', // emerald-500
                'icon' => 'TbBook',
            ],
            [
                'name' => 'Gastronomía',
                'color' => '#f59e0b', // amber-500
                'icon' => 'TbToolsKitchen2',
            ],
            [
                'name' => 'Tecnología',
                'color' => '#6366f1', // indigo-500
                'icon' => 'TbDeviceLaptop',
            ],
            [
                'name' => 'Arte y Cultura',
                'color' => '#ef4444', // red-500
                'icon' => 'TbPalette',
            ],
            [
                'name' => 'Negocios',
                'color' => '#64748b', // slate-500
                'icon' => 'TbBriefcase',
            ],
        ];

        foreach ($categories as $category) {
            \Domain\EventManagement\Models\EventCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
