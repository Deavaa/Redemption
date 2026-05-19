@if ($paginator->hasPages())
<nav class="compact-pagination" role="navigation" aria-label="Pagination Navigation">
    <ul class="cp-list">
        @if ($paginator->onFirstPage())
            <li class="cp-item cp-disabled"><span class="cp-link">&laquo;</span></li>
        @else
            <li class="cp-item"><a class="cp-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">&laquo;</a></li>
        @endif

        <li class="cp-item cp-active" aria-current="page">
            <span class="cp-link">{{ $paginator->currentPage() }}</span>
        </li>

        @if ($paginator->hasMorePages())
            <li class="cp-item"><a class="cp-link" href="{{ $paginator->nextPageUrl() }}" rel="next">&raquo;</a></li>
        @else
            <li class="cp-item cp-disabled"><span class="cp-link">&raquo;</span></li>
        @endif
    </ul>
    <span class="cp-info">
        {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </span>
</nav>
@endif
