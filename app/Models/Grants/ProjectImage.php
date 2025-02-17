<?php

declare(strict_types=1);

namespace App\Models\Grants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;

/**
 * @property-read ArtProject $artProject
 */
class ProjectImage extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'art_project_id',
        'name',
        'path',
    ];

    public function artProject(): BelongsTo
    {
        return $this->belongsTo(ArtProject::class);
    }
}
