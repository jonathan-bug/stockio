<?php

use App\Models\CashRegister;
use Livewire\Component;

new class extends Component
{
    public CashRegister $cash_register;

    public function mount(CashRegister $cash_register)
    {
        $this->cash_register = $cash_register;
    }

    public function getEffectiveTotalProperty()
    {
        return $this->cash_register->sales()->where('payment_method', 1)->sum('total');
    }

    public function getCardTotalProperty()
    {
        return $this->cash_register->sales()->where('payment_method', 2)->sum('total');
    }

    public function getTotalProperty()
    {
        return $this->effective_total + $this->card_total;
    }
};
?>

<div class="container mt-3">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Caja registradora #{{ $this->cash_register->id }}</h3>
                <a href="{{ route('cash_registers.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12 mb-4">
            <table>
                <tbody>
                    <tr>
                        <td>Estado: </td>
                        <td>
                            @if($this->cash_register->status == 1)
                            <span class="badge bg-success">Abierta</span>
                            @elseif($this->cash_register->status == 2)
                            <span class="badge bg-secondary">Cerrada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>Usuario: </td>
                        <td>{{ $this->cash_register->user->name }}</td>
                    </tr>
                    <tr>
                        <td>Fecha de apertura: </td>
                        <td>{{ $this->cash_register->opened_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-12">
            <h4>Ventas</h4>
            <hr>
        </div>
        <div class="col-12">
            <table class="table table-borderless table-light">
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
                    @foreach($cash_register->sales as $sale)
                    @foreach($sale->details as $detail)
                    <tr>
                        <td>{{ $detail->product->name }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>${{ number_format($detail->unit_price, 2) }}</td>
                        <td>${{ number_format($detail->discount, 2) }}</td>
                        <td>${{ number_format($detail->quantity * $detail->unit_price - $detail->discount, 2) }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            <hr>
        </div>
        <div class="col-12 mb-4">
            <table>
                <tbody>
                    <tr>
                        <td>Monto inicial</td>
                        <td>${{ number_format($this->cash_register->initial_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Ventas en efectivo</td>
                        <td>${{ number_format($this->effective_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Ventas con tarjeta</td>
                        <td>${{ number_format($this->card_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Total vendido</td>
                        <td>${{ number_format($this->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Efectivo esperado</td>
                        <td>${{ number_format($this->effective_total + $this->cash_register->initial_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="col-12">

        </div>
    </div>
</div>