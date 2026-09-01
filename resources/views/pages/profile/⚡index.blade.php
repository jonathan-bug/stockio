<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    // User
    public $name = '';
    public $email = '';

    // Password
    public $password = '';
    public $newPassword = '';
    public $newPassword_confirmation = '';

    public function mount()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function save()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)]
        ]);

        $user->update([
            'name' => $this->name,
            'email' => $this->email
        ]);

        $this->dispatch('alert', message: 'Profile updated successfully');
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => 'required',
            'newPassword' => 'required|min:8|confirmed'
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Hash::check($this->password, $user->password)) {
            $this->addError('password', 'The password is not correct.');

            return;
        }

        $user->update([
            'password' => Hash::make($this->newPassword)
        ]);

        $this->reset('password', 'newPassword', 'newPassword_confirmation');
        $this->dispatch('alert', message: 'Password updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-12">
                        <h3>Información de la cuenta</h3>
                        <hr>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control" wire:model="name">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input id="email" type="email" class="form-control" wire:model="email">
                            @error('email')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-12">
            <form wire:submit="updatePassword">
                <div class="row gy-3">
                    <div class="col-12">
                        <h3>Seguridad</h3>
                        <hr>
                    </div>
                    <div class="col-12">
                        <label for="password" class="form-label">Contraseña actual</label>
                        <input id="password" type="password" class="form-control" wire:model="password">
                        @error('password')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="newPassword" class="form-label">Contraseña nueva</label>
                        <input id="newPassword" type="password" class="form-control" wire:model="newPassword">
                        @error('newPassword')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-6">
                        <label for="newPasswordConfirmation" class="form-label">Confirmar contraseña</label>
                        <input id="newPasswordConfirmation" type="password" class="form-control" wire:model="newPassword_confirmation">
                        @error('newPassword_confirmation')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12">
                        <div class="form-group d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>