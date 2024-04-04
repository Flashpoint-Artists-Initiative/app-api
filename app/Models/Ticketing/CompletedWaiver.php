<?php

declare(strict_types=1);

namespace App\Models\Ticketing;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as ContractsAuditable;

class CompletedWaiver extends Model implements ContractsAuditable
{
    use Auditable, HasFactory;

    protected $fillable = [
        'waiver_id',
        'user_id',
        'form_data',
        'paper_completion',
    ];

    protected $casts = [
        'form_data' => 'array',
    ];

    public function waiver(): BelongsTo
    {
        return $this->belongsTo(Waiver::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
