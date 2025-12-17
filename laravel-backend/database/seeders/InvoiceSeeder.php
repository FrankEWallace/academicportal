<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\FeeStructure;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        // Get students and fee structures
        $students = Student::all();
        $feeStructures = FeeStructure::all();

        if ($students->isEmpty() || $feeStructures->isEmpty()) {
            $this->command->info('No students or fee structures found. Skipping invoice seeding.');
            return;
        }

        $invoices = [];

        foreach ($students as $student) {
            // Create invoices for each student based on their program
            $programName = $student->department->name ?? 'Computer Science';
            
            $programFeeStructures = $feeStructures->where('program', $programName);
            
            foreach ($programFeeStructures as $feeStructure) {
                // Create invoice for each fee structure
                $invoices[] = [
                    'student_id' => $student->id,
                    'fee_structure_id' => $feeStructure->id,
                    'amount_due' => $feeStructure->amount,
                    'amount_paid' => 0.00,
                    'balance' => $feeStructure->amount,
                    'due_date' => $feeStructure->due_date,
                    'issued_date' => now()->subDays(rand(1, 30)),
                    'status' => 'pending',
                    'description' => $feeStructure->description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Add some paid invoices
        if (count($invoices) > 0) {
            // Mark some invoices as paid
            for ($i = 0; $i < min(5, count($invoices)); $i++) {
                $invoices[$i]['amount_paid'] = $invoices[$i]['amount_due'];
                $invoices[$i]['balance'] = 0.00;
                $invoices[$i]['status'] = 'paid';
            }

            // Mark some as partially paid
            for ($i = 5; $i < min(8, count($invoices)); $i++) {
                $partialAmount = $invoices[$i]['amount_due'] * 0.5;
                $invoices[$i]['amount_paid'] = $partialAmount;
                $invoices[$i]['balance'] = $invoices[$i]['amount_due'] - $partialAmount;
                $invoices[$i]['status'] = 'partial';
            }

            // Mark some as overdue
            for ($i = 8; $i < min(12, count($invoices)); $i++) {
                $invoices[$i]['due_date'] = now()->subDays(rand(1, 30));
                $invoices[$i]['status'] = 'overdue';
            }
        }

        // Insert invoices in batches
        foreach (array_chunk($invoices, 50) as $chunk) {
            foreach ($chunk as $invoiceData) {
                $invoice = Invoice::create($invoiceData);
                
                // Generate invoice number after creation
                if (empty($invoice->invoice_number)) {
                    $invoice->invoice_number = 'INV-' . date('Ym') . '-' . str_pad($invoice->id, 4, '0', STR_PAD_LEFT);
                    $invoice->save();
                }
            }
        }

        $this->command->info('Created ' . count($invoices) . ' invoices successfully.');
    }
}
