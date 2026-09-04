<?php

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $status = '';
    public $supplier = '';
    public Collection $suppliers;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedSupplier()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->suppliers = Supplier::all();
    }

    public function render()
    {
        $purchases = Purchase::with('supplier')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                        ->orWhere('date', 'like', '%' . $this->search . '%');
                });
            })->when($this->status !== '', function ($query) {
                $query->where('status', (int) $this->status);
            })->when($this->supplier !== '', function ($query) {
                $query->where('supplier_id', (int) $this->supplier);
            })->paginate(10);

        return $this->view([
            'purchases' => $purchases
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de compras</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por número de factura y fecha">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="supplier">
                        <option value="">Todos</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="status">
                        <option value="">Todos</option>
                        <option value="1">Pendiente</option>
                        <option value="2">Aplicada parcialmente</option>
                        <option value="3">Aplicada</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchases as $purchase)
                    <tr>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier->name }}</td>
                        <td>{{ $purchase->total }}</td>
                        <td>{{ $purchase->date }}</td>
                        <td>{{ $purchase->status }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12">
            {{ $purchases->links() }}
        </div>
    </div>
</div>