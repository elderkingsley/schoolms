<?php

namespace Database\Seeders;

use App\Models\FeeItem;
use Illuminate\Database\Seeder;

class FeeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Compulsory items first, in the order they should appear on invoices
            ['name' => 'Tuition Fees',                'type' => 'compulsory', 'sort_order' => 1,  'description' => 'Term tuition fee'],
            ['name' => 'Registration Fee',            'type' => 'compulsory', 'sort_order' => 2,  'description' => 'One-time registration fee per term'],
            ['name' => 'Development Levy',            'type' => 'compulsory', 'sort_order' => 3,  'description' => 'School development and infrastructure levy'],
            ['name' => 'Learning Materials',          'type' => 'compulsory', 'sort_order' => 4,  'description' => 'Textbooks, workbooks and stationery'],
            ['name' => 'Diction',                     'type' => 'compulsory', 'sort_order' => 5,  'description' => 'Speech and diction classes'],
            ['name' => 'Result Uploads',              'type' => 'compulsory', 'sort_order' => 6,  'description' => 'Online result portal access fee'],
            // Optional items follow
            ['name' => 'After School Care',           'type' => 'optional',   'sort_order' => 7,  'description' => 'Extended after-school supervision'],
            ['name' => 'Uniforms',                    'type' => 'optional',   'sort_order' => 8,  'description' => 'School uniform supply'],
            ['name' => 'Extra-Curricular Activities', 'type' => 'optional',   'sort_order' => 9,  'description' => 'Sports, arts and clubs'],
            ['name' => 'End of Term Events',          'type' => 'optional',   'sort_order' => 10, 'description' => 'Graduation, concerts and prize giving'],
        ];

        foreach ($items as $item) {
            FeeItem::firstOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
