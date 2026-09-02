<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public $barcode = '';
    public $name = '';
    public $category = 0;
    public $sales_price = 0;
    public $minimum_stock = 0;
    public $is_active = true;

    public Collection $categories;

    public function mount()
    {
        $this->categories = Category::all();
        $this->category = $this->categories->first()?->id ?? 0;
    }

    public function save()
    {
        $this->validate([
            'barcode' => ['nullable', 'string', 'max:255'],
            'name' => 'required',
            'category' => ['required', 'exists:categories,id'],
            'sales_price' => ['required', 'numeric', 'decimal:0,2', 'min:0'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean']
        ]);

        Product::create([
            'barcode' => $this->barcode,
            'name' => $this->name,
            'category_id' => $this->category,
            'sales_price' => $this->sales_price,
            'minimum_stock' => $this->minimum_stock,
            'is_active' => $this->is_active
        ]);

        $this->reset('barcode', 'name', 'category', 'sales_price', 'minimum_stock', 'is_active');
        $this->category = $this->categories->first()?->id ?? 0;

        $this->dispatch('alert', message: 'Product created successfully');
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Crear producto</h3>
                <a href="{{ route('products.index') }}" class="btn btn-secondary" wire:navigate>Volver</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <form wire:submit="save">
                <div class="row gy-3">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="barcode" class="form-label">Código de barras</label>
                            <input id="barcode" type="text" class="form-control" wire:model="barcode">
                            @error('barcode')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="name" class="form-label">Nombre</label>
                            <input id="name" type="text" class="form-control" wire:model="name">
                            @error('name')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="category" class="form-label">Categoría</label>
                            <select id="category" class="form-select" wire:model="category">
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="sales_price" class="form-label">Precio de venta</label>
                            <input id="sales_price" type="number" step="0.01" class="form-control" wire:model.decimal="sales_price">
                            @error('sales_price')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="minimum_stock" class="form-label">Stock mínimo</label>
                            <input id="minimum_stock" type="number" step="1" class="form-control" wire:model="minimum_stock">
                            @error('minimum_stock')
                            <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label for="is_active" class="form-label">Estado</label>
                            <select id="is_active" class="form-select" wire:model.boolean="is_active">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
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