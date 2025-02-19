<?php
declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLegalNameFormComponent(),
                        $this->getPreferredNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getBirthdayFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    protected function getLegalNameFormComponent(): Component
    {
        return TextInput::make('legal_name')
            ->label('Legal Name')
            ->required()
            ->maxLength(255)
            ->helperText('As it shows up on your ID.  This will only be visible to gate staff as you enter the event.')
            ->autofocus();
    }

    protected function getPreferredNameFormComponent(): Component
    {
        return TextInput::make('preferred_name')
            ->label('Preferred Name')
            ->required()
            ->maxLength(255)
            ->helperText('If you don\'t want to use your legal name, what should we call you? Visible to event leadership and volunteer coordinators.');
    }

    protected function getBirthdayFormComponent(): Component
    {
        return DatePicker::make('birthday')
            ->required()
            ->displayFormat('d/m/Y')
            ->helperText('Used to verify your age while entering the event.');
    }
}
