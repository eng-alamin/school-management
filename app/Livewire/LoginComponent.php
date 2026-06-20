<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginComponent extends Component
{
    public $identifier = ''; // email / username / phone
    public $password = '';
    public $remember = false;

    protected $rules = [
        'identifier' => 'required|string',
        'password'   => 'required|min:4',
    ];

    protected function messages(): array
    {
        return [
            'identifier.required' => 'Email, Username বা Phone নম্বর দিন।',
            'password.required'   => 'Password দিন।',
            'password.min'        => 'Password কমপক্ষে ৪ অক্ষরের হতে হবে।',
        ];
    }

    public function mount()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }
    }

    public function login()
    {
        $this->validate();

        // Smart detection — কোন field দিয়ে login করছে
        $field = filter_var($this->identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : (preg_match('/^[0-9+\-\s]+$/', $this->identifier)
                ? 'phone'
                : 'username');

        $user = User::where($field, $this->identifier)->first();

        // Password চেক
        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->password = '';
            $this->addError('identifier', 'এই তথ্য আমাদের সিস্টেমে নেই।');
            return;
        }

        // Account active কিনা চেক
        if (! $user->is_active) {
            $this->addError('identifier', 'আপনার একাউন্ট নিষ্ক্রিয়। Admin-এর সাথে যোগাযোগ করুন।');
            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        // Last login info আপডেট
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        $this->redirect(route('admin.dashboard'));
    }

    public function render()
    {
        return view('livewire.login-component')
            ->layout('layouts.app', [
                'title' => 'Login | Monarchy School',
            ]);
    }
}