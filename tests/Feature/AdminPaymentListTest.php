<?php

namespace Tests\Feature;

use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaymentListTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_payments_newest_first(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'user_type' => 'admin',
            'force_password_change' => false,
        ]);
        $admin->assignRole('admin');

        $olderInvoice = $this->createInvoiceForStudent('ADM-OLD', 'Older');
        $newerInvoice = $this->createInvoiceForStudent('ADM-NEW', 'Newer');

        FeePayment::create([
            'fee_invoice_id' => $olderInvoice->id,
            'amount' => 7500,
            'method' => 'Cash',
            'receipt_number' => 'RCP-OLDER-001',
            'reference' => 'OLDER-REF',
            'recorded_by' => $admin->id,
            'paid_at' => now()->subDay(),
        ]);

        FeePayment::create([
            'fee_invoice_id' => $newerInvoice->id,
            'amount' => 12500,
            'method' => 'Bank Transfer',
            'receipt_number' => 'RCP-NEWER-001',
            'reference' => 'NEWER-REF',
            'recorded_by' => $admin->id,
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.payments'))
            ->assertOk()
            ->assertSee('Payments')
            ->assertSee('₦20,000')
            ->assertSee('RCP-NEWER-001')
            ->assertSee(route('admin.fees.invoices.show', $newerInvoice), false)
            ->assertSeeInOrder(['Newer Student', 'Older Student']);
    }

    private function createInvoiceForStudent(string $admissionNumber, string $firstName): FeeInvoice
    {
        $session = AcademicSession::create([
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $term = Term::create([
            'academic_session_id' => $session->id,
            'name' => 'First',
            'is_active' => true,
        ]);

        $student = Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'gender' => 'Male',
            'date_of_birth' => '2015-01-01',
            'status' => 'active',
        ]);

        return FeeInvoice::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 20000,
            'amount_paid' => 0,
            'balance' => 20000,
            'status' => 'unpaid',
        ]);
    }
}
