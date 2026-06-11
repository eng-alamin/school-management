<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginComponent extends Component
{
    public $email = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    protected function messages(): array
    {
        return [
            'email.required'    => 'Email address is required.',
            'email.email'       => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min'      => 'Password must be at least 6 characters.',
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

        $user = User::where('email', $this->email)->first();

        if (! $user || ! Hash::check($this->password, $user->password)) {
            $this->password = '';
            $this->addError('email', 'These credentials do not match our records.');
            return;
        }

        Auth::login($user, $this->remember);

        session()->regenerate();

        // Update last login info
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