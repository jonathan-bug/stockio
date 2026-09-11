<?php

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $payment_method = '';

    public function render()
    {
        $sales = Sale::when($this->search, function ($query) {
            $query->where(function ($query) {
                $query->where('payment_reference', 'like', '%' . $this->search . '%')
                    ->orWhere('total', 'like', '%' . $this->search . '%')
                    ->orWhere('created_at', 'like', '%' . $this->search . '%');
            });
        })->when($this->payment_method !== '', function ($query) {
            $query->where('payment_method', (int) $this->payment_method);
        })->paginate(10);

        return $this->view([
            'sales' => $sales
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de ventas</h3>
                <a href="{{ route('sales.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por número de referencia, total o fecha">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="payment_method">
                        <option value="">Todos los métodos de pago</option>
                        <option value="1">Efectivo</option>
                        <option value="2">Tarjeta</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Número de referencia</th>
                        <th>Método de pago</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                    <tr>
                        <td>{{ $sale->payment_reference }}</td>
                        <td>
                            @if($sale->payment_method == 1)
                            <span class="badge bg-success">Efectivo</span>
                            @elseif($sale->payment_method == 2)
                            <span class="badge bg-primary">Tarjeta</span>
                            @endif
                        </td>
                        <td>{{ $sale->total }}</td>
                        <td>{{ $sale->created_at }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12">
            {{ $sales->links() }}
        </div>
    </div>
</div>