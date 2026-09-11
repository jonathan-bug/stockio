<?php

use App\Models\InventoryAdjustment;
use App\Models\Kardex;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

new class extends Component
{
    public $type = '';
    public $quantity = 0;
    public $reason = '';

    public Product $product;

    public function mount(Product $product)
    {
        abort_unless($product->is_active, 404);

        $this->product = $product;
    }

    public function render()
    {
        $inventory_adjustments = $this->product->inventory_adjustments()->latest()->paginate(10);

        return $this->view([
            'inventory_adjustments' => $inventory_adjustments
        ]);
    }

    public function save()
    {
        $this->validate([
            'type' => ['required', 'in:1,2'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => 'nullable'
        ]);

        try {
            DB::transaction(function () {
                $productStock = ProductStock::where('product_id', $this->product->id)->first();

                if (!$productStock) {
                    throw new \Exception('The product does not have stock');
                }

                $quantity = (int) $this->quantity;

                if ((int) $this->type === 2 && $productStock->quantity < $quantity) {
                    throw new \Exception('The quantity cannot be bigger than the stock');
                }

                if ((int) $this->type === 1) {
                    $productStock->increment('quantity', $quantity);
                } else {
                    $productStock->decrement('quantity', $quantity);
                }

                $productStock->refresh();

                $inventory_adjustment = InventoryAdjustment::create([
                    'type' => (int) $this->type,
                    'quantity' => $quantity,
                    'reason' => $this->reason,
                    'product_id' => $this->product->id,
                    'user_id' => Auth::id()
                ]);

                Kardex::create([
                    'product_id' => $this->product->id,
                    'type' => 3,
                    'quantity' => $quantity,
                    'stock' => $productStock->quantity,
                    'description' => (int) $this->type === 1 ? 'Entrada por ajuste #' . $inventory_adjustment->id : 'Salida por ajuste #' . $inventory_adjustment->id,
                    'user_id' => Auth::id()
                ]);
            });

            $this->reset('type', 'quantity', 'reason');
            $this->dispatch('alert', message: 'Inventory adjustment completed successfully');
        } catch (\Exception $error) {
            $this->dispatch('alert', message: $error->getMessage(), type: 'warning');
        }
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear ajuste de inventario</h3>
                <a href="{{ route('inventory_adjustments.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="form-label">Producto</label>
                            <input type="text" class="form-control bg-light" value="{{ $this->product->name }}" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <label for="" class="form-label">Stock</label>
                        <input type="text" class="form-control bg-light" value="{{ $this->product->stock->quantity }}" readonly>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="type" class="form-label">Tipo</label>
                            <select id="type" class="form-select" wire:model="type">
                                <option value="">Seleccione tipo de ajuste</option>
                                <option value="1">Entrada</option>
                                <option value="2">Salida</option>
                            </select>
                            @error('type')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="quantity" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" min="1" wire:model="quantity">
                            @error('quantity')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="reason" class="form-label">Motivo</label>
                            <input type="text" class="form-control" wire:model="reason">
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
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Tipo de ajuste</th>
                        <th>Cantidad</th>
                        <th>Usuario</th>
                        <th>Motivo</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inventory_adjustments as $inventory_adjustment)
                    <tr>
                        <td>
                            @if($inventory_adjustment->type === 1)
                            <span class="badge bg-primary">Entrada</span>
                            @else
                            <span class="badge bg-secondary">Salida</span>
                            @endif
                        </td>
                        <td>{{ $inventory_adjustment->quantity }}</td>
                        <td>{{ $inventory_adjustment->user->name }}</td>
                        <td>{{ $inventory_adjustment->reason ?: '-' }}</td>
                        <td>{{ $inventory_adjustment->created_at }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>