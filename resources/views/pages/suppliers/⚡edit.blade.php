<?php

use App\Models\Supplier;
use Livewire\Component;

new class extends Component
{
    public $name = '';
    public $phone = '';
    public $email = '';
    public $is_active = 0;

    public Supplier $supplier;

    public function mount(Supplier $supplier)
    {
        $this->supplier = $supplier;

        $this->name = $this->supplier->name;
        $this->phone = $this->supplier->phone;
        $this->email = $this->supplier->email;
        $this->is_active = $this->supplier->is_active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => ['nullable', 'email'],
            'is_active' => ['required', 'boolean']
        ]);

        $this->supplier->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active
        ]);

        $this->dispatch('alert', message: 'Supplier updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar proveedor</h3>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
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
                            <label for="phone" class="form-label">Teléfono</label>
                            <input id="phone" type="text" class="form-control" wire:model="phone">
                            @error('phone')
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
    </div>
</div>