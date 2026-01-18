<?php

namespace App\Models\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'receipt_number',
        'receipt_type',
        'file_path',
        'file_url',
        'sent_to_customer',
        'sent_at',
        'email_address',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'sent_to_customer' => 'boolean',
    ];

    // Relationships
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Scopes
    public function scopeSent($query)
    {
        return $query->where('sent_to_customer', true);
    }

    public function scopeUnsent($query)
    {
        return $query->where('sent_to_customer', false);
    }

    // Helper methods
    public function isSent(): bool
    {
        return $this->sent_to_customer;
    }

    public function isPdf(): bool
    {
        return $this->receipt_type === 'pdf';
    }

    public function isHtml(): bool
    {
        return $this->receipt_type === 'html';
    }
}