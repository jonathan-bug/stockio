<?php

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $is_active = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedIsActive()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->when($this->is_active !== '', function ($query) {
            $query->where('is_active', (int) $this->is_active);
        })->paginate(10);

        return $this->view([
            'categories' => $categories
        ]);
    }

    public function deactivate(int $id)
    {
        $category = Category::findOrFail($id);
        $category->update([
            'is_active' => false
        ]);

        $this->dispatch('alert', message: 'Category deactivated successfully');
    }

    public function activate(int $id)
    {
        $category = Category::findOrFail($id);
        $category->update([
            'is_active' => true
        ]);

        $this->dispatch('alert', message: 'Category activated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de categorias</h3>
                <a href="{{ route('categories.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" placeholder="Buscar por nombre" wire:model.live="search">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="is_active">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>
                            @if($category->is_active)
                            <div class="badge text-bg-success">Activo</div>
                            @else
                            <div class="badge text-bg-secondary">Inactivo</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @can('categories.edit')
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning" wire:navigate>
                                    <i class="fa fa-pen"></i>
                                </a>
                                @endcan
                                @if($category->is_active)
                                @can('categories.deactivate')
                                <button class="btn btn-danger" wire:click="deactivate({{ $category->id }})">
                                    <i class="fa fa-user-slash"></i>
                                </button>
                                @endcan
                                @else
                                @can('categories.activate')
                                <button class="btn btn-success" wire:click="activate({{ $category->id }})">
                                    <i class="fa fa-rotate-left"></i>
                                </button>
                                @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>