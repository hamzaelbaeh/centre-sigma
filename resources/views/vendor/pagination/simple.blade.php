@if ($paginator->hasPages())
<nav style="margin-top:14px;display:flex;gap:8px;align-items:center">
@if ($paginator->onFirstPage())
<span class="btn btn-ghost" style="opacity:.5">Précédent</span>
@else
<a class="btn btn-ghost" href="{{ $paginator->previousPageUrl() }}">Précédent</a>
@endif
<span class="muted">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
@if ($paginator->hasMorePages())
<a class="btn btn-ghost" href="{{ $paginator->nextPageUrl() }}">Suivant</a>
@else
<span class="btn btn-ghost" style="opacity:.5">Suivant</span>
@endif
</nav>
@endif
