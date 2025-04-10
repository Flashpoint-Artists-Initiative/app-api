<?php

declare(strict_types=1);

namespace App\Filament\Admin\Resources\ArtProjectResource\Pages;

use App\Filament\Admin\Resources\ArtProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtProjects extends ListRecords
{
    protected static string $resource = ArtProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
