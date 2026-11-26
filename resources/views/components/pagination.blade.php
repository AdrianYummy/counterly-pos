@props(['paginator', 'label' => 'records'])

@if($paginator->hasPages())
    @php
        $startPage = max(1, $paginator->currentPage() - 2);
        $endPage = min($paginator->lastPage(), $paginator->currentPage() + 2);
    @endphp
    <nav class="record-pagination" aria-label="{{ ucfirst($label) }} pagination">
        <p>Showing <strong>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> {{ $label }}</p>
        <div class="page-buttons">
            @if($paginator->onFirstPage())
                <span class="page-button disabled" aria-disabled="true">Previous</span>
            @else
                <a class="page-button" href="{{ $paginator->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            @for($page = $startPage; $page <= $endPage; $page++)
                @if($page === $paginator->currentPage())
                    <span class="page-button active" aria-current="page">{{ $page }}</span>
                @else
                    <a class="page-button" href="{{ $paginator->url($page) }}" aria-label="Go to page {{ $page }}">{{ $page }}</a>
                @endif
            @endfor

            @if($paginator->hasMorePages())
                <a class="page-button" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="page-button disabled" aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif
