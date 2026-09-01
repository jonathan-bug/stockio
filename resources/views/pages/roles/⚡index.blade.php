<?php

use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $roles = Role::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->paginate(10);

        return $this->view([
            'roles' => $roles
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de roles</h3>
                <a href="{{ route('roles.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" placeholder="Buscar por nombre" wire:model.live="search">
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td>{{ $role->name }}</td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @can('roles.permissions')
                                <a href="{{ route('roles.permissions', $role) }}" class="btn btn-primary">
                                    <i class="fa fa-list"></i>
                                </a>
                                @endcan
                                @can('roles.edit')
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning" wire:navigate>
                                    <i class="fa fa-pen"></i>
                                </a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12">
            {{ $roles->links() }}
        </div>
    </div>
</div>