<?php

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component
{
    public $invoice_number = '';
    public $supplier = 0;
    public $date = '';

    public Collection $suppliers;
    public Purchase $purchase;
    public Collection $products;
    public $details = [];

    public function mount(Purchase $purchase)
    {
        $this->purchase = $purchase;
        $this->suppliers = Supplier::where('is_active', true)
            ->orWhere('id', $this->purchase->supplier->id)
            ->get();
        $this->supplier = $this->purchase->supplier_id;
        $this->date = $this->purchase->date;

        $this->products = Product::where('is_active', true)->get();

        $this->details = $this->purchase->details()
            ->get()
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity,
                    'unit_cost' => $detail->unit_cost,
                    'is_applied' => $detail->is_applied
                ];
            })->toArray();
    }

    public function save()
    {
        $this->validate([
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'supplier' => ['required', 'exists:suppliers,id'],
            'date' => ['required', 'date']
        ]);

        $this->purchase->update([
            'invoice_number' => $this->invoice_number,
            'supplier_id' => $this->supplier,
            'date' => $this->date
        ]);

        $this->dispatch('alert', message: 'Purchase updated successfully');
    }

    public function pushDetail()
    {
        $this->details[] = [
            'id' => null,
            'product_id' => '',
            'quantity' => 1,
            'unit_cost' => 0,
            'is_applied' => false
        ];
    }

    public function popDetail(int $index)
    {
        if ($this->details[$index]['is_applied']) {
            return;
        }

        unset($this->details[$index]);
        $this->details = array_values($this->details);
    }

    public function updateStatus()
    {
        $totalDetails = $this->purchase->details()->count();
        $appliedDetails = $this->purchase->details()
            ->where('is_applied', true)
            ->count();

        if ($totalDetails == 0) {
            $status = 1;
        } else if ($totalDetails == $appliedDetails) {
            $status = 3;
        } else {
            $status = 2;
        }

        $this->purchase->update([
            'status' => $status
        ]);
    }

    public function saveDetails()
    {
        $this->validate([
            'details.*.product_id' => ['required', 'exists:products,id'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
            'details.*.unit_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0']
        ]);

        $currentDetails = collect($this->details)
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->toArray();

        $this->purchase->details()
            ->where('is_applied', false)
            ->whereNotIn('id', $currentDetails)
            ->delete();

        foreach ($this->details as $index => $detail) {
            if ($detail['is_applied']) {
                continue;
            }

            if ($detail['id']) {
                $purchaseDetail = $this->purchase->details()->findOrFail($detail['id']);
                $purchaseDetail->update([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_cost' => $detail['unit_cost']
                ]);
            } else {
                $purchaseDetail = $this->purchase->details()->create([
                    'product_id' => $detail['product_id'],
                    'quantity' => $detail['quantity'],
                    'unit_cost' => $detail['unit_cost'],
                    'is_applied' => false
                ]);

                $this->details[$index]['id'] = $purchaseDetail->id;
            }
        }

        $this->purchase->update([
            'total' => $this->total
        ]);

        $this->updateStatus();
        $this->dispatch('alert', message: 'Purchase details updated successfully');
    }

    public function getTotalProperty()
    {
        return collect($this->details)->sum(function ($detail) {
            return (int) ($detail['quantity'] ?? 0) * (float) ($detail['unit_cost'] ?? 0);
        });
    }

    public function applyDetails()
    {
        $details = $this->purchase->details()->where('is_applied', false)->get();
        foreach ($details as $detail) {
            $detail->update([
                'is_applied' => true
            ]);
        }

        $this->updateStatus();
        $this->details = $this->purchase->details()
            ->get()
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'product_id' => $detail->product_id,
                    'quantity' => $detail->quantity,
                    'unit_cost' => $detail->unit_cost,
                    'is_applied' => $detail->is_applied
                ];
            })->toArray();

        $this->dispatch('alert', message: 'Purchase details applied successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Editar compra</h3>
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
                            <input type="text" class="form-control" wire:model="invoice_number">
                            @error('invoice_number')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="supplier" class="form-label">Proveedor</label>
                            <select id="supplier" class="form-control" wire:model="supplier">
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
                            <input id="total" type="text" class="form-control bg-light" value="${{ number_format($this->total, 2) }}" readonly>
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
        <div class="col-12">
            <div class="d-flex align-items-center">
                <h3>Gestión de detalles de compra</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Costo unitario</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $index => $detail)
                    <tr>
                        <td>
                            <select class="form-select" wire:model="details.{{ $index }}.product_id" @disabled($detail['is_applied'])>
                                <option value="">Seleccione un producto</option>

                                @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error("details.$index.product_id")
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" class="form-control" wire:model.live="details.{{ $index }}.quantity" @disabled($detail['is_applied'])>
                            @error("details.$index.quantity")
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            <input type="number" step="0.01" class="form-control" wire:model.live="details.{{ $index }}.unit_cost" @disabled($detail['is_applied'])>
                            @error("details.$index.unit_cost")
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </td>
                        <td>
                            ${{ number_format((int) ($detail['quantity'] ?? 0) * (float) ($detail['unit_cost'] ?? 0), 2) }}
                        </td>
                        <td>
                            @if($detail['is_applied'])
                            <span class="badge bg-success">Aplicado</span>
                            @else
                            <span class="badge bg-dark">Pendiente</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end">
                                @if(!$detail['is_applied'])
                                <button class="btn btn-danger" wire:click="popDetail({{ $index }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <button class="btn btn-dark" wire:click="applyDetails">Aplicar</button>
                <button class="btn btn-primary" wire:click="pushDetail">Agregar</button>
                <div class="vr"></div>
                <button class="btn btn-success" wire:click="saveDetails">Guardar</button>
            </div>
        </div>
    </div>
</div>