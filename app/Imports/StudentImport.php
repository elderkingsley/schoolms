<?php

namespace App\Imports;

use App\Models\AcademicSession;
use App\Models\Enrolment;
use App\Models\ParentGuardian;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ParentWelcomeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use League\Csv\Reader;

class StudentImport
{
    public array $errors  = [];
    public int   $imported = 0;

    public function import(string $filePath): void
    {
        $csv    = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0); // first row = headers

        $session = AcademicSession::current();

        foreach ($csv->getRecords() as $row => $record) {
            try {
                $this->processRow($record, $row + 2, $session); // +2 = 1 header + 0-indexed
            } catch (\Exception $e) {
                $this->errors[] = "Row {$row}: " . $e->getMessage();
            }
        }
    }

    protected function processRow(array $record, int $row, ?AcademicSession $session): void
    {
        // Expected CSV columns:
        // first_name, last_name, other_name, gender, date_of_birth,
        // class_name, admission_number,
        // parent_name, parent_email, parent_phone, parent_relationship

        $admNo = trim($record['admission_number'] ?? '');
        if (!$admNo) {
            $this->errors[] = "Row {$row}: admission_number is required";
            return;
        }

        // Create or update student
        $student = Student::updateOrCreate(
            ['admission_number' => $admNo],
            [
                'first_name'    => trim($record['first_name']    ?? ''),
                'last_name'     => trim($record['last_name']     ?? ''),
                'other_name'    => trim($record['other_name']    ?? ''),
                'gender'        => trim($record['gender']        ?? 'Male'),
                'date_of_birth' => trim($record['date_of_birth'] ?? now()->toDateString()),
                'status'        => 'active',
            ]
        );

        // Enrol in class if class_name provided
        if (!empty($record['class_name']) && $session) {
            $class = SchoolClass::where('name', trim($record['class_name']))->first();
            if ($class) {
                Enrolment::firstOrCreate(
                    ['student_id' => $student->id, 'academic_session_id' => $session->id],
                    ['school_class_id' => $class->id, 'enrolled_at' => now(), 'status' => 'active']
                );
            }
        }

        // Create parent if email provided
        $parentEmail = trim($record['parent_email'] ?? '');
        if ($parentEmail) {
            $user = User::firstOrCreate(
                ['email' => $parentEmail],
                [
                    'name'      => trim($record['parent_name'] ?? 'Parent'),
                    'password'  => Hash::make($tempPass = Str::random(10)),
                    'user_type' => 'parent',
                    'is_active' => true,
                ]
            );

            if ($user->wasRecentlyCreated) {
                $user->assignRole('parent');
            }

            $parent = ParentGuardian::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'phone'        => trim($record['parent_phone'] ?? ''),
                    'relationship' => trim($record['parent_relationship'] ?? 'Guardian'),
                ]
            );

            $student->parents()->syncWithoutDetaching([
                $parent->id => [
                    'relationship'       => $parent->relationship,
                    'is_primary_contact' => true,
                ],
            ]);

            if ($user->wasRecentlyCreated) {
                $user->notify(new ParentWelcomeNotification($user, $student, $tempPass));
            }
        }

        $this->imported++;
    }
}
