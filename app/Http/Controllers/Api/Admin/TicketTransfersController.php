<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\OrionController;
use App\Models\Ticketing\TicketTransfer;
use App\Policies\TicketTransferAdminPolicy;

class TicketTransfersController extends OrionController
{
    protected $model = TicketTransfer::class;

    protected $policy = TicketTransferAdminPolicy::class;
}
