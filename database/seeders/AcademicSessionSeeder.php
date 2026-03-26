<?php

namespace Database\Seeders;

use App\Models\AcademicSession;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicSessionSeeder extends Seeder
{
    public function run(): void
    {
        $session = AcademicSession::firstOrCreate(
            ['name' => '2025/2026'],
            ['is_active' => true]
        );

        $terms = [
            ['name' => 'First',  'is_active' => false],
            ['name' => 'Second', 'is_active' => true],
            ['name' => 'Third',  'is_active' => false],
        ];

        foreach ($terms as $term) {
            Term::firstOrCreate(
                ['academic_session_id' => $session->id, 'name' => $term['name']],
                ['is_active' => $term['is_active']]
            );
        }
    }
}
