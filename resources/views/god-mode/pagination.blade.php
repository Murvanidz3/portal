{{-- კომპაქტური გვერდების ნავიგაცია (უსაჭირო დიდი SVG ისრების გარეშე) --}}
@if ($paginator->hasPages())
    <nav class="god-pagination" role="navigation" aria-label="Pagination">
        <div class="god-pagination-inner">
            @if ($paginator->onFirstPage())
                <span class="god-pagination-link is-disabled">წინა</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="god-pagination-link" rel="prev">წინა</a>
            @endif

            <span class="god-pagination-meta">
                გვერდი {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                <span class="god-pagination-total">(სულ {{ $paginator->total() }})</span>
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="god-pagination-link" rel="next">შემდეგი</a>
            @else
                <span class="god-pagination-link is-disabled">შემდეგი</span>
            @endif
        </div>
    </nav>
@endif
