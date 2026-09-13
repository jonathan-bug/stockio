<?php

use App\Models\Sale;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component
{
    public Sale $sale;

    public function mount(Sale $sale)
    {
        $this->sale = $sale;
    }

    public function getSubtotalProperty()
    {
        return collect($this->sale->details)->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
    }

    public function getDiscountProperty()
    {
        return collect($this->sale->details)->sum(fn($item) => $item->discount);
    }

    public function getTotalProperty()
    {
        return $this->subtotal - $this->discount;
    }
};
?>

<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Venta #{{ $sale->payment_reference }}</h3>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row">
                <div class="col-12 mb-4">
                    <table class="">
                        <tbody>
                            <tr>
                                <td>Fecha:</td>
                                <td>{{ $sale->created_at }}</td>
                            </tr>
                            <tr>
                                <td>Referencia:</td>
                                <td>{{ $sale->payment_reference }}</td>
                            </tr>
                            <tr>
                                <td>Método:</td>
                                <td>
                                    @if($sale->payment_method == 1)
                                    <span class="badge bg-success">Efectivo</span>
                                    @elseif($sale->payment_method == 2)
                                    <span class="badge bg-primary">Tarjeta</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12">
                    <h4>Productos</h4>
                    <hr>
                    <table class="table table-light table-borderless">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unitario</th>
                                <th>Descuento</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->details as $detail)
                            <tr>
                                <td>{{ $detail->product->name }}</td>
                                <td>{{ $detail->quantity }}</td>
                                <td>${{ number_format($detail->unit_price, 2) }}</td>
                                <td>${{ number_format($detail->discount, 2) }}</td>
                                <td>${{ number_format($detail->quantity * $detail->unit_price - $detail->discount , 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <hr>
                </div>
                <div class="col-12 mb-4">
                    <table class="table-light table-borderless">
                        <tbody>
                            <tr>
                                <td>Subtotal</td>
                                <td>${{ number_format($this->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Descuento</td>
                                <td>${{ number_format($this->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Total</td>
                                <td>${{ number_format($this->total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12">
                    <table class="table-light table-borderless">
                        <tbody>
                            <tr>
                                <td>Monto ingresado</td>
                                <td>${{ number_format($sale->payment_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td>Cambio</td>
                                <td>${{ number_format($sale->payment_change, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>