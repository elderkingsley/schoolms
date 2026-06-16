<?php

namespace Tests\Feature;

use App\Jobs\ProcessPayGridInflowJob;
use App\Jobs\PushInvoiceToPayGridJob;
use App\Jobs\SendInvoiceJob;
use App\Models\AcademicSession;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use App\Models\FeeItem;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ParentCreditFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_process_paygrid_inflow_creates_parent_credit_for_excess_amount(): void
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);
        [$parent, $student, $invoice] = $this->createParentStudentInvoiceGraph();

        Notification::fake();

        app()->call([new ProcessPayGridInflowJob([
            'account_number' => '0123456789',
            'amount_ngn' => 140,
            'reference' => 'PAYGRID-OVER-001',
            'sender_name' => 'Parent One',
        ]), 'handle']);

        $invoice->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertSame('100.00', (string) $invoice->amount_paid);
        $this->assertSame('0.00', (string) $invoice->balance);

        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $invoice->id,
            'amount' => '100.00',
            'method' => 'BudPay Transfer',
            'reference' => 'PAYGRID-OVER-001-inv-'.$invoice->id,
            'recorded_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('parent_credits', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'origin_fee_invoice_id' => $invoice->id,
            'source_reference' => 'PAYGRID-OVER-001-credit',
            'total_amount' => '40.00',
            'balance_amount' => '40.00',
            'status' => 'open',
        ]);

        $this->assertSame(-40.0, $invoice->displayBalance());
    }

    public function test_send_invoice_job_applies_parent_credit_before_queuing_paygrid_push(): void
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);
        [$parent, $student, $invoice] = $this->createParentStudentInvoiceGraph();

        $credit = $parent->credits()->create([
            'origin_fee_invoice_id' => null,
            'source_reference' => 'prior-credit-001',
            'total_amount' => 30,
            'balance_amount' => 30,
            'status' => 'open',
            'notes' => 'Existing parent credit',
            'created_by' => $admin->id,
        ]);

        Queue::fake();
        Notification::fake();

        app()->call([new SendInvoiceJob($invoice), 'handle']);

        $invoice->refresh();
        $credit->refresh();

        $this->assertNotNull($invoice->sent_at);
        $this->assertSame('partial', $invoice->status);
        $this->assertSame('30.00', (string) $invoice->amount_paid);
        $this->assertSame('70.00', (string) $invoice->balance);
        $this->assertSame('applied', $credit->status);
        $this->assertSame('0.00', (string) $credit->balance_amount);

        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $invoice->id,
            'amount' => '30.00',
            'method' => 'Parent Credit',
            'reference' => 'credit-'.$credit->id.'-inv-'.$invoice->id,
            'recorded_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('parent_credit_applications', [
            'parent_credit_id' => $credit->id,
            'fee_invoice_id' => $invoice->id,
            'amount' => '30.00',
            'reference' => 'credit-'.$credit->id.'-inv-'.$invoice->id,
            'applied_by' => $admin->id,
        ]);

        Queue::assertPushed(PushInvoiceToPayGridJob::class, 1);
    }

    public function test_siblings_resolve_the_same_billing_account_when_parent_order_differs(): void
    {
        $fatherUser = User::factory()->create(['user_type' => 'parent']);
        $motherUser = User::factory()->create(['user_type' => 'parent']);

        $father = ParentGuardian::create([
            'user_id' => $fatherUser->id,
            'budpay_account_number' => '1111111111',
            'budpay_bank_name' => 'Test Bank',
            'budpay_bank_code' => '999',
            'budpay_wallet_status' => 'active',
        ]);

        $mother = ParentGuardian::create([
            'user_id' => $motherUser->id,
            'budpay_account_number' => '2222222222',
            'budpay_bank_name' => 'Other Bank',
            'budpay_bank_code' => '998',
            'budpay_wallet_status' => 'active',
        ]);

        $firstChild = $this->createStudent('ADM-101', 'First');
        $secondChild = $this->createStudent('ADM-102', 'Second');

        $firstChild->parents()->attach($father->id, ['relationship' => 'Father', 'is_primary_contact' => true]);
        $firstChild->parents()->attach($mother->id, ['relationship' => 'Mother', 'is_primary_contact' => false]);

        $secondChild->parents()->attach($mother->id, ['relationship' => 'Mother', 'is_primary_contact' => true]);
        $secondChild->parents()->attach($father->id, ['relationship' => 'Father', 'is_primary_contact' => false]);

        $this->assertSame('1111111111', $firstChild->billingParent(requireAccount: true)->active_account_number);
        $this->assertSame('1111111111', $secondChild->billingParent(requireAccount: true)->active_account_number);
    }

    public function test_process_paygrid_inflow_settles_unpaid_invoices_across_siblings(): void
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);
        [$parent, $firstChild, $firstInvoice] = $this->createParentStudentInvoiceGraph();

        $secondChild = $this->createStudent('ADM-002', 'Second');
        $parent->students()->attach($secondChild->id, [
            'relationship' => 'Guardian',
            'is_primary_contact' => true,
        ]);
        $secondInvoice = $this->createInvoiceForStudent($secondChild);

        Notification::fake();

        app()->call([new ProcessPayGridInflowJob([
            'account_number' => '0123456789',
            'amount_ngn' => 160,
            'reference' => 'PAYGRID-FAMILY-001',
            'sender_name' => 'Parent One',
        ]), 'handle']);

        $firstInvoice->refresh();
        $secondInvoice->refresh();

        $this->assertSame('paid', $firstInvoice->status);
        $this->assertSame('100.00', (string) $firstInvoice->amount_paid);

        $this->assertSame('partial', $secondInvoice->status);
        $this->assertSame('60.00', (string) $secondInvoice->amount_paid);
        $this->assertSame('40.00', (string) $secondInvoice->balance);

        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $firstInvoice->id,
            'amount' => '100.00',
            'method' => 'BudPay Transfer',
            'reference' => 'PAYGRID-FAMILY-001-inv-'.$firstInvoice->id,
            'recorded_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('fee_payments', [
            'fee_invoice_id' => $secondInvoice->id,
            'amount' => '60.00',
            'method' => 'BudPay Transfer',
            'reference' => 'PAYGRID-FAMILY-001-inv-'.$secondInvoice->id,
            'recorded_by' => $admin->id,
        ]);
    }

    public function test_student_scoped_credit_is_not_applied_to_sibling_invoice(): void
    {
        $admin = User::factory()->create(['user_type' => 'super_admin']);
        [$parent, $firstChild, $firstInvoice] = $this->createParentStudentInvoiceGraph();

        $secondChild = $this->createStudent('ADM-002', 'Second');
        $parent->students()->attach($secondChild->id, [
            'relationship' => 'Guardian',
            'is_primary_contact' => true,
        ]);
        $secondInvoice = $this->createInvoiceForStudent($secondChild);

        $credit = $parent->credits()->create([
            'student_id' => $firstChild->id,
            'origin_fee_invoice_id' => $firstInvoice->id,
            'source_reference' => 'first-child-credit-001',
            'total_amount' => 30,
            'balance_amount' => 30,
            'status' => 'open',
            'notes' => 'Existing first child credit',
            'created_by' => $admin->id,
        ]);

        Queue::fake();
        Notification::fake();

        app()->call([new SendInvoiceJob($secondInvoice), 'handle']);

        $secondInvoice->refresh();
        $credit->refresh();

        $this->assertSame('unpaid', $secondInvoice->status);
        $this->assertSame('0.00', (string) $secondInvoice->amount_paid);
        $this->assertSame('100.00', (string) $secondInvoice->balance);
        $this->assertSame('open', $credit->status);
        $this->assertSame('30.00', (string) $credit->balance_amount);
        $this->assertDatabaseMissing('parent_credit_applications', [
            'parent_credit_id' => $credit->id,
            'fee_invoice_id' => $secondInvoice->id,
        ]);
    }

    /**
     * @return array{0: ParentGuardian, 1: Student, 2: FeeInvoice}
     */
    private function createParentStudentInvoiceGraph(): array
    {
        $parentUser = User::factory()->create([
            'user_type' => 'parent',
            'email' => 'parent@example.com',
        ]);

        $parent = ParentGuardian::create([
            'user_id' => $parentUser->id,
            'budpay_account_number' => '0123456789',
            'budpay_bank_name' => 'Test Bank',
            'budpay_bank_code' => '999',
            'budpay_wallet_status' => 'active',
        ]);

        $student = $this->createStudent('ADM-001', 'Test');

        $parent->students()->attach($student->id, [
            'relationship' => 'Guardian',
            'is_primary_contact' => true,
        ]);

        $invoice = $this->createInvoiceForStudent($student);

        return [$parent, $student, $invoice];
    }

    private function createStudent(string $admissionNumber, string $firstName): Student
    {
        return Student::create([
            'admission_number' => $admissionNumber,
            'first_name' => $firstName,
            'last_name' => 'Student',
            'gender' => 'Male',
            'date_of_birth' => '2015-01-01',
            'status' => 'active',
        ]);
    }

    private function createInvoiceForStudent(Student $student): FeeInvoice
    {
        $session = AcademicSession::firstOrCreate([
            'name' => '2026/2027',
        ], [
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $term = Term::firstOrCreate([
            'academic_session_id' => $session->id,
            'name' => 'First',
        ], [
            'academic_session_id' => $session->id,
            'name' => 'First',
            'is_active' => true,
        ]);

        $invoice = FeeInvoice::create([
            'student_id' => $student->id,
            'term_id' => $term->id,
            'total_amount' => 100,
            'amount_paid' => 0,
            'balance' => 100,
            'status' => 'unpaid',
        ]);

        $feeItem = FeeItem::create([
            'name' => 'Tuition',
            'type' => 'compulsory',
            'is_active' => true,
        ]);

        FeeInvoiceItem::create([
            'fee_invoice_id' => $invoice->id,
            'fee_item_id' => $feeItem->id,
            'item_name' => 'Tuition',
            'amount' => 100,
            'added_by' => 'system',
        ]);

        return $invoice;
    }
}
