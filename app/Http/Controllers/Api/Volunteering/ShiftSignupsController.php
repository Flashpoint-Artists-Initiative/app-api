<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Volunteering;

use App\Http\Controllers\OrionRelationsController;
use App\Http\Requests\Volunteering\ShiftCancelRequest;
use App\Http\Requests\Volunteering\ShiftSignupRequest;
use App\Models\User;
use App\Models\Volunteering\Shift;
use App\Policies\Volunteering\ShiftPolicy;
use App\Policies\Volunteering\ShiftSignupPolicy;
use Illuminate\Http\JsonResponse;

class ShiftSignupsController extends OrionRelationsController
{
    protected $model = Shift::class;

    protected $relation = 'volunteers';

    protected $policy = ShiftSignupPolicy::class;

    protected $parentPolicy = ShiftPolicy::class;

    public function signupAction(Shift $shift, ShiftSignupRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->shifts()->attach($shift->id);

        return response()->json(status: 204);
    }

    public function cancelAction(Shift $shift, ShiftCancelRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $user->shifts()->detach($shift->id);

        return response()->json(status: 204);
    }
}
