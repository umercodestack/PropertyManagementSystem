<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportFinanceSoa extends Model
{
    use HasFactory;
    protected $table = 'reports_finance_soa';
    protected $fillable = [
        'user_id',
        'booking_dates',
        'publish_date',
        'listings',
        'total',
        'file_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
