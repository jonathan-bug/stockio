<?php

use App\Models\CashRegister;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

new class extends Component
{
    public $initial_amount = 0;

    public function save()
    {
        $this->validate([
            'initial_amount' => ['required', 'numeric', 'decimal:0,2', 'min:0']
        ]);

        if (CashRegister::where('user_id', Auth::id())->where('status', 1)->exists()) {
            $this->dispatch('alert', message: 'You can only have one open cash register', type: 'warning');
            return;
        }

        CashRegister::create([
            'initial_amount' => $this->initial_amount,
            'opened_at' => now(),
            'status' => 1,
            'user_id' => Auth::id()
        ]);

        $this->dispatch('alert', message: 'Cash register opened successfully');
        $this->reset('initial_amount');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Abrir caja registradora</h3>
                <a href="{{ route('cash_registers.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="" class="form-label">Monto inicial</label>
                            <input type="number" step="0.01" class="form-control" wire:model="initial_amount">
                            @error('initial_amount')
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