<x-layouts.app title="User Accounts">
    <div class="page-actions">
        <form class="search-form" method="GET">
            <label class="sr-only" for="staff-search">Search staff</label>
            <input id="staff-search" name="search" value="{{ $search }}" placeholder="Search name, username, or email" list="staff-suggestions" autocomplete="off">
            <datalist id="staff-suggestions">
                @foreach($searchSuggestions as $suggestion)<option value="{{ $suggestion }}">@endforeach
            </datalist>
            <button class="button secondary" type="submit">Search</button>
            @if($search)<a class="text-link" href="{{ route('staff.index') }}">Clear</a>@endif
        </form>
        <a class="button primary" href="{{ route('staff.create') }}"><x-icon name="plus"/>Add Account</a>
    </div>
    <section class="panel">
        <div class="panel-head"><div><p class="eyebrow">Access management</p><h2>Staff accounts</h2></div><span class="result-count">{{ $staffMembers->total() }} total</span></div>
        @if($staffMembers->isEmpty())
            <div class="empty-state"><div class="empty-icon"><x-icon name="staff"/></div><h2>No matching accounts</h2><p>Try a suggested name, username, or email.</p></div>
        @else
            <div class="table-wrap"><table><thead><tr><th>Staff member</th><th>Sign-in details</th><th>Role</th><th>Status</th><th>Created</th><th class="actions-cell">Actions</th></tr></thead><tbody>
            @foreach($staffMembers as $staff)
                <tr>
                    <td><div class="media-cell"><img class="round" src="{{ $staff->avatar ? asset('storage/'.$staff->avatar) : asset('images/avatar-placeholder.svg') }}" alt=""><strong>{{ $staff->full_name }} @if(auth()->user()->is($staff))<small class="you-label">Current account</small>@endif</strong></div></td>
                    <td>{{ '@'.$staff->username }}<small>{{ $staff->email }}</small></td>
                    <td><span class="badge {{ $staff->isAdmin() ? 'success' : 'warning' }}">{{ $staff->isAdmin() ? 'Administrator' : 'Cashier' }}</span></td>
                    <td><span class="badge success">Active</span></td>
                    <td>{{ $staff->created_at->timezone(config('counterly.display_timezone'))->format('M j, Y') }}</td>
                    <td class="actions-cell">
                        <a class="icon-action view" href="{{ route('staff.show', $staff) }}" aria-label="View {{ $staff->full_name }}" title="View"><x-icon name="eye"/></a>
                        <a class="icon-action edit" href="{{ route('staff.edit', $staff) }}" aria-label="Edit {{ $staff->full_name }}" title="Edit"><x-icon name="edit"/></a>
                        <form method="POST" action="{{ route('staff.destroy', $staff) }}" data-confirm="Delete the account for {{ $staff->full_name }}? This cannot be undone.">@csrf @method('DELETE')<button class="icon-action delete" type="submit" aria-label="Delete {{ $staff->full_name }}" title="Delete"><x-icon name="trash"/></button></form>
                    </td>
                </tr>
            @endforeach
            </tbody></table></div>
            <x-pagination :paginator="$staffMembers" label="staff accounts"/>
        @endif
    </section>
</x-layouts.app>
