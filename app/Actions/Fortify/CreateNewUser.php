<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'username' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique(User::class)],
            'password' => $this->passwordRules(),
        ])->validate();

        $uuid = (string) \Illuminate\Support\Str::uuid();

        return User::create([
            'id' => $uuid,
            'username' => $input['username'],
            'password' => $input['password'],
            'role_id' => 99, // default unassigned/guest
            'authable_type' => User::class,
            'authable_id' => $uuid,
        ]);
    }
}
