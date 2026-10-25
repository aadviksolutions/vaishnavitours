@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; padding: 1rem 0.25rem; border-top: 1px solid var(--slate-200); font-size: 0.85rem; margin-top: 1rem;">
        <div style="color: var(--slate-600); font-weight: 500;">
            Showing <span style="font-weight: 700; color: var(--dark-950);">{{ $paginator->firstItem() ?? 0 }}</span> to <span style="font-weight: 700; color: var(--dark-950);">{{ $paginator->lastItem() ?? 0 }}</span> of <span style="font-weight: 700; color: var(--dark-950);">{{ $paginator->total() }}</span> results
        </div>

        <div style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--slate-200); background: #f8fafc; color: var(--slate-400); cursor: not-allowed; font-weight: 600; font-size: 0.825rem;">
                    ← Prev
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--slate-300); background: #fff; color: var(--dark-900); text-decoration: none; font-weight: 600; font-size: 0.825rem; transition: all 0.15s;">
                    ← Prev
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span style="padding: 6px 8px; color: var(--slate-400); font-weight: 700;">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 8px; border-radius: 6px; background: var(--dark-900); color: #fff; font-weight: 800; font-size: 0.85rem;">
                                {{ $page }}
                            </span>
                        @else
                            <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 8px; border-radius: 6px; border: 1px solid var(--slate-200); background: #fff; color: var(--dark-900); text-decoration: none; font-weight: 600; font-size: 0.85rem;">
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--slate-300); background: #fff; color: var(--dark-900); text-decoration: none; font-weight: 600; font-size: 0.825rem; transition: all 0.15s;">
                    Next →
                </a>
            @else
                <span style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 6px; border: 1px solid var(--slate-200); background: #f8fafc; color: var(--slate-400); cursor: not-allowed; font-weight: 600; font-size: 0.825rem;">
                    Next →
                </span>
            @endif
        </div>
    </nav>
@endif
