<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public $search = '';
    public $category = '';
    public $is_active = '';

    public Collection $categories;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedIsActive()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->categories = Category::all();
    }

    public function render()
    {
        $products = Product::with('category')
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('barcode', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('sales_price', 'like', '%' . $this->search . '%')
                        ->orWhere('minimum_stock', 'like', '%' . $this->search . '%');
                });
            })->when($this->category, function ($query) {
                $query->where('category_id', $this->category);
            })->when($this->is_active !== '', function ($query) {
                $query->where('is_active', (int) $this->is_active);
            })->paginate(10);

        return $this->view([
            'products' => $products
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3>Gestión de productos</h3>
                <a href="{{ route('products.create') }}" class="btn btn-primary" wire:navigate>Agregar</a>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" placeholder="Buscar por código de barras, nombre, precio de venta o stock mínimo" wire:model.live="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="category">
                        <option value="">Todas las categorias</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="is_active">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Código de barras</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio de venta</th>
                        <th>Stock mínimo</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->barcode }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->sales_price }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if($product->is_active)
                            <span class="badge bg-success">Activo</span>
                            @else
                            <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex justify-content-end gap-2">

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>