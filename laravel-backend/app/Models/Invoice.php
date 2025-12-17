<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'student_id',
        'fee_structure_id',
        'amount_due',
        'amount_paid',
        'balance',
        'due_date',
        'issued_date',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'issued_date' => 'date',
    ];

    // Boot method to auto-generate invoice number
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            
            if (empty($invoice->issued_date)) {
                $invoice->issued_date = now()->toDateString();
            }
            
            // Calculate balance
            $invoice->balance = $invoice->amount_due - $invoice->amount_paid;
            
            // Auto-set status based on payment
            if ($invoice->amount_paid == 0) {
                $invoice->status = 'pending';
            } elseif ($invoice->amount_paid < $invoice->amount_due) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'paid';
            }
        });

        static::updating(function ($invoice) {
            // Recalculate balance
            $invoice->balance = $invoice->amount_due - $invoice->amount_paid;
            
            // Auto-update status based on payment
            if ($invoice->amount_paid == 0) {
                $invoice->status = ($invoice->due_date < now()) ? 'overdue' : 'pending';
            } elseif ($invoice->amount_paid < $invoice->amount_due) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'paid';
            }
        });
    }

    // Generate unique invoice number
    private static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)
                          ->orderBy('id', 'desc')
                          ->first();
        
        $nextNumber = $lastInvoice ? (int)substr($lastInvoice->invoice_number, -4) + 1 : 1;
        
        return 'INV-' . $year . $month . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure()
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', now());
                    });
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Accessors
    public function getFormattedAmountDueAttribute()
    {
        return '$' . number_format($this->amount_due, 2);
    }

    public function getFormattedAmountPaidAttribute()
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return '$' . number_format($this->balance, 2);
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->is_overdue) {
            return 0;
        }
        return Carbon::parse($this->due_date)->diffInDays(now());
    }

    // Methods
    public function recordPayment($amount, $paymentData = [])
    {
        $payment = $this->payments()->create(array_merge([
            'amount_paid' => $amount,
            'payment_date' => now()->toDateString(),
        ], $paymentData));

        // Update invoice amounts
        $this->amount_paid += $amount;
        $this->save();

        return $payment;
    }

    public function getTotalPaid()
    {
        return $this->payments()->where('status', 'completed')->sum('amount_paid');
    }
}
