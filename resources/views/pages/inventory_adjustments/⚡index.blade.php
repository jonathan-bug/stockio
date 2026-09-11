<?php

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::with('stock')->when($this->search, function ($query) {
            $query->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhereHas('stock', function ($sub_query) {
                        $sub_query->where('quantity', 'like', '%' . $this->search . '%');
                    });
            });
        })->where('is_active', true)->paginate(10);

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
                <h3>Gestión de ajustes</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <input type="text" class="form-control" wire:model.live="search" placeholder="Buscar por producto o stock">
        </div>
        <div class="col-12">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->stock->quantity ?? 0 }}</td>
                        <td>
                            <div class="d-flex justify-content-end">
                                <a href="" class="btn btn-secondary">
                                    <i class="fa fa-sliders"></i>
                                </a>
                            </div>
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