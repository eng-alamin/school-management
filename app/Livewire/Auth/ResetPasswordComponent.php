<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
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
            'email.required'                 => 'Email দিন।',
            'email.email'                    => 'সঠিক Email দিন।',
            'email.exists'                   => 'এই Email আমাদের সিস্টেমে নেই।',
            'password.required'              => 'নতুন Password দিন।',
            'password.min'                   => 'Password কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed'             => 'Password দুটো মিলছে না।',
            'password_confirmation.required' => 'Password আবার দিন।',
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
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            session()->flash('success', 'Password সফলভাবে পরিবর্তন হয়েছে। এখন Login করুন।');
            $this->redirect(route('login'));
            return;
        }

        // Token invalid বা expired
        $this->addError('email', match($status) {
            Password::INVALID_TOKEN => 'Reset link expired বা invalid। নতুন link request করুন।',
            Password::INVALID_USER  => 'এই Email আমাদের সিস্টেমে নেই।',
            default                 => 'কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।',
        });
    }

    public function render()
    {
        return view('livewire.auth.reset-password-component')
            ->layout('layouts.app', [
                'title' => 'Reset Password | Monarchy School',
            ]);
    }
}