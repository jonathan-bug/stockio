<?php

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public Role $role;
    public $name = '';

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $this->role->name;
    }

    public function save()
    {
        $this->validate([
            'name' => ['required', 'string', Rule::unique('roles', 'name')->ignore($this->role->id)]
        ]);

        $this->role->update([
            'name' => $this->name
        ]);

        $this->dispatch('alert', message: 'Role updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar rol</h3>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="name" class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control" wire:model="name">
                            @error('name')
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
    </div>
</div>