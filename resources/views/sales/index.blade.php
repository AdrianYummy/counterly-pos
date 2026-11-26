<x-layouts.app title="Transactions">
    <section class="panel filter-panel">
        <form method="GET" class="filter-grid">
            <label>Search<input name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Product, customer, or cashier" list="transaction-suggestions" autocomplete="off"></label>
            <datalist id="transaction-suggestions">@foreach($searchSuggestions as $suggestion)<option value="{{ $suggestion }}">@endforeach</datalist>
            <label>Product<select name="product_id"><option value="">All products</option>@foreach($products as $product)<option value="{{ $product->public_id }}" @selected((string)($filters['product_id'] ?? '') === (string)$product->public_id)>{{ $product->name }}</option>@endforeach</select></label>
            <label>Cashier<select name="staff_id"><option value="">All staff</option>@foreach($staffMembers as $staff)<option value="{{ $staff->public_id }}" @selected((string)($filters['staff_id'] ?? '') === (string)$staff->public_id)>{{ $staff->full_name }}</option>@endforeach</select></label>
            <label>From<input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
            <label>To<input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
            <div class="filter-actions"><button class="button secondary" type="submit">Apply Filters</button><a class="text-link" href="{{ route('sales.index') }}">Reset</a></div>
        </form>
    </section>
    <div class="page-actions"><p class="muted">Completed transactions are read-only and newest first.</p><a class="button primary" href="{{ route('sales.create') }}"><x-icon name="cart"/>Open Point of Sale</a></div>
    <section class="panel">
        <div class="panel-head"><div><p class="eyebrow">Sales record</p><h2>Transaction history</h2></div><span class="result-count">{{ $sales->total() }} results</span></div>
        @if($sales->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="receipt"/></div><h2>No transactions for this date range</h2><p>Change the filters or complete a new sale.</p><a class="button primary" href="{{ route('sales.create') }}">Open Point of Sale</a></div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Reference</th><th>Date</th><th>Product</th><th>Customer</th><th>Cashier</th><th>Qty</th><th>Total</th><th class="actions-cell">Actions</th></tr></thead><tbody>
            @foreach($sales as $sale)
                <tr><td><strong>{{ $sale->publicReference() }}</strong></td><td>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('M j, Y') }}<small>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('g:i A') }}</small></td><td>{{ $sale->product->name }}</td><td>{{ $sale->customer?->full_name ?? 'Walk-in Customer' }}</td><td>{{ $sale->staff->full_name }}</td><td>{{ number_format($sale->quantity) }}</td><td><strong>₱{{ number_format((float) $sale->total_price, 2) }}</strong></td><td class="actions-cell"><a class="icon-action view" href="{{ route('sales.show', $sale) }}" aria-label="View receipt {{ $sale->publicReference() }}" title="View receipt"><x-icon name="eye"/></a></td></tr>
            @endforeach
            </tbody></table></div>
            <x-pagination :paginator="$sales" label="transactions"/>
        @endif
    </section>
</x-layouts.app>
