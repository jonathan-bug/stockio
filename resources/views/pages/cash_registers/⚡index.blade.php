<?php

use App\Models\CashRegister;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public $from_date = '';
    public $to_date = '';
    public $user = '';
    public $status = '';

    public Collection $users;

    public function mount()
    {
        $this->users = User::all();
    }

    public function render()
    {
        $query = CashRegister::with('user');

        if (Auth::user()->can('cash_registers.view_all')) {
            $query->when($this->user !== '', fn($query) => $query->where('user_id', (int) $this->user));
        } else {
            $query->where('user_id', Auth::id());
        }

        $cash_registers = $query
            ->when($this->from_date, fn($query) => $query->whereDate('opened_at', '>=', $this->from_date))
            ->when($this->to_date, fn($query) => $query->whereDate('opened_at', '<=', $this->to_date))
            ->when($this->status !== '', fn($query) => $query->where('status', (int) $this->status))
            ->latest()
            ->paginate(10);

        return $this->view([
            'cash_registers' => $cash_registers
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de cajas registradoras</h3>
                <a href="{{ route('cash_registers.create') }}" class="btn btn-primary" wire:navigate>Abrir</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text">Desde</span>
                        <input type="date" class="form-control" wire:model.live="from_date">
                    </div>
                </div>
                <div class="col">
                    <div class="input-group">
                        <span class="input-group-text">Hasta</span>
                        <input type="date" class="form-control" wire:model.live="to_date">
                    </div>
                </div>
                @can('cash_registers.view_all')
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="user">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="status">
                        <option value="">Todas</option>
                        <option value="1">Abierta</option>
                        <option value="2">Cerrada</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        @can('cash_registers.view_all')
                        <th>Usuario</th>
                        @endcan
                        <th>Fecha de apertura</th>
                        <th>Fecha de cierre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cash_registers as $cash_register)
                    <tr>
                        @can('cash_registers.view_all')
                        <td>{{ $cash_register->user->name }}</td>
                        @endcan
                        <td>{{ $cash_register->opened_at }}</td>
                        <td>{{ $cash_register->closed_at ?: '-' }}</td>
                        <td>
                            @if($cash_register->status == 1)
                            <span class="badge bg-success">Abierta</span>
                            @elseif($cash_register->status == 2)
                            <span class="badge bg-secondary">Cerrada</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">
                                @if($cash_register->status == 1 && $cash_register->user_id == Auth::id())
                                <button class="btn btn-secondary">
                                    <i class="fa fa-lock"></i>
                                </button>
                                @endif
                                <a href="{{ route('cash_registers.show', $cash_register) }}" class="btn btn-primary" wire:navigate>
                                    <i class="fa fa-list"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>