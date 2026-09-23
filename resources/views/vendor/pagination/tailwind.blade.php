@if ($paginator->hasPages())
    <nav aria-label="Halaman tabel" class="flex flex-wrap items-center justify-between gap-3 text-sm">
        <p class="text-slate-500">Halaman {{ $paginator->currentPage() }} dari {{ $paginator->lastPage() }} ·
            {{ $paginator->total() }} data</p>
        <div class="flex gap-2">
            @if ($paginator->onFirstPage())
            <span class="btn-secondary opacity-50" aria-disabled="true">Sebelumnya</span>@else<a class="btn-secondary"
                    href="{{ $paginator->previousPageUrl() }}" rel="prev">Sebelumnya</a>
            @endif
            @if ($paginator->hasMorePages())
            <a class="btn-secondary" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya</a>@else<span
                    class="btn-secondary opacity-50" aria-disabled="true">Berikutnya</span>
            @endif
        </div>
    </nav>
@endif
