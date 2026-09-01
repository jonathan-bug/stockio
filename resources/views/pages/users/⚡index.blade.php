<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $role = '';
    public $is_active = '';

    public Collection $roles;

    public function mount()
    {
        $this->roles = Role::all();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRole()
    {
        $this->resetPage();
    }

    public function updatedIsActive()
    {
        $this->resetPage();
    }

    public function render()
    {
        $users = User::with('roles')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })->when($this->role, function ($query) {
                $query->role($this->role);
            })->when($this->is_active !== '', function ($query) {
                $query->where('is_active', (int) $this->is_active);
            })->paginate(10);

        return $this->view([
            'users' => $users
        ]);
    }

    public function activate(int $id)
    {
        $user = User::findOrFail($id);
        $user->update([
            'is_active' => true
        ]);

        $this->dispatch('alert', message: 'User activated successfully');
    }

    public function deactivate(int $id)
    {
        $user = User::findOrFail($id);

        if ($user->id == Auth::id()) {
            $this->dispatch('alert', message: 'You cannot deactivate yourself', type: 'danger');

            return;
        }

        $user->update([
            'is_active' => false
        ]);

        $this->dispatch('alert', message: 'User deactivated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de usuarios</h3>
                <a href="{{ route('users.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" placeholder="Buscar por nombre o email" wire:model.live="search">
                </div>

                <div class="col-md-3">
                    <select class="form-select" wire:model.live="role">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
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
            <table class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->roles->first()?->name ?? '-' }}</td>
                        <td>
                            @if($user->is_active)
                            <span class="badge text-bg-success">Activo</span>
                            @else
                            <span class="badge text-bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @can('users.edit')
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-warning" wire:navigate>
                                    <i class="fa fa-pen"></i>
                                </a>
                                @endcan
                                @if($user->is_active)
                                @can('users.deactivate')
                                <button class="btn btn-danger" wire:click="deactivate({{ $user->id }})">
                                    <i class="fa fa-user-slash"></i>
                                </button>
                                @endcan
                                @else
                                @can('users.activate')
                                <button class="btn btn-success" wire:click="activate({{ $user->id }})">
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
            {{ $users->links() }}
        </div>
    </div>
</div>