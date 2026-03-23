<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['name' => 'English Language',    'code' => 'ENG'],
            ['name' => 'Mathematics',          'code' => 'MTH'],
            ['name' => 'Basic Science',        'code' => 'BSC'],
            ['name' => 'Social Studies',       'code' => 'SST'],
            ['name' => 'Civic Education',      'code' => 'CIV'],
            ['name' => 'Christian Religion',   'code' => 'CRS'],
            ['name' => 'Yoruba',               'code' => 'YOR'],
            ['name' => 'Computer Studies',     'code' => 'ICT'],
            ['name' => 'Physical Education',   'code' => 'PHE'],
            ['name' => 'Creative Arts',        'code' => 'ART'],
        ];

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['code' => $subject['code']], $subject);
        }
    }
}
