<?php

declare(strict_types=1);

namespace App\Filament\App\Clusters\UserPages\Pages;

use App\Filament\App\Clusters\UserPages;
use App\Models\Ticketing\CompletedWaiver;
use App\Models\Ticketing\Waiver;
use App\Models\User;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;

class Waivers extends Page implements HasForms, HasTable
{
    use InteractsWithTable, InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.clusters.user-pages.pages.waivers';

    protected static ?string $cluster = UserPages::class;

    public function table(Table $table): Table
    {
        return $table
            ->query(CompletedWaiver::where('user_id', Auth::id()))
            ->columns([
                //
            ])
            ->filters([
                //
            ]);
    }
}
