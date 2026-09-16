<?php

use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use Illuminate\Support\Js;
use Livewire\Component;

new class extends Component
{
    public $todayRevenue = 0;
    public $todaySales = 0;
    public $todayEffective = 0;
    public $products = 0;

    public function mount()
    {
        $this->todayRevenue = Sale::whereDate('created_at', now())->sum('total');
        $this->todaySales = Sale::whereDate('created_at', now())->count();
        $this->todayEffective = Sale::whereDate('created_at', now())->where('payment_method', 1)->sum('total');
        $this->products = Product::count();
    }

    public function getWeekTotalsProperty()
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $sales = Sale::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        return collect(range(0, 6))->map(function ($day) use ($start, $sales) {
            $date = $start->copy()->addDays($day)->format('Y-m-d');

            return [
                'date' => $date,
                'day' => $start->copy()->addDays($day)->translatedFormat('D'),
                'total' => $sales->get($date)?->total ?? 0
            ];
        });
    }

    public function getPaymentTotalsProperty()
    {
        return [
            'effective' => Sale::whereDate('created_at', now())->where('payment_method', 1)->sum('total'),
            'card' => Sale::whereDate('created_at', now())->where('payment_method', 2)->sum('total')
        ];
    }

    public function getTopProductsProperty()
    {
        return SaleDetail::with('product')
            ->selectRaw('product_id, SUM(quantity) as quantity')
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity
                ];
            });
    }

    public function render()
    {
        $lowProducts = Product::with('stock')
            ->whereHas('stock', function ($query) {
                $query->whereColumn('quantity', '<=', 'products.minimum_stock');
            })
            ->orderBy('name')
            ->limit(5)
            ->get();

        return $this->view([
            'lowProducts' => $lowProducts
        ]);
    }
};
?>

<div class="container mt-3">
    <div class="row g-3">
        <div class="col-12">
            <div class="d-flex align-items-center">
                <h3>Dashboard</h3>
            </div>
            <hr>
        </div>
        <div class="col-12">
            <div class="row g-3">
                <div class="col-3">
                    <div class="card p-3">
                        <div class="d-flex gap-3">
                            <div>
                                <div class="rounded-circle bg-success d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                    <i class="fa fa-sack-dollar fa-2xl text-light"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="text-success fw-bold">${{ $this->todayRevenue }}</h2>
                                </div>
                                <div>Ingresos de hoy</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card p-3">
                        <div class="d-flex gap-3">
                            <div>
                                <div class="rounded-circle bg-danger d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                    <i class="fa fa-list-check fa-2xl text-light"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="text-danger fw-bold">{{ $this->todaySales }}</h2>
                                </div>
                                <div>Ventas de hoy</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card p-3">
                        <div class="d-flex gap-3">
                            <div>
                                <div class="rounded-circle bg-primary d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                    <i class="fa fa-coins fa-2xl text-light"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="text-primary fw-bold">${{ $this->todayEffective }}</h2>
                                </div>
                                <div>Efectivo de hoy</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card p-3">
                        <div class="d-flex gap-3">
                            <div>
                                <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center" style="width: 80px; height: 80px;">
                                    <i class="fa fa-cubes fa-2xl text-light"></i>
                                </div>
                            </div>
                            <div class="d-flex flex-column justify-content-between">
                                <div>
                                    <h2 class="text-secondary fw-bold">{{ $this->products }}</h2>
                                </div>
                                <div>Productos</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <h5>Ventas de la semana</h5>
                    <div id="weekTotalsContainer" class="card" data-week-totals='@json($this->week_totals)' style="height: 300px;">
                        <canvas id="weekTotals"></canvas>
                    </div>
                </div>
                <div class="col-6">
                    <h5>Ventas por método de pago</h5>
                    <div id="paymentTotalsContainer" class="card" data-payment-totals='@json($this->payment_totals)' style="height: 300px;">
                        <canvas id="paymentTotals"></canvas>
                    </div>
                </div>
                <div class="col-12">
                    <h5>Productos más vendidos</h5>
                    <div id="topProductsContainer" class="card" data-top-products='@json($this->top_products)' style="height: 300px;">
                        <canvas id="topProducts"></canvas>
                    </div>
                </div>
                <div class="col-12">
                    <h5>Productos con stock bajo</h5>
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Stock mínimo</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowProducts as $lowProduct)
                            <tr>
                                <td>{{ $lowProduct->name }}</td>
                                <td>{{ $lowProduct->minimum_stock }}</td>
                                <td>{{ $lowProduct->stock->quantity }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const weekTotalsContainer = document.getElementById('weekTotalsContainer')
    const paymentTotalsContainer = document.getElementById('paymentTotalsContainer')
    const topProductsContainer = document.getElementById('topProductsContainer')

    const weekTotals = JSON.parse(weekTotalsContainer.dataset.weekTotals)
    const paymentTotals = JSON.parse(paymentTotalsContainer.dataset.paymentTotals)
    const topProducts = JSON.parse(topProductsContainer.dataset.topProducts)

    new Chart(document.getElementById('weekTotals'), {
        type: 'line',
        data: {
            labels: weekTotals.map(item => item.day),
            datasets: [{
                label: 'Ventas',
                data: weekTotals.map(item => item.total),
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })

    new Chart(document.getElementById('paymentTotals'), {
        type: 'doughnut',
        data: {
            labels: ['Efectivo', 'Tarjeta'],
            datasets: [{
                data: [
                    paymentTotals.effective,
                    paymentTotals.card,
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })

    new Chart(document.getElementById('topProducts'), {
        type: 'bar',
        data: {
            labels: topProducts.map(item => item.name),
            datasets: [{
                label: 'Cantidad vendida',
                data: topProducts.map(item => item.quantity)
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    })
</script>