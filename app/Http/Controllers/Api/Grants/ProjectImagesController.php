<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Grants;

use App\Http\Controllers\OrionRelationsController;
use App\Models\Grants\ArtProject;

class ProjectImagesController extends OrionRelationsController
{
    protected $model = ArtProject::class;

    protected $relation = 'images';
}
