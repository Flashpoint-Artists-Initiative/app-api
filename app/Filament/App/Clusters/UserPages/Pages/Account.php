<?php

declare(strict_types=1);

namespace App\Filament\App\Clusters\UserPages\Pages;

use App\Filament\App\Clusters\UserPages;
use App\Filament\Traits\HasAuthComponents;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;

class Account extends Page
{
    use HasAuthComponents;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Account Details';

    protected static ?string $title = 'Account Details';

    protected static string $view = 'filament.app.clusters.user-pages.pages.account';

    protected static ?string $cluster = UserPages::class;

    public function accountInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record(filament()->auth()->user())
            ->schema([
                Section::make('Legal Name')
                    ->schema([
                        TextEntry::make('legal_name'),
                    ])
                    ->collapsed()
                    ->columnSpan(1),
                TextEntry::make('preferred_name')
                    ->placeholder('None'),
                TextEntry::make('email'),
                TextEntry::make('birthday')
                    ->date('F jS, Y'),
                TextEntry::make('created_at')
                    ->label('Account Created')
                    ->date('F jS, Y'),
            ])
            ->columns(2);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->fillForm(function () {
                    /** @var User */
                    $user = filament()->auth()->user();

                    return [
                        'legal_name' => $user->legal_name,
                        'preferred_name' => $user->preferred_name,
                        'email' => $user->email,
                        'birthday' => $user->birthday,
                    ];
                })
                ->form([
                    $this->getLegalNameFormComponent(),
                    $this->getPreferredNameFormComponent(),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->helperText('Changing your email address will require re-verification.'),
                    $this->getBirthdayFormComponent(),
                ])
                ->action(function (array $data) {
                    /** @var User */
                    $user = filament()->auth()->user();
                    $user->legal_name = $data['legal_name'];
                    $user->preferred_name = $data['preferred_name'];
                    $user->email = $data['email'];
                    $user->save();
                }),
            Action::make('delete')
                ->requiresConfirmation()
                ->modalDescription('Are you sure you want to delete your account? This action cannot be undone.')
                ->modalHeading('Delete Account')
                ->color(Color::Red),
            // ->action(fn () => $this->post->delete()),
        ];
    }
}
