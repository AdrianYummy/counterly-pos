<x-layouts.app title="Dashboard">
    <div class="page-actions"><div><p class="muted">Operational summary for {{ now(config('counterly.display_timezone'))->format('F j, Y') }}.</p></div><div class="button-row"><a class="button secondary" href="{{ route('products.create') }}"><x-icon name="plus"/>Add Product</a><a class="button primary" href="{{ route('sales.create') }}"><x-icon name="cart"/>Open Point of Sale</a></div></div>
    <section class="stat-grid" aria-label="Today's business summary">
        <article class="stat-card tone-green"><span>Sales today</span><strong>₱{{ number_format((float) $todaySales, 2) }}</strong><a href="{{ route('sales.index', ['date_from' => now(config('counterly.display_timezone'))->format('Y-m-d'), 'date_to' => now(config('counterly.display_timezone'))->format('Y-m-d')]) }}">View today’s transactions</a></article>
        <article class="stat-card tone-gold"><span>Transactions today</span><strong>{{ number_format($todayTransactionCount) }}</strong><small>Completed checkouts</small></article>
        <article class="stat-card tone-coral"><span>Low-stock products</span><strong>{{ number_format($lowStockCount) }}</strong><a href="{{ route('products.index') }}">Review inventory</a></article>
        <article class="stat-card tone-blue"><span>Active records</span><strong>{{ number_format($productCount) }}</strong><small>{{ number_format($customerCount) }} customers · {{ number_format($staffCount) }} staff</small></article>
    </section>
    <section class="analytics-grid" aria-label="Sales analytics">
        <article class="panel analytics-card sales-trend-card">
            <div class="panel-head analytics-head">
                <div><p class="eyebrow">Seven-day performance</p><h2>Sales trend</h2><p>Daily revenue in Philippine pesos.</p></div>
                <div class="analytics-total"><span>7-day revenue</span><strong>₱{{ number_format((float) $sevenDayRevenue, 2) }}</strong><small>{{ number_format($sevenDayTransactions) }} {{ Illuminate\Support\Str::plural('transaction', $sevenDayTransactions) }}</small></div>
            </div>
            <div class="sales-chart" role="img" aria-label="Seven-day sales revenue bar chart">
                @foreach($salesTrend as $day)
                    @php
                        $barHeight = $day['revenue'] > 0 ? max(6, ($day['revenue'] / $salesTrendMax) * 100) : 0;
                    @endphp
                    <div class="sales-chart-column" tabindex="0" aria-label="{{ $day['label'] }}: ₱{{ number_format($day['revenue'], 2) }}, {{ $day['transactions'] }} transactions, {{ $day['units'] }} units">
                        <div class="chart-tooltip"><strong>₱{{ number_format($day['revenue'], 2) }}</strong><span>{{ $day['transactions'] }} {{ Illuminate\Support\Str::plural('checkout', $day['transactions']) }} · {{ $day['units'] }} units</span></div>
                        <div class="sales-bar-track"><span class="sales-bar" style="--bar-height: {{ number_format($barHeight, 2, '.', '') }}%"></span></div>
                        <div class="sales-chart-label"><strong>{{ $day['day'] }}</strong><span>{{ $day['label'] }}</span></div>
                    </div>
                @endforeach
            </div>
            <a class="analytics-link" href="{{ route('sales.index', ['date_from' => $salesTrend->first()['date'], 'date_to' => $salesTrend->last()['date']]) }}">View seven-day transactions →</a>
        </article>

        <article class="panel analytics-card top-products-card">
            <div class="panel-head analytics-head"><div><p class="eyebrow">Customer favorites</p><h2>Top products</h2><p>Ranked by units sold this week.</p></div></div>
            @if($topProducts->isEmpty())
                <div class="empty-state compact"><div class="empty-icon"><x-icon name="chart"/></div><h3>No sales data yet</h3><p>Top products will appear after checkout.</p></div>
            @else
                <ol class="product-chart">
                    @foreach($topProducts as $index => $product)
                        <li>
                            <span class="product-rank">{{ $index + 1 }}</span>
                            <div class="product-chart-main">
                                <div><a href="{{ route('sales.index', ['product_id' => $product['public_id']]) }}">{{ $product['name'] }}</a><strong>{{ number_format($product['units']) }} sold</strong></div>
                                <div class="product-bar-track"><span style="--bar-width: {{ number_format(($product['units'] / $topProductMax) * 100, 2, '.', '') }}%"></span></div>
                                <small>₱{{ number_format($product['revenue'], 2) }} revenue</small>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </article>
    </section>
    <div class="dashboard-grid">
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Latest activity</p><h2>Recent transactions</h2></div><a href="{{ route('sales.index') }}">View all</a></div>
            @if($recentSales->isEmpty())
                <div class="empty-state"><div class="empty-icon"><x-icon name="receipt"/></div><h3>No transactions yet</h3><p>Completed sales will appear here.</p><a class="button primary" href="{{ route('sales.create') }}">Open Point of Sale</a></div>
            @else
                <div class="table-wrap"><table><thead><tr><th>Reference</th><th>Product</th><th>Customer</th><th>Cashier</th><th>Qty</th><th>Total</th></tr></thead><tbody>
                @foreach($recentSales as $sale)<tr><td><a href="{{ route('sales.show', $sale) }}">{{ $sale->transactionReference() }}</a><small>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('g:i A') }}</small></td><td>{{ $sale->product->name }}</td><td>{{ $sale->customer?->full_name ?? 'Walk-in Customer' }}</td><td>{{ $sale->staff->full_name }}</td><td>{{ $sale->quantity }}</td><td><strong>₱{{ number_format((float) $sale->total_price, 2) }}</strong></td></tr>@endforeach
                </tbody></table></div>
            @endif
        </section>
        <section class="panel">
            <div class="panel-head"><div><p class="eyebrow">Inventory attention</p><h2>Low stock</h2></div><a href="{{ route('products.index') }}">All products</a></div>
            @if($lowStockProducts->isEmpty())<div class="empty-state compact"><div class="empty-icon"><x-icon name="box"/></div><h3>Stock looks healthy</h3><p>No products are at or below the alert threshold.</p></div>
            @else<ul class="stock-list">@foreach($lowStockProducts as $product)<li><div><a href="{{ route('products.show', $product) }}">{{ $product->name }}</a><small>₱{{ number_format((float) $product->price, 2) }} each</small></div><span class="badge {{ $product->stock_quantity === 0 ? 'danger' : 'warning' }}">{{ $product->stock_quantity === 0 ? 'Out of stock' : $product->stock_quantity.' left' }}</span></li>@endforeach</ul>@endif
        </section>
    </div>
    <section class="panel activity-panel" aria-labelledby="recent-edits-title">
        <div class="panel-head">
            <div><p class="eyebrow">Accountability</p><h2 id="recent-edits-title">Recent record activity</h2><p>See who created, updated, sold, or deleted products, stock, customers, and staff accounts.</p></div>
            <span class="result-count">{{ number_format($recentEdits->total()) }} recorded</span>
        </div>
        @if($recentEdits->isEmpty())
            <div class="empty-state compact"><div class="empty-icon"><x-icon name="history"/></div><h3>No record activity yet</h3><p>New product, stock, customer, and staff changes will appear here.</p></div>
        @else
            <ol class="activity-list">
                @foreach($recentEdits as $edit)
                    @php
                        $subjectLabel = match ($edit->subject_type) {
                            'product' => 'Product / stock',
                            'staff' => 'Staff account',
                            default => 'Customer',
                        };
                        $badgeClass = match ($edit->subject_type) {
                            'product' => 'info',
                            'staff' => 'warning',
                            default => 'success',
                        };
                    @endphp
                    <li>
                        <div class="activity-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($edit->actor_name, 0, 1)) }}</div>
                        <div class="activity-main">
                            <div class="activity-heading">
                                <p><strong>{{ $edit->actor_name }}</strong> {{ $edit->action }} <b>{{ $edit->subject_name }}</b> <span class="badge {{ $badgeClass }}">{{ $subjectLabel }}</span></p>
                                <time datetime="{{ $edit->created_at->toIso8601String() }}">{{ $edit->created_at->timezone(config('counterly.display_timezone'))->format('M j, Y · g:i A') }}</time>
                            </div>
                            <ul class="change-list">
                                @foreach($edit->changes as $field => $change)
                                    <li><span>{{ Illuminate\Support\Str::headline($field) }}</span><del>{{ $change['from'] }}</del><span aria-hidden="true">→</span><ins>{{ $change['to'] }}</ins></li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endforeach
            </ol>
            <x-pagination :paginator="$recentEdits" label="accountability records"/>
        @endif
    </section>
</x-layouts.app>
