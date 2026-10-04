<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * Creates a portfolio administrator from the command line.
 *
 * This is the supported way to add an administrator in production: it never requires
 * publicly exposing /register, and it never sets a well-known password.
 *
 * The password is read from an interactive prompt or a hidden question, is never
 * echoed, and is never written to stdout, the log, or a config file.
 */
class CreateAdminUser extends Command
{
    protected $signature = 'admin:create
                            {--name= : The administrator name. Prompted for when omitted.}
                            {--email= : The administrator e-mail. Prompted for when omitted.}
                            {--password= : The administrator password. Read interactively when omitted.}
                            {--no-interaction-required : Fail instead of prompting when a value is missing.}';

    protected $description = 'Create a portfolio administrator account (safe alternative to enabling /register)';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Name');
        $email = $this->option('email') ?: $this->ask('E-mail address');

        if (blank($name) || blank($email)) {
            $this->components->error('Name and e-mail are both required.');

            return self::FAILURE;
        }

        $email = strtolower(trim($email));

        $validator = Validator::make(
            ['name' => $name, 'email' => $email],
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email:rfc', 'max:255', 'unique:'.User::class],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->components->error('An account with that e-mail already exists. No changes were made.');

            return self::FAILURE;
        }

        $password = $this->resolvePassword();

        if ($password === null) {
            return self::FAILURE;
        }

        /*
         * `confirmed` is deliberately absent: it expects a `password_confirmation`
         * request field, which a console command does not have. Confirmation is
         * already enforced with hash_equals() in resolvePassword().
         */
        $passwordValidator = Validator::make(
            ['password' => $password],
            ['password' => ['required', 'string', PasswordRule::min(12)]]
        );

        if ($passwordValidator->fails()) {
            foreach ($passwordValidator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            // Hashing goes through the model's 'hashed' cast.
            'password' => Hash::make($password),
            'is_admin' => true,
        ]);

        // The password is intentionally never echoed.
        $this->components->info("Administrator {$email} created.");
        $this->line('  Sign in at <fg=cyan>/login</>. If a stray ALLOW_REGISTRATION=true is set, remove it.');

        return self::SUCCESS;
    }

    /**
     * Read the password without echoing it.
     *
     * @return string|null null signals failure and the caller should stop.
     */
    private function resolvePassword(): ?string
    {
        $password = (string) $this->option('password');

        if ($password !== '') {
            return $password;
        }

        if (! $this->input->isInteractive()) {
            $this->components->error('No password supplied and no interactive terminal available. Re-run with --password.');

            return null;
        }

        $password = (string) $this->secret('Password');
        $confirmation = (string) $this->secret('Confirm password');

        if ($password === '') {
            $this->components->error('The password cannot be empty.');

            return null;
        }

        if (! hash_equals($password, $confirmation)) {
            $this->components->error('The passwords do not match.');

            return null;
        }

        return $password;
    }
}
