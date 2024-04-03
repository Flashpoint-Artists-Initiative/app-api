<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Event;
use App\Models\Ticketing\TicketType;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 *
 * @property Event $event
 * @property TicketType $ticketType
 */
interface TicketInterface
{
}
