<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $stock_status = '';
    public $category = '';
    public Collection $categories;

    public function mount()
    {
        $this->categories = Category::where('is_active', true)->get();
    }

    public function render()
    {
        $products = Product::with(['category', 'stock'])
            ->when($this->search, function ($query) {
                $query->where(function ($query) {
                    $query->where('barcode', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('minimum_stock', 'like', '%' . $this->search . '%')
                        ->orWhereHas('stock', function ($sub_query) {
                            $sub_query->where('quantity', 'like', '%' . $this->search . '%');
                        });
                });
            })->when($this->category !== '', function ($query) {
                $query->where('category_id', $this->category);
            })->when($this->stock_status !== '', function ($query) {
                if ((int) $this->stock_status === 1) {
                    $query->where(function ($query) {
                        $query->whereDoesntHave('stock')
                            ->orWhereHas('stock', function ($sub_query) {
                                $sub_query->where('quantity', 0);
                            });
                    });
                } else if ((int) $this->stock_status === 2) {
                    $query->whereHas('stock', function ($sub_query) {
                        $sub_query->where('quantity', '>', 0)
                            ->whereColumn('product_stocks.quantity', '<', 'products.minimum_stock');
                    });
                } else if ((int) $this->stock_status === 3) {
                    $query->whereHas('stock', function ($sub_query) {
                        $sub_query->whereColumn('product_stocks.quantity', '>=', 'products.minimum_stock');
                    });
                }
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
                <h3>Existencias</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-2">
                <div class="col">
                    <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por código de barras, producto, stock actual y stock mínimo">
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
                    <select class="form-select" wire:model.live="stock_status">
                        <option value="">Todos los estados</option>
                        <option value="1">Sin stock</option>
                        <option value="2">Stock bajo</option>
                        <option value="3">Stock normal</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Código de barras</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock actual</th>
                        <th>Stock mínimo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->barcode ?: '-' }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->category->name }}</td>
                        <td>{{ $product->stock->quantity ?? 0 }}</td>
                        <td>{{ $product->minimum_stock }}</td>
                        <td>
                            @if(($product->stock->quantity ?? 0) == 0)
                            <span class="badge bg-danger">Sin stock</span>
                            @elseif(($product->stock->quantity ?? 0) < $product->minimum_stock)
                                <span class="badge bg-warning">Stock bajo</span>
                                @else
                                <span class="badge bg-success">Stock normal</span>
                                @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-12">
            {{ $products->links() }}
        </div>
    </div>
</div>