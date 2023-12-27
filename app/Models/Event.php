<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'start_date',
        'end_date',
        'contact_email',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];
}