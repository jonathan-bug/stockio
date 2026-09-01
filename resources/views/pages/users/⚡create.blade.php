<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $role = 0;
    public $is_active = true;

    public Collection $roles;

    public function mount()
    {
        $this->roles = Role::all();
        $this->role = $this->roles->first()?->id ?? 0;
        $this->password = Str::random(12);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => 'required|boolean',
            'password' => 'required'
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'password' => Hash::make($this->password)
        ]);

        $role = Role::findOrFail($this->role);
        $user->assignRole($role);

        $this->dispatch('user-created', password: $this->password);
        $this->reset('name', 'email', 'role', 'is_active', 'password');
        $this->role = $this->roles->first()?->id ?? 0;
    }

    public function regeneratePassword()
    {
        $this->password = Str::random(12);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div
            x-data="{ show: false, password: '' }"
            x-on:user-created.window="password = $event.detail.password; show = true"
            x-show="show"
            x-cloak
            x-transition
            class="alert alert-success mt-3">
            <div class="d-flex justify-content-between align-items-center">
                <span>
                    Usuario creado correctamente. Contraseña temporal:
                    <strong x-text="password"></strong>
                </span>

                <button type="button" class="btn-close" @click="show = false"></button>
            </div>
        </div>
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear usuario</h3>
                <a href="{{ route('users.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>

        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
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
                    <div class="col-6">
                        <div class="form-group">
                            <label for="role" class="form-label">Rol</label>
                            <select id="role" class="form-select" wire:model="role">
                                @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="is_active" class="form-label">Estado</label>
                            <select id="is_active" class="form-select" wire:model.boolean="is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('is_active')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="d-flex gap-4">
                            <input id="password" type="text" class="form-control" wire:model="password">
                            <button type="button" class="btn btn-primary" wire:click="regeneratePassword">Regenerar</button>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>