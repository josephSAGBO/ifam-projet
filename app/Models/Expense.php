<?php

namespace App\Models;

use App\Models\Concerns\AssignsDefaultSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use AssignsDefaultSite, HasFactory;

    protected $fillable = [
        'expense_category_id',
        'site_id',
        'title',
        'amount',
        'expense_date',
        'payment_method',
        'reference',
        'vendor',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
