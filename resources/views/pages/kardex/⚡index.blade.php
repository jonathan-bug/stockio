<?php

use App\Models\Kardex;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $type = '';
    public $from_date = '';
    public $to_date = '';
    public $user = '';
    public Collection $users;

    public function mount()
    {
        $this->users = User::all();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedType()
    {
        $this->resetPage();
    }

    public function updatedUser()
    {
        $this->resetPage();
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function render()
    {
        $movements = Kardex::with(['product', 'user'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function ($sub_query) {
                    $sub_query->where('products.name', 'like', '%' . $this->search . '%');
                });
            })->when($this->type !== '', function ($query) {
                $query->where('type', (int) $this->type);
            })->when($this->user !== '', function ($query) {
                $query->where('user_id', (int) $this->user);
            })->when($this->from_date !== '', function ($query) {
                $query->whereDate('created_at', '>=', $this->from_date);
            })->when($this->to_date !== '', function ($query) {
                $query->whereDate('created_at', '<=', $this->to_date);
            })
            ->latest()
            ->paginate(10);

        return $this->view([
            'movements' => $movements
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de kardex</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por producto">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="type">
                        <option value="">Todos los movimientos</option>
                        <option value="1">Compra</option>
                        <option value="2">Venta</option>
                        <option value="3">Ajuste</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="user">
                        <option value="">Todos los usuarios</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text">Desde</span>
                        <input type="date" class="form-control" wire:model.live="from_date">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group mb-3">
                        <span class="input-group-text">Hasta</span>
                        <input type="date" class="form-control" wire:model.live="to_date">
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Tipo de movimiento</th>
                        <th>Cantidad</th>
                        <th>Stock resultante</th>
                        <th>Usuario</th>
                        <th>Descripción</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                    <tr>
                        <td>{{ $movement->product->name }}</td>
                        <td>
                            @if($movement->type == 1)
                            <span class="badge bg-primary">Compra</span>
                            @elseif($movement->type == 2)
                            <span class="badge bg-secondary">Venta</span>
                            @else
                            <span class="badge bg-dark">Ajuste</span>
                            @endif
                        </td>
                        <td>{{ $movement->quantity }}</td>
                        <td>{{ $movement->stock }}</td>
                        <td>{{ $movement->user->name }}</td>
                        <td>{{ $movement->description }}</td>
                        <td>{{ $movement->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12">
            {{ $movements->links() }}
        </div>
    </div>
</div>