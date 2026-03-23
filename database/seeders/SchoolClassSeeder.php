<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['name' => 'Nursery 1',   'level' => 'Nursery',  'order' => 1],
            ['name' => 'Nursery 2',   'level' => 'Nursery',  'order' => 2],
            ['name' => 'Primary 1',   'level' => 'Primary',  'order' => 3],
            ['name' => 'Primary 2',   'level' => 'Primary',  'order' => 4],
            ['name' => 'Primary 3',   'level' => 'Primary',  'order' => 5],
            ['name' => 'Primary 4',   'level' => 'Primary',  'order' => 6],
            ['name' => 'Primary 5',   'level' => 'Primary',  'order' => 7],
            ['name' => 'Primary 6',   'level' => 'Primary',  'order' => 8],
        ];

        foreach ($classes as $class) {
            SchoolClass::firstOrCreate(['name' => $class['name']], $class);
        }
    }
}
