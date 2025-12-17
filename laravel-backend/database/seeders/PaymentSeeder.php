<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        // Get invoices that have been paid or partially paid
        $paidInvoices = Invoice::whereIn('status', ['paid', 'partial'])->get();
        $adminUser = User::where('role', 'admin')->first();

        if ($paidInvoices->isEmpty() || !$adminUser) {
            $this->command->info('No paid invoices or admin user found. Skipping payment seeding.');
            return;
        }

        $paymentMethods = ['cash', 'bank_transfer', 'credit_card', 'debit_card', 'cheque', 'online'];
        $payments = [];

        foreach ($paidInvoices as $invoice) {
            if ($invoice->status === 'paid') {
                // Create full payment
                $payments[] = [
                    'invoice_id' => $invoice->id,
                    'amount_paid' => $invoice->amount_due,
                    'payment_date' => now()->subDays(rand(1, 15)),
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                    'payment_notes' => 'Full payment for ' . $invoice->description,
                    'status' => 'completed',
                    'processed_by' => $adminUser->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            } elseif ($invoice->status === 'partial') {
                // Create partial payment(s)
                $remainingAmount = $invoice->amount_paid;
                $paymentCount = rand(1, 2);
                
                if ($paymentCount === 1) {
                    // Single partial payment
                    $payments[] = [
                        'invoice_id' => $invoice->id,
                        'amount_paid' => $remainingAmount,
                        'payment_date' => now()->subDays(rand(1, 10)),
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'payment_notes' => 'Partial payment for ' . $invoice->description,
                        'status' => 'completed',
                        'processed_by' => $adminUser->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                } else {
                    // Multiple partial payments
                    $firstPayment = $remainingAmount * 0.6;
                    $secondPayment = $remainingAmount - $firstPayment;
                    
                    $payments[] = [
                        'invoice_id' => $invoice->id,
                        'amount_paid' => $firstPayment,
                        'payment_date' => now()->subDays(rand(5, 15)),
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'payment_notes' => 'First installment for ' . $invoice->description,
                        'status' => 'completed',
                        'processed_by' => $adminUser->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    
                    $payments[] = [
                        'invoice_id' => $invoice->id,
                        'amount_paid' => $secondPayment,
                        'payment_date' => now()->subDays(rand(1, 5)),
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                        'payment_notes' => 'Second installment for ' . $invoice->description,
                        'status' => 'completed',
                        'processed_by' => $adminUser->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Insert payments in batches
        foreach (array_chunk($payments, 50) as $chunk) {
            foreach ($chunk as $paymentData) {
                $payment = Payment::create($paymentData);
                
                // Generate payment reference after creation
                if (empty($payment->payment_reference)) {
                    $payment->payment_reference = 'PAY-' . date('Ym') . '-' . str_pad($payment->id, 4, '0', STR_PAD_LEFT);
                    $payment->save();
                }
            }
        }

        $this->command->info('Created ' . count($payments) . ' payments successfully.');
    }
}
