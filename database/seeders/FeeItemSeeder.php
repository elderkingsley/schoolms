<?php

namespace Database\Seeders;

use App\Models\FeeItem;
use Illuminate\Database\Seeder;

class FeeItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Registration Fee',          'type' => 'compulsory', 'description' => 'One-time registration fee per term'],
            ['name' => 'Tuition Fees',               'type' => 'compulsory', 'description' => 'Term tuition fee'],
            ['name' => 'Development Levy',           'type' => 'compulsory', 'description' => 'School development and infrastructure levy'],
            ['name' => 'Diction',                    'type' => 'compulsory', 'description' => 'Speech and diction classes'],
            ['name' => 'After School Care',          'type' => 'optional',   'description' => 'Extended after-school supervision'],
            ['name' => 'Result Uploads',             'type' => 'compulsory', 'description' => 'Online result portal access fee'],
            ['name' => 'Uniforms',                   'type' => 'optional',   'description' => 'School uniform supply'],
            ['name' => 'Learning Materials',         'type' => 'compulsory', 'description' => 'Textbooks, workbooks and stationery'],
            ['name' => 'Extra-Curricular Activities','type' => 'optional',   'description' => 'Sports, arts and clubs'],
            ['name' => 'End of Term Events',         'type' => 'optional',   'description' => 'Graduation, concerts and prize giving'],
        ];

        foreach ($items as $item) {
            FeeItem::firstOrCreate(
                ['name' => $item['name']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
