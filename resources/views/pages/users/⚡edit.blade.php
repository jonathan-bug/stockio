<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public $name = '';
    public $email = '';
    public $is_active = 0;
    public $role = 0;
    public $password = '';

    public User $user;
    public Collection $roles;

    public function mount(User $user)
    {
        $this->user = $user;

        $this->roles = Role::all();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->role = $this->user->roles()->firstOrFail()->id;
        $this->is_active = $this->user->is_active;
        $this->password = Str::random(12);
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'role' => ['required', 'exists:roles,id'],
            'is_active' => 'boolean'
        ]);

        $this->user->name = $this->name;
        $this->user->email = $this->email;
        $this->user->is_active = $this->is_active;

        if ($this->user->id == Auth::id() && !$this->user->is_active) {
            $this->dispatch('alert', message: 'You cannot deactivate yourself', type: 'danger');

            return;
        }

        $this->user->save();

        $role = Role::findOrFail($this->role);
        $this->user->syncRoles($role);

        $this->dispatch('alert', message: 'User updated successfully');
    }

    public function regeneratePassword()
    {
        $this->password = Str::random(12);
    }

    public function updatePassword()
    {
        $this->validate([
            'password' => 'required|min:8'
        ]);

        $this->user->password = Hash::make($this->password);
        $this->user->save();

        $this->dispatch('alert', message: 'Password updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar usuario</h3>
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
                            <input id="email" type="text" class="form-control" wire:model="email">
                            @error('email')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" wire:model="role">
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
                            <select id="is_active" class="form-select" wire:model="is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('is_active')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
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
        <div class="col-12">
            <h3>Seguridad</h3>
            <hr>
        </div>

        <div class="col-12">
            <form wire:submit="updatePassword">
                <div class="row gy-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="d-flex gap-4">
                                <input type="text" class="form-control" wire:model="password">
                                <button type="button" class="btn btn-primary" wire:click="regeneratePassword">Regenerar</button>
                            </div>
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