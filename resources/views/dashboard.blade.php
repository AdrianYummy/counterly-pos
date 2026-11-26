<x-layouts.app title="Dashboard">
    <div class="page-actions"><div><p class="muted">Operational summary for {{ now(config('counterly.display_timezone'))->format('F j, Y') }}.</p></div><div class="button-row"><a class="button secondary" href="{{ route('products.create') }}"><x-icon name="plus"/>Add Product</a><a class="button primary" href="{{ route('sales.create') }}"><x-icon name="cart"/>Open Point of Sale</a></div></div>
    <section class="stat-grid" aria-label="Today's business summary">
        <article class="stat-card accent"><span>Sales today</span><strong>₱{{ number_format((float) $todaySales, 2) }}</strong><a href="{{ route('sales.index', ['date_from' => now(config('counterly.display_timezone'))->format('Y-m-d'), 'date_to' => now(config('counterly.display_timezone'))->format('Y-m-d')]) }}">View today’s transactions</a></article>
        <article class="stat-card"><span>Transactions today</span><strong>{{ number_format($todayTransactionCount) }}</strong><small>Completed sale records</small></article>
        <article class="stat-card"><span>Low-stock products</span><strong>{{ number_format($lowStockCount) }}</strong><a href="{{ route('products.index') }}">Review inventory</a></article>
        <article class="stat-card"><span>Active records</span><strong>{{ number_format($productCount) }}</strong><small>{{ number_format($customerCount) }} customers · {{ number_format($staffCount) }} staff</small></article>
    </section>
    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Latest activity</p><h2>Recent transactions</h2></div><a href="{{ route('sales.index') }}">View all</a></div>
            @if($recentSales->isEmpty())
                <div class="empty-state"><div class="empty-icon"><x-icon name="receipt"/></div><h3>No transactions yet</h3><p>Completed sales will appear here.</p><a class="button primary" href="{{ route('sales.create') }}">Open Point of Sale</a></div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Reference</th><th>Product</th><th>Customer</th><th>Cashier</th><th>Qty</th><th>Total</th></tr></thead><tbody>
                @foreach($recentSales as $sale)<tr><td><a href="{{ route('sales.show', $sale) }}">{{ $sale->publicReference() }}</a><small>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('g:i A') }}</small></td><td>{{ $sale->product->name }}</td><td>{{ $sale->customer?->full_name ?? 'Walk-in Customer' }}</td><td>{{ $sale->staff->full_name }}</td><td>{{ $sale->quantity }}</td><td><strong>₱{{ number_format((float) $sale->total_price, 2) }}</strong></td></tr>@endforeach
                </tbody></table></div>
            @endif
        </section>
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Inventory attention</p><h2>Low stock</h2></div><a href="{{ route('products.index') }}">All products</a></div>
            @if($lowStockProducts->isEmpty())<div class="empty-state compact"><div class="empty-icon"><x-icon name="box"/></div><h3>Stock looks healthy</h3><p>No products are at or below the alert threshold.</p></div>
            @else<ul class="stock-list">@foreach($lowStockProducts as $product)<li><div><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a><small>₱{{ number_format((float) $product->price, 2) }} each</small></div><span class="badge {{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">{{ $product->stock_quantity === 0 ? 'Out of stock' : $product->stock_quantity.' left' }}</span></li>@endforeach</ul>@endif
        </section>
    </div>
</x-layouts.app>
