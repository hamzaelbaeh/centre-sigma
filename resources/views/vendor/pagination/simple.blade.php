@if ($paginator->hasPages())
<nav style="margin-top:14px;display:flex;gap:8px;align-items:center">
@if ($paginator->onFirstPage())
<span class="btn btn-ghost" style="opacity:.5">{{ __('Précédent') }}</span>
@else
<a class="btn btn-ghost" href="{{ $paginator->previousPageUrl() }}">{{ __('Précédent') }}</a>
@endif
<span class="muted">Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
@if ($paginator->hasMorePages())
<a class="btn btn-ghost" href="{{ $paginator->nextPageUrl() }}">{{ __('Suivant') }}</a>
@else
<span class="btn btn-ghost" style="opacity:.5">{{ __('Suivant') }}</span>
@endif
</nav>
@endif
