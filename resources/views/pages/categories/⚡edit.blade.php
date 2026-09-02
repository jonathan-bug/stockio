<?php

use App\Models\Category;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    #[Validate('required')]
    public $name = '';
    public $is_active = 0;
    public Category $category;

    public function mount(Category $category)
    {
        $this->category = $category;
        $this->name = $this->category->name;
        $this->is_active = $this->category->is_active;
    }

    public function save()
    {
        $this->validate([
            'name' => 'required',
            'is_active' => 'required'
        ]);

        $this->category->update([
            'name' => $this->name,
            'is_active' => $this->is_active
        ]);

        $this->dispatch('alert', message: 'Category updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar categoria</h3>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
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