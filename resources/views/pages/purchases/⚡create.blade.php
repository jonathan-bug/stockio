<?php

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component
{
    public $invoice_number = '';
    public $supplier = 0;
    public $total = 0;
    public $date = '';

    public Collection $suppliers;

    public function mount()
    {
        $this->suppliers = Supplier::where('is_active', true)->get();
        $this->supplier = $this->suppliers->first()?->id ?? 0;
        $this->date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'supplier' => ['required', 'exists:suppliers,id'],
            'total' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'date' => ['required', 'date']
        ]);

        Purchase::create([
            'invoice_number' => $this->invoice_number,
            'supplier_id' => $this->supplier,
            'total' => $this->total,
            'date' => $this->date,
            'status' => 1
        ]);

        $this->dispatch('alert', message: 'Purchase created successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Agregar compra</h3>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="invoice_number" class="form-label">Número de factura</label>
                            <input id="invoice_number" type="text" class="form-control" wire:model="invoice_number">
                            @error('invoice_number')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="supplier" class="form-label">Proveedor</label>
                            <select id="supplier" class="form-select" wire:model="supplier">
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            @error('supplier')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="total" class="form-label">Costo total</label>
                            <input type="number" step="0.01" class="form-control" wire:model="total">
                            @error('total')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="date" class="form-label">Fecha</label>
                            <input id="date" type="date" class="form-control" wire:model="date">
                            @error('date')
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