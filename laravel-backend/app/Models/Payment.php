<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_reference',
        'invoice_id',
        'amount_paid',
        'payment_date',
        'payment_method',
        'transaction_id',
        'payment_notes',
        'status',
        'processed_by',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Boot method to auto-generate payment reference
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = self::generatePaymentReference();
            }
            
            if (empty($payment->payment_date)) {
                $payment->payment_date = now()->toDateString();
            }
        });
    }

    // Generate unique payment reference
    private static function generatePaymentReference()
    {
        $year = date('Y');
        $month = date('m');
        $lastPayment = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)
                          ->orderBy('id', 'desc')
                          ->first();
        
        $nextNumber = $lastPayment ? (int)substr($lastPayment->payment_reference, -4) + 1 : 1;
        
        return 'PAY-' . $year . $month . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('payment_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount_paid, 2);
    }

    public function getPaymentMethodLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->payment_method));
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    // Methods
    public function refund($reason = null)
    {
        if ($this->status !== 'completed') {
            throw new \Exception('Can only refund completed payments');
        }

        $this->update([
            'status' => 'refunded',
            'payment_notes' => $this->payment_notes . "\nRefunded: " . ($reason ?? 'No reason provided')
        ]);

        // Update the invoice amounts
        $invoice = $this->invoice;
        $invoice->amount_paid -= $this->amount_paid;
        $invoice->save();

        return $this;
    }
}
