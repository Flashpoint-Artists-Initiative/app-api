<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Grants;

use App\Enums\ArtProjectStatusEnum;
use App\Http\Controllers\OrionController;
use App\Http\Requests\Grants\ArtProjectRequest;
use App\Models\Grants\ArtProject;
use App\Models\User;
use App\Policies\ArtProjectPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Orion\Http\Requests\Request;

class ArtProjectsController extends OrionController
{
    protected $model = ArtProject::class;

    protected $policy = ArtProjectPolicy::class;

    protected $request = ArtProjectRequest::class;

    public function __construct()
    {
        $this->middleware(['lockdown:grants'])->except(['index', 'show', 'search']);

        parent::__construct();
    }

    // public function voteAction(ArtProject $artProject): JsonResponse
    // {
    //     /** @var User */
    //     $user = auth()->user();

    //     try {
    //         $artProject->vote($user);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }

    //     return response()->json(['message' => 'Vote cast successfully']);
    // }

    /**
     * Modifies index query to hide non-approved artProjects for users without specific permission to view them
     *
     * @param  string[]  $requestedRelations
     */
    protected function buildIndexFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildIndexFetchQuery($request, $requestedRelations);

        /** @var ?User $user */
        $user = auth()->user();

        // Hide non-active artProjects for users without specific permission to view them
        if (! $user || ! $user->can('artProjects.viewPending')) {
            $query->where('project_status', ArtProjectStatusEnum::Approved->value);
        }

        // Hide soft-deleted artProjects for users without specific permission to view them
        if (! $user || ! $user->can('artProjects.viewDeleted')) {
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }

    /**
     * @param  string[]  $requestedRelations
     */
    protected function buildShowFetchQuery(Request $request, array $requestedRelations): Builder
    {
        $query = parent::buildShowFetchQuery($request, $requestedRelations);

        /** @var ?User $user */
        $user = auth()->user();

        // Hide soft-deleted artProjects for users without specific permission to view them
        if (! $user || ! $user->can('artProjects.viewDeleted')) {
            // @phpstan-ignore-next-line
            $query->withoutTrashed();
        }

        return $query;
    }
}
