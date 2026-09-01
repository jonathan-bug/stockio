<?php

use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

new class extends Component
{
    public Role $role;
    public array $selectedPermissions = [];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->selectedPermissions = $this->role->permissions->pluck('name')->toArray();
    }

    public function render()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        });

        return $this->view([
            'permissions' => $permissions
        ]);
    }

    public function save()
    {
        $this->role->syncPermissions($this->selectedPermissions);
        $this->dispatch('alert', message: 'Permissions updated successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de permisos</h3>
                <a href="{{ route('roles.index') }}" class="btn btn-secondary">Volver</a>
            </div>
            <hr>
        </div>

        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    @foreach($permissions as $module => $modulePermissions)
                    <div class="col-12">
                        <div class="card p-3">
                            <h5>{{ ucfirst($module) }}</h5>

                            <div class="row">
                                @foreach($modulePermissions as $permission)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" wire:model="selectedPermissions" value="{{ $permission->name }}" id="permission_{{ $permission->id }}">
                                        <label for="permission_{{ $permission->id }}" class="form-label">{{ $permission->name }}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
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