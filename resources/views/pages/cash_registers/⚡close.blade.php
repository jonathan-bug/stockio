<?php

use App\Models\CashRegister;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public CashRegister $cash_register;
    public $closing_amount = 0;

    public function mount(CashRegister $cash_register)
    {
        abort_unless($cash_register->status == 1, 404);
        abort_unless($cash_register->user_id == Auth::id(), 403);

        $this->cash_register = $cash_register;
    }

    public function getEffectiveTotalProperty()
    {
        return $this->cash_register->sales()->where('payment_method', 1)->sum('total');
    }

    public function getExpectedEffectiveProperty()
    {
        return (float) $this->cash_register->initial_amount + $this->effective_total;
    }

    public function getDifferenceProperty()
    {
        return (float) $this->closing_amount - $this->expected_effective;
    }

    public function save()
    {
        $this->validate([
            'closing_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0']
        ]);

        $this->cash_register->update([
            'closing_amount' => $this->closing_amount,
            'closed_at' => now(),
            'status' => 2
        ]);

        session()->flash('success', 'Cash register closed successfully');

        return $this->redirectRoute('cash_registers.index', navigate: true);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Cerrar caja</h3>
                <a href="{{ route('cash_registers.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="form-label">Monto inicial</label>
                            <input type="text" class="form-control" value="${{ number_format($this->cash_register->initial_amount, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="form-label">Total de ventas en efectivo</label>
                            <input type="text" class="form-control" value="${{ number_format($this->effective_total, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="form-label">Monto esperado</label>
                            <input type="text" class="form-control" value="${{ number_format($this->expected_effective, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="" class="form-label">Diferencia</label>
                            <input type="text" class="form-control {{ $this->difference >= 0 ? 'text-success' : 'text-danger' }}" value="${{ number_format($this->difference, 2) }}" readonly>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="" class="form-label">Monto en caja</label>
                            <input type="number" class="form-control" step="0.01" wire:model.live="closing_amount">
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