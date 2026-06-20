<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Password;

class ForgotPasswordComponent extends Component
{
    public $identifier = ''; // email / username / phone
    public $successMessage = '';

    protected $rules = [
        'identifier' => 'required|string',
    ];

    protected function messages(): array
    {
        return [
            'identifier.required' => 'Email, Username বা Phone নম্বর দিন।',
        ];
    }

    public function mount()
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            return redirect('/dashboard');
        }
    }

    public function sendResetLink()
    {
        $this->validate();

        // Smart detection — কোন field দিয়ে খুঁজবে
        $field = filter_var($this->identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (preg_match('/^[0-9+\-\s]+$/', $this->identifier)
                ? 'phone'
                : 'username');

        $user = User::where($field, $this->identifier)->first();

        if (! $user) {
            $this->addError('identifier', 'এই তথ্য আমাদের সিস্টেমে নেই।');
            return;
        }

        if (! $user->email) {
            $this->addError('identifier', 'এই একাউন্টে কোনো Email নেই। Admin-এর সাথে যোগাযোগ করুন।');
            return;
        }

        // Password reset link পাঠাও
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->successMessage = 'Password reset link আপনার Email-এ পাঠানো হয়েছে।';
            $this->identifier = '';
        } else {
            $this->addError('identifier', 'কিছু একটা সমস্যা হয়েছে। আবার চেষ্টা করুন।');
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot-password-component')
            ->layout('layouts.app', [
                'title' => 'Forgot Password | Monarchy School',
            ]);
    }
}