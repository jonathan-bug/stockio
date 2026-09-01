<?php

use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Permission\Models\Role;

new class extends Component
{
    #[Validate('required|string|unique:roles,name')]
    public $name = '';

    public function save()
    {
        $this->validate();

        Role::create([
            'name' => $this->name
        ]);

        $this->reset('name');
        $this->dispatch('alert', message: 'Role created successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear rol</h3>
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