<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

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
        $errors = [];

        if (! str_ends_with($input['email'], '@stargroup.com.mx')) {
            $errors[] = __('Only emails with the @stargroup.com.mx domain are allowed.');
        }

        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ]);

        if ($validator->fails()) {
            $errors = array_merge($errors, $validator->errors()->all());
        }

        if (! empty($errors)) {
            $errorMessages = '<ul style="list-style-type: disc; padding-left: 20px;">';

            foreach ($errors as $error) {
                $errorMessages .= "<li style='list-style-position: inside;'>$error</li>";
            }

            $errorMessages .= '</ul>';

            session()->flash('swal', [
                'icon' => 'error',
                'title' => '¡Error!',
                'html' => '<b>'.__('Your registration contains the following errors:').'</b><br><br>'.$errorMessages,
            ]);

            throw ValidationException::withMessages(['errors' => $errors]);
        }

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        return $user;
    }
}
