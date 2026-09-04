<?php

use App\Models\Supplier;
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
        $suppliers = Supplier::when($this->search, function ($query) {
            $query->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        })->when($this->is_active !== '', function ($query) {
            $query->where('is_active', (int) $this->is_active);
        })->paginate(10);

        return $this->view([
            'suppliers' => $suppliers
        ]);
    }

    public function activate(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'is_active' => true
        ]);

        $this->dispatch('alert', message: 'Supplier activated successfully');
    }

    public function deactivate(int $id)
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            'is_active' => false
        ]);

        $this->dispatch('alert', message: 'Supplier deactivated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de proveedores</h3>
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por nombre, teléfono o email">
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
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone }}</td>
                        <td>{{ $supplier->email ?: '-' }}</td>
                        <td>
                            @if($supplier->is_active)
                            <span class="badge bg-success">Activo</span>
                            @else
                            <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @can('suppliers.edit')
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-warning" wire:navigate>
                                    <i class="fa fa-pen"></i>
                                </a>
                                @endcan
                                @if($supplier->is_active)
                                @can('suppliers.deactivate')
                                <button class="btn btn-danger" wire:click="deactivate({{ $supplier->id }})">
                                    <i class="fa fa-user-slash"></i>
                                </button>
                                @endcan
                                @else
                                @can('suppliers.activate')
                                <button class="btn btn-success" wire:click="activate({{ $supplier->id }})">
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
        <div class="col-12">
            {{ $suppliers->links() }}
        </div>
    </div>
</div>