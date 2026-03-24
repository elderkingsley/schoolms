<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [

            ['name' => 'Creche & Daycare',   'level' => 'Creche',  'order' => 1],
            ['name' => 'Playgroup',   'level' => 'Playgroup',  'order' => 2],
            ['name' => 'Pre-Nursery',   'level' => 'Pre-Nursery',  'order' => 3],
            ['name' => 'Nursery 1',   'level' => 'Nursery',  'order' => 4],
            ['name' => 'Nursery 2',   'level' => 'Nursery',  'order' => 5],
            ['name' => 'Primary 1',   'level' => 'Primary',  'order' => 6],
            ['name' => 'Primary 2',   'level' => 'Primary',  'order' => 7],
            ['name' => 'Primary 3',   'level' => 'Primary',  'order' => 8],
            ['name' => 'Primary 4',   'level' => 'Primary',  'order' => 9],
            ['name' => 'Primary 5',   'level' => 'Primary',  'order' => 10],
            ['name' => 'Primary 6',   'level' => 'Primary',  'order' => 11],
        ];

        foreach ($classes as $class) {
            SchoolClass::firstOrCreate(['name' => $class['name']], $class);
        }
    }
}
