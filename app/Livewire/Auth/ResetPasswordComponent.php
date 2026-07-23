<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;

class ResetPasswordComponent extends Component
{
    public string $token    = '';
    public string $email    = '';
    public string $password = '';
    public string $password_confirmation = '';

    public bool   $showPassword        = false;
    public bool   $showConfirmPassword = false;

    // ── Submission Result (same pattern as TeacherRegistrationComponent) ──
    public bool $submitted = false;

    protected function rules(): array
    {
        return [
            'email'                 => 'required|email|exists:users,email',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required'                 => 'Please enter your email address.',
            'email.email'                    => 'Please enter a valid email address.',
            'email.exists'                   => 'This email is not registered in our system.',
            'password.required'              => 'Please enter a new password.',
            'password.min'                   => 'Password must be at least 8 characters.',
            'password.confirmed'             => 'Passwords do not match.',
            'password_confirmation.required' => 'Please confirm your new password.',
        ];
    }

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate();

        $status = Password::reset(
            [
                'email'                 => $this->email,
                'password'              => $this->password,
                'password_confirmation' => $this->password_confirmation,
                'token'                 => $this->token,
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->submitted = true;
            $this->reset(['password', 'password_confirmation']);

            $this->dispatch('toast', type: 'success', message: 'Password changed successfully!');
            $this->dispatch('scroll-top');

            return;
        }

        // Token invalid বা expired
        $this->addError('email', match ($status) {
            Password::INVALID_TOKEN => 'Reset link expired or invalid. Please request a new link.',
            Password::INVALID_USER  => 'This email is not registered in our system.',
            default                 => 'Something went wrong. Please try again.',
        });
    }

    public function render()
    {
        return view('livewire.auth.reset-password-component')
            ->layout('layouts.frontend.app', [
                'title' => 'Reset Password | Monarchy School',
            ]);
    }
}