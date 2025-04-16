<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ValueError;

class UserRoleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:role
                                {user_id : User ID}
                                {role : Role}
                                {--a|add : Adds the specified role (default)}
                                {--d|delete : Remove the specified role}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or remove a role from a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $user = User::findOrFail($this->argument('user_id'));
        } catch (ModelNotFoundException $e) {
            $this->error(sprintf('User ID "%d" not found', $this->argument('user_id')));

            return 1;
        }

        try {
            $role = RolesEnum::from($this->argument('role'));
        } catch (ValueError $e) {
            $this->error(sprintf('Role "%s" not found', $this->argument('role')));

            return 2;
        }

        if ($this->option('delete')) {
            $user->removeRole($role);
            $this->info(sprintf('Role %s removed from UID %d', $role->getLabel(), $user->id));
        } else {
            $user->assignRole($role);
            $this->info(sprintf('Role %s added to UID %d', $role->getLabel(), $user->id));
        }

        return 0;
    }
}
