<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\UniqueConstraintViolationException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class CreateUserCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create
                                {legal_name : Legal Name}
                                {email : Email}
                                {birthday : Birthday (YYYY-MM-DD)}
                                {password : Password}
                                {--preferred_name= : Preferred Name (optional)}
                                {--role=} : Role (optional)
                                {--y|confirm : Confirm input}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user with an optional role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('confirm')) {
            $this->table(['Field', 'Input'], $this->buildTable());
            if (! $this->confirm('Add user?')) {
                return 1;
            }
        }

        try {
            $user = User::create([
                'legal_name' => $this->argument('legal_name'),
                'preferred_name' => ! empty($this->option('preferred_name')) ? $this->option('preferred_name') : null,
                'email' => $this->argument('email'),
                'birthday' => $this->argument('birthday'),
                'password' => $this->argument('legal_name'),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            $this->error($e->getPrevious()->getMessage());

            return 2;
        }

        $role = $this->option('role');
        $roleText = '';

        if ($role !== 'none') {
            $user->assignRole($role);
            $roleText = 'and role <options=bold>' . RolesEnum::from($role)->label() . '</>';
        }

        $this->info(sprintf('User <options=bold>%s</> was created with ID: <options=bold>%d</> %s', $user->email, $user->id, $roleText));

        return 0;
    }

    protected function promptForMissingArgumentsUsing()
    {
        return [
            'legal_name' => 'Legal Name',
            'email' => 'Email',
            'birthday' => ['Birthday', 'YYYY-MM-DD'],
            'password' => 'Password',
        ];
    }

    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        $input->setOption('preferred_name', text('Preferred Name'));
        $input->setOption('role', select('Role', $this->getRolesSelectOptions()));
    }

    protected function getRolesSelectOptions(): array
    {
        $output = ['none' => 'None'];

        foreach (RolesEnum::cases() as $role) {
            $output[$role->value] = $role->label();
        }

        return $output;
    }

    protected function buildTable(): array
    {
        $arguments = $this->arguments();
        $options = $this->options();

        return [
            ['Legal Name', $arguments['legal_name']],
            ['Email', $arguments['email']],
            ['Birthday', $arguments['birthday']],
            ['Password', $arguments['password']],
            ['Preferred Name', $options['preferred_name']],
            ['Role', $options['role']],
        ];
    }
}
