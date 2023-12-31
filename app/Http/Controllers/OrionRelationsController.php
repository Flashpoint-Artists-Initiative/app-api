<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Orion\Http\Controllers\RelationController as BaseController;
use Orion\Http\Requests\Request;

abstract class OrionRelationsController extends BaseController
{
    public function limit(): int
    {
        return config('orion.default_pagination_limit', 50);
    }

    public function maxLimit(): ?int
    {
        return config('orion.default_pagination_max_limit', 200);
    }

    /**
     * Many to Many hooks
     *
     * Orion checks for access to 'update' the parent model and 'view the child model
     * when attaching / detaching.  We add checks here to more intentionally make sure
     * that the user has access to attach the specific child model.
     *
     * These authorize calls default to checking the {$relation}.attach/detach permissions
     * inside the parent model's policy
     *
     * $this->relation typically coincides with the name of the permission to check access on.
     * If this isn't the case, simply overload the function in the child policy class
     */
    protected function beforeAttach(Request $request, Model $parentEntity)
    {
        $this->authorize('attach', [$parentEntity, $this->relation]);
    }

    protected function beforeDetach(Request $request, Model $parentEntity)
    {
        $this->authorize('detach', [$parentEntity, $this->relation]);
    }

    protected function beforeSync(Request $request, Model $parentEntity)
    {
        $this->authorize('attach', [$parentEntity, $this->relation]);
        $this->authorize('detach', [$parentEntity, $this->relation]);
    }

    // Uncomment this when we have a relation that uses it
    // protected function beforeToggle(Request $request, Model $parentEntity)
    // {
    //     $this->authorize('attach', [$parentEntity, $this->relation]);
    //     $this->authorize('detach', [$parentEntity, $this->relation]);
    // }

    /**
     * One to Many hooks
     *
     * These work the same way as the Many to Many above
     */
    // protected function beforeAssociate(Request $request, Model $parentEntity, Model $entity)
    // {

    // }

    // protected function beforeDissociate(Request $request, Model $parentEntity, Model $entity)
    // {

    // }
}
