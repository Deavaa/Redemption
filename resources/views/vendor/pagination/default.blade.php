@if ($paginator->hasPages())
<nav class="compact-pagination" role="navigation" aria-label="Pagination Navigation">
    <ul class="cp-list">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="cp-item cp-disabled" aria-disabled="true">
                <span class="cp-link">&laquo;</span>
            </li>
        @else
            <li class="cp-item">
                <a class="cp-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&laquo;</a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="cp-item cp-disabled" aria-disabled="true">
                    <span class="cp-link cp-dots">{{ $element }}</span>
                </li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="cp-item cp-active" aria-current="page">
                            <span class="cp-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="cp-item">
                            <a class="cp-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="cp-item">
                <a class="cp-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&raquo;</a>
            </li>
        @else
            <li class="cp-item cp-disabled" aria-disabled="true">
                <span class="cp-link">&raquo;</span>
            </li>
        @endif
    </ul>

    {{-- Page Info --}}
    <span class="cp-info">
        {{ $paginator->firstItem() }}-{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </span>
</nav>
@endif
