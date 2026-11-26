<x-layouts.app title="Customers">
    <div class="page-actions">
        <form class="search-form" method="GET"><label class="sr-only" for="customer-search">Search customers</label><input id="customer-search" name="search" value="{{ $search }}" placeholder="Search name or email" list="customer-suggestions" autocomplete="off"><datalist id="customer-suggestions">@foreach($searchSuggestions as $suggestion)<option value="{{ $suggestion }}">@endforeach</datalist><button class="button secondary" type="submit">Search</button>@if($search)<a class="text-link" href="{{ route('customers.index') }}">Clear</a>@endif</form>
        <a class="button primary" href="{{ route('customers.create') }}"><x-icon name="plus"/>Add Customer</a>
    </div>
    <section class="panel"><div class="panel-head"><div><p class="eyebrow">Customer records</p><h2>Customers</h2></div><span class="result-count">{{ $customers->total() }} results</span></div>
        @if($customers->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="users"/></div><h2>{{ $search ? 'No customers match your search' : 'No customers yet' }}</h2><p>{{ $search ? 'Try a suggested name or email address.' : 'Customer records are optional for walk-in sales.' }}</p>@unless($search)<a class="button primary" href="{{ route('customers.create') }}">Add Customer</a>@endunless</div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Customer</th><th>Email</th><th>Phone</th><th>Customer since</th><th class="actions-cell">Actions</th></tr></thead><tbody>
            @foreach($customers as $customer)<tr><td><strong>{{ $customer->full_name }}</strong></td><td><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></td><td>{{ $customer->phone ?: '—' }}</td><td>{{ $customer->created_at->timezone(config('counterly.display_timezone'))->format('M j, Y') }}</td><td class="actions-cell"><a class="icon-action view" href="{{ route('customers.show', $customer) }}" aria-label="View {{ $customer->full_name }}" title="View"><x-icon name="eye"/></a><a class="icon-action edit" href="{{ route('customers.edit', $customer) }}" aria-label="Edit {{ $customer->full_name }}" title="Edit"><x-icon name="edit"/></a>@if(auth()->user()->isAdmin())<form method="POST" action="{{ route('customers.destroy', $customer) }}" data-confirm="Delete {{ $customer->full_name }}? This cannot be undone.">@csrf @method('DELETE')<button class="icon-action delete" type="submit" aria-label="Delete {{ $customer->full_name }}" title="Delete"><x-icon name="trash"/></button></form>@endif</td></tr>@endforeach
            </tbody></table></div><x-pagination :paginator="$customers" label="customers"/>
        @endif
    </section>
</x-layouts.app>
