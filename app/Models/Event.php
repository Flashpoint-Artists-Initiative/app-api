<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Ticketing\PurchasedTicket;
use App\Models\Ticketing\ReservedTicket;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'start_date',
        'end_date',
        'contact_email',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function purchasedTickets(): HasManyThrough
    {
        return $this->hasManyThrough(PurchasedTicket::class, TicketType::class);
    }

    public function reservedTickets(): HasManyThrough
    {
        return $this->hasManyThrough(ReservedTicket::class, TicketType::class);
    }
}
