<?php
declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use App\Filament\Traits\HasAuthComponents;
use Filament\Pages\Auth\Register as BaseRegister;

class Register extends BaseRegister
{
    use HasAuthComponents;
    
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

}
