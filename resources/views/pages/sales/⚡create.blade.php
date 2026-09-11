<?php

use App\Models\Kardex;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $cart = [];
    public $quantities = [];

    public $showProcessing = false;
    public $payment_method = '';
    public $payment_reference = '';
    public $payment_amount = 0;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function processSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', message: 'The cart is empty', type: 'warning');
            return;
        }

        $this->showProcessing = true;
    }

    public function addToCart(int $id)
    {
        $product = Product::with('stock')->findOrFail($id);
        $stock = $product->stock->quantity ?? 0;
        $quantity = (int) ($this->quantities[$id] ?? 1);

        if ($quantity < 1) {
            $this->dispatch('alert', message: 'Invalid quantity', type: 'warning');
            return;
        }

        if ($quantity > $stock) {
            $this->dispatch('alert', message: 'Insufficient stock', type: 'warning');
            return;
        }

        foreach ($this->cart as $index => $item) {
            if ($item['product_id'] == $product->id) {
                $newQuantity = $item['quantity'] + $quantity;

                if ($newQuantity > $stock) {
                    $this->dispatch('alert', message: 'Insufficient stock', type: 'warning');
                    return;
                }

                $this->cart[$index]['quantity'] = $newQuantity;
                return;
            }
        }

        $this->cart[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => $quantity,
            'unit_price' => $product->sales_price,
            'discount' => 0
        ];
    }

    public function updateCartQuantity(int $index)
    {
        $product = Product::with('stock')->findOrFail($this->cart[$index]['product_id']);
        $quantity = (int) $this->cart[$index]['quantity'];
        $stock = $product->stock->quantity ?? 0;

        if ($quantity < 1) {
            $this->cart[$index]['quantity'] = 1;
            return;
        }

        if ($quantity > $stock) {
            $this->cart[$index]['quantity'] = $stock;
            $this->dispatch('alert', message: 'Insufficient stock', type: 'warning');
        }
    }

    public function updateCartDiscount(int $index)
    {
        $quantity = (int) $this->cart[$index]['quantity'];
        $unitPrice = (float) $this->cart[$index]['unit_price'];
        $discount = (float) $this->cart[$index]['discount'];
        $subtotal = $quantity * $unitPrice;

        if ($discount < 0) {
            $this->cart[$index]['discount'] = 0;
            return;
        }

        if ($discount > $subtotal) {
            $this->cart[$index]['discount'] = $subtotal;
            $this->dispatch('alert', message: 'The discount cannot exceed the product amount', type: 'warning');
        }
    }

    public function validateProcess()
    {
        if (empty($this->cart)) {
            $this->dispatch('alert', message: 'The cart is empty', type: 'warning');
            return;
        }

        if (!in_array((int) $this->payment_method, [1, 2])) {
            $this->dispatch('alert', message: 'Select a payment method', type: 'warning');
            return;
        }

        if (trim($this->payment_reference) == '') {
            $this->dispatch('alert', message: 'Enter a payment reference', type: 'warning');
            return;
        }

        if ((int) $this->payment_method == 1 && (float) $this->payment_amount < $this->total) {
            $this->dispatch('alert', message: 'The payment amount is insufficient', type: 'warning');
            return;
        }

        $this->dispatch('alert', message: 'Payment validated successfully', type: 'success');

        try {
            DB::transaction(function () {
                $paymentAmount = (int) $this->payment_method === 1 ? (float) $this->payment_amount : (float) $this->total;
                $paymentChange = (int) $this->payment_method === 1 ? max(0, $paymentAmount - $this->total) : 0;

                $sale = Sale::create([
                    'total' => $this->total,
                    'payment_method' => (int) $this->payment_method,
                    'payment_reference' => trim($this->payment_reference),
                    'payment_amount' => $paymentAmount,
                    'payment_change' => $paymentChange
                ]);

                foreach ($this->cart as $item) {
                    $productStock = ProductStock::where('product_id', $item['product_id'])->first();

                    if (!$productStock || $productStock->quantity < (int) $item['quantity']) {
                        throw new \Exception('Insufficient stock');
                    }

                    $quantity = (int) $item['quantity'];
                    $unitPrice = (float) $item['unit_price'];
                    $discount = (float) $item['discount'];

                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount' => $discount
                    ]);

                    $productStock->decrement('quantity', $quantity);
                    $productStock->refresh();

                    Kardex::create([
                        'product_id' => $item['product_id'],
                        'type' => 2,
                        'quantity' => $quantity,
                        'stock' => $productStock->quantity,
                        'description' => 'Salida por venta #' . $sale->id,
                        'user_id' => Auth::id()
                    ]);
                }
            });

            $this->cart = [];
            $this->quantities = [];
            $this->showProcessing = false;
            $this->payment_method = '';
            $this->payment_reference = '';
            $this->payment_amount = 0;

            session()->flash('success', 'Sale processed successfully');

            return $this->redirectRoute('sales.index', navigate: true);
        } catch (\Exception $error) {
            $this->dispatch('alert', message: $error->getMessage(), type: 'warning');
        }
    }

    public function render()
    {
        $products = Product::with('stock')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('barcode', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%');
                });
            })->paginate(10);

        return $this->view([
            'products' => $products
        ]);
    }

    public function removeFromCart(int $index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return (int) $item['quantity'] * (float) $item['unit_price'];
        });
    }

    public function getTotalProperty()
    {
        return $this->subtotal - $this->discount;
    }

    public function getDiscountProperty()
    {
        return collect($this->cart)->sum(function ($item) {
            return (float) $item['discount'];
        });
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Agregar venta</h3>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>

        <div class="col-12 col-md-8">
            <div class="row g-3">
                <div class="col-12">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por código de barras o producto">
                </div>
                <div class="col-12">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Código de barras</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                            <tr>
                                <td>{{ $product->barcode ?: '-' }}</td>
                                <td>{{ $product->name }}</td>
                                <td>${{ $product->sales_price }}</td>
                                <td>{{ $product->stock->quantity ?? 0 }}</td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" class="form-control" min="1" wire:model="quantities.{{ $product->id }}">
                                        <button class="btn btn-primary" wire:click="addToCart({{ $product->id }})">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4>Carrito</h4>
                </div>
                <div class="card-body">
                    @forelse($cart as $index => $item)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $item['name'] }}</strong>
                            <button class="btn btn-danger btn-sm" wire:click="removeFromCart({{ $index }})">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="input-group input-group-sm" style="width: 140px;">
                                <input type="number" class="form-control" min="1" wire:model="cart.{{ $index }}.quantity" wire:change="updateCartQuantity({{ $index }})">
                            </div>
                            <span>${{ number_format($item['quantity'] * $item['unit_price'], 2) }}</span>
                        </div>
                        <div class="input-group input-group-sm mt-2">
                            <span class="input-group-text">Descuento $</span>
                            <input type="number" class="form-control" min="0" step="0.01" wire:model.live="cart.{{ $index }}.discount" wire:change="updateCartDiscount({{ $index }})">
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted">
                        No hay productos agregados
                    </div>
                    @endforelse
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <span>Subtotal</span>
                        <strong>${{ number_format($this->subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Descuento</span>
                        <strong>${{ number_format($this->discount, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5">
                        <strong>Total</strong>
                        <strong>${{ number_format($this->total, 2) }}</strong>
                    </div>
                    <button class="btn btn-success w-100 mt-3" wire:click="processSale">Procesar</button>
                </div>
            </div>
            @if($showProcessing)
            <div class="card mt-3">
                <div class="card-header">
                    <h4>Pago</h4>
                </div>
                <div class="card-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Total a pagar</label>
                        <input type="text" class="form-control" value="${{ number_format($this->total, 2) }}" readonly>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Método de pago</label>
                        <select class="form-select" wire:model.live="payment_method">
                            <option value="">Seleccione un método</option>
                            <option value="1">Efectivo</option>
                            <option value="2">Tarjeta</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Número de referencia</label>
                        <input type="text" class="form-control" wire:model="payment_reference">
                    </div>

                    @if($payment_method == 1)
                    <div class="form-group mb-3">
                        <label class="form-label">Monto ingresado</label>
                        <input type="number" class="form-control" min="0" step="0.01" wire:model.live="payment_amount">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Cambio</label>
                        <input type="text" class="form-control" value="${{ number_format(max(0, (float) $payment_amount - $this->total), 2) }}" readonly>
                    </div>
                    @endif
                    <div class="d-flex gap-2">
                        <button class="btn btn-success w-100" wire:click="validateProcess">Confirmar venta</button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>