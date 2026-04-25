<?php

namespace App\Console\Commands;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

#[Signature('auth:register {--name= : The name of the user} {--email= : The email of the user} {--password= : The password of the user} {--password_confirmation= : The password confirmation}')]
#[Description('Register a new user manually')]
class RegisterUser extends Command
{
    public function handle(CreateNewUser $creator): int
    {
        info('Register a new user');

        $name = $this->option('name') ?? text(
            label: 'What is the user\'s name?',
            placeholder: 'John Doe',
            required: true,
        );

        $email = $this->option('email') ?? text(
            label: 'What is the user\'s email address?',
            placeholder: 'john@example.com',
            required: true,
        );

        $password = $this->option('password') ?? password(
            label: 'What is the user\'s password?',
            required: true,
        );

        $passwordConfirmation = $this->option('password_confirmation') ?? password(
            label: 'Confirm the password',
            required: true,
        );

        try {
            $user = $creator->create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
            ]);

            info("User {$user->name} ({$user->email}) created successfully!");

            return Command::SUCCESS;
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $errorMessage) {
                error($errorMessage);
            }

            return Command::FAILURE;
        } catch (\Exception $e) {
            error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
