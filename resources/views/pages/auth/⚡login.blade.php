<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component
{
    #[Validate('required|email')]
    public $email = '';
    #[Validate('required')]
    public $password = '';
    public $remember = false;

    public function login()
    {
        // Validar
        $this->validate();

        // Iniciar sesión
        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'The email or password is invalid.');

            return;
        }

        // Regenerar sesión
        request()->session()->regenerate();

        // Inicio
        $this->redirectRoute('dashboard');
    }
};
?>

<div class="container vh-100">
    <div class="row h-100 justify-content-center align-items-center">
        <div class="col-12 col-lg-4 col-md-6">
            <form class="card" wire:submit="login">
                <div class="card-header p-3">
                    <h4 class="card-title text-center fs-1 fw-bold text-primary">Stockio</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" placeholder="email@example.com" wire:model="email">
                        @error('email')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group mt-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" class="form-control" wire:model="password">
                        @error('password')
                        <span class="text-danger text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-check mt-3">
                        <input type="checkbox" id="remember" class="form-check-input" wire:model="remember">
                        <label for="remember" class="form-check-label">Recuérdame</label>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-2 align-items-center ">
                        <button class="btn btn-primary" type="submit">Iniciar sesión</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>