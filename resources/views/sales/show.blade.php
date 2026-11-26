<x-layouts.app :title="'Transaction '.$sale->transactionReference()">
    <div class="page-actions no-print">
        <a class="text-link icon-link" href="{{ route('sales.index') }}"><x-icon name="arrow-left"/>Back to transactions</a>
        <div class="button-row">
            @if($sale->customer)
                <form method="POST" action="{{ route('sales.email-receipt', $sale) }}">
                    @csrf
                    <button class="button secondary" type="submit"><x-icon name="mail"/>Email Receipt</button>
                </form>
            @endif
            <button class="button secondary" type="button" data-print><x-icon name="printer"/>Print Receipt</button>
            <a class="button primary" href="{{ route('sales.create') }}"><x-icon name="plus"/>New Sale</a>
        </div>
    </div>
    <section class="receipt panel" id="print-receipt">
        <div class="receipt-head">
            <div><div class="brand receipt-brand"><img src="{{ asset('images/cafeflow-logo.png') }}" alt=""><strong>CaféFlow</strong></div><p>Transaction receipt</p></div>
            <div><span>Reference</span><strong>{{ $sale->transactionReference() }}</strong></div>
        </div>
        <div class="receipt-status"><span class="badge success">Completed</span><span>{{ $sale->created_at->timezone(config('counterly.display_timezone'))->format('F j, Y · g:i A') }} PHT</span></div>
        <div class="receipt-items">
            <div class="receipt-row receipt-labels"><span>Item</span><span>Qty</span><span>Unit price</span><span>Amount</span></div>
            @foreach($items as $item)
                <div class="receipt-row">
                    <strong>{{ $item->product->name }}</strong>
                    <span>{{ number_format($item->quantity) }}</span>
                    <span>₱{{ number_format((float) $item->total_price / $item->quantity, 2) }}</span>
                    <strong>₱{{ number_format((float) $item->total_price, 2) }}</strong>
                </div>
            @endforeach
        </div>
        <div class="receipt-total"><span>Total paid</span><strong>₱{{ number_format($transactionTotal, 2) }}</strong></div>
        <dl>
            <div><dt>Customer</dt><dd>{{ $sale->customer?->full_name ?? 'Walk-in Customer' }}</dd></div>
            <div><dt>Recorded by</dt><dd>{{ $sale->staff->full_name }}</dd></div>
            @if($sale->customer)<div><dt>Receipt email</dt><dd>{{ $sale->customer->email }}</dd></div>@endif
        </dl>
        <p class="receipt-note">Thank you for your purchase. This receipt preserves every recorded item price even if product pricing changes later.</p>
    </section>
</x-layouts.app>
