<?php

namespace App\Console\Commands;

use App\Enums\Users\RoleEnum;
use App\Lib\Helpers\FileStorageHelper;
use App\Lib\Helpers\ToolboxHelper;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Creates a new user.
 */
class UserCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user (Conceptor, Administrator or Visitor)';

    /**
     * The first name of the new user.
     *
     * @var string|null
     */
    protected $first_name;

    /**
     * The last name of the new user.
     *
     * @var string|null
     */
    protected $last_name;

    /**
     * The email of the new user.
     *
     * @var string|null
     */
    protected $email;

    /**
     * The password of the new user.
     *
     * @var string|null
     */
    protected $password;

    /**
     * The role of the new user.
     *
     * @var \App\Enums\Users\RoleEnum|null
     */
    protected $role;

    /**
     * Execute the console command.
     *
     * @return integer
     */
    public function handle(): int
    {
        $this->first_name = null;
        $this->addFirstname();

        $this->last_name = null;
        $this->addLastname();

        $this->email = null;
        $this->addEmail();

        $this->password = null;
        $this->addPassword();

        $this->role = null;
        $this->addRole();

        $user               = new User();
        $user->first_name   = $this->first_name;
        $user->last_name    = $this->last_name;
        $user->email        = $this->email;
        $user->password     = $this->password;
        $user->picture      = FileStorageHelper::storeFile(
            $user,
            new \SplFileInfo(\resource_path('../database/factories/assets/users/default-picture.png'))
        );
        $user->published    = true;
        $user->published_at = Carbon::now();
        $user->role         = $this->role;
        $user->saveOrFail();

        $this->info('User created 👌');

        return 0;
    }

    /**
     * Add a first name to the user.
     *
     * @return void
     */
    private function addFirstname(): void
    {
        while (is_null($this->first_name)) {
            try {
                $tmp       = $this->ask('Type the wanted user first name', 'John');
                $validator = Validator::make(['first_name' => $tmp], [
                    'first_name' => 'required|min:3|max:255'
                ]);
                $validator->validate();
                $this->first_name = $tmp;
            } catch (ValidationException $e) {
                $this->error(sprintf(
                    'Please provide a valid first name %s',
                    \implode(',', collect($e->errors())->flatten()->all())
                ));
                continue;
            }
        };
    }

    /**
     * Add a last name to the user.
     *
     * @return void
     */
    private function addLastname(): void
    {
        while (is_null($this->last_name)) {
            try {
                $tmp       = $this->ask('Type the wanted user last name', 'Doe');
                $validator = Validator::make(['last_name' => $tmp], [
                    'last_name' => 'required|min:3|max:255'
                ]);
                $validator->validate();
                $this->last_name = $tmp;
            } catch (ValidationException $e) {
                $this->error(sprintf(
                    'Please provide a valid last name %s',
                    \implode(',', collect($e->errors())->flatten()->all())
                ));
                continue;
            }
        };
    }

    /**
     * Add an email to the user.
     *
     * @return void
     */
    private function addEmail(): void
    {
        while (is_null($this->email)) {
            try {
                $tmp       = $this->ask('Type the wanted user email', 'john.doe@gmail.com');
                $validator = Validator::make(['email' => $tmp], [
                    'email' => 'required|unique:users,email|email:rfc,dns'
                ]);
                $validator->validate();
                $this->email = $tmp;
            } catch (ValidationException $e) {
                $this->error(sprintf(
                    'Please provide a valid email %s',
                    \implode(',', collect($e->errors())->flatten()->all())
                ));
                continue;
            }
        };
    }

    /**
     * Add a password to the user.
     *
     * @return void
     */
    private function addPassword(): void
    {
        while (
            ($this->password = $this->secret(
                (!\is_null($this->password) ? 'Failed to confirm, ' : '') . 'Type the wanted user password'
            )) !== $this->secret('Confirm the password')
        ) {
            continue;
        };
    }

    /**
     * Add a role to the user.
     *
     * @return void
     */
    private function addRole(): void
    {
        while (is_null($this->role)) {
            try {
                $tmp = $this->choice(
                    'Select his role',
                    [
                        Str::of(RoleEnum::conceptor->label())->ucFirst()->value(),
                        Str::of(RoleEnum::admin->label())->ucFirst()->value(),
                        Str::of(RoleEnum::visitor->label())->ucFirst()->value()
                    ],
                    Str::of(RoleEnum::visitor->label())->ucFirst()->value(),
                );
                if ($tmp === Str::of(RoleEnum::conceptor->label())->ucFirst()->value()) {
                    $tmp = RoleEnum::conceptor->value();
                } elseif ($tmp === Str::of(RoleEnum::admin->label())->ucFirst()->value()) {
                    $tmp = RoleEnum::admin->value();
                } elseif ($tmp === Str::of(RoleEnum::visitor->label())->ucFirst()->value()) {
                    $tmp = RoleEnum::visitor->value();
                }
                $this->role = RoleEnum::make(ToolboxHelper::getValidatedEnum(
                    $tmp,
                    'role',
                    '\App\Enums\Users\RoleEnum',
                ));
            } catch (ValidationException $e) {
                $this->error(sprintf(
                    'Please choose from the selection %s',
                    \implode(',', collect($e->errors())->flatten()->all())
                ));
                continue;
            } //end try
        }; //end while
    }
}
