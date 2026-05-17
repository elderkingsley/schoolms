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
            'origin_fee_invoice_id' => $invoice->id,
            'source_reference' => 'PAYGRID-OVER-001-credit',
            'total_amount' => '40.00',
            'balance_amount' => '40.00',
            'status' => 'open',
        ]);
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

        $student = Student::create([
            'admission_number' => 'ADM-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'gender' => 'Male',
            'date_of_birth' => '2015-01-01',
            'status' => 'active',
        ]);

        $parent->students()->attach($student->id, [
            'relationship' => 'Guardian',
            'is_primary_contact' => true,
        ]);

        $session = AcademicSession::create([
            'name' => '2026/2027',
            'is_active' => true,
        ]);

        $term = Term::create([
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

        return [$parent, $student, $invoice];
    }
}
