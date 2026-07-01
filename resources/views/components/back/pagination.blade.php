{{-- GET ACTUAL PAGINATION --}}
@php $pagination = intval(session()->get('pagination.' . str(request()->route()->getName())->slug())); @endphp
@if ($paginator->items())
    <section class="pagination-custom d-flex justify-content-between align-items-center">
        {{-- SELECT ITEMS PER PAGE --}}
        <div class="dropup-center dropup d-flex justify-content-center align-items-center input-group w-fit">
            <span class="btn-md input-group-text" data-bs-tooltip="tooltip" data-bs-placement="top"
                title="{{ __('pagination.paginate_list') }}">
                <i class="fa-solid fa-list-ul"></i>
            </span>
            <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" type="button" aria-expanded="false">
                {{ $pagination }}
            </button>
            <ul class="dropdown-menu">
                @foreach (\App\Enums\Pagination\ItemsPerPaginationEnum::toArray() as $itemsPerPaginationEnum)
                    <li>
                        <a href="{{ request()->fullUrlWithQuery(['page' => 1, 'pagination' => $itemsPerPaginationEnum->value]) }}"
                            role="button" @class([
                                'dropdown-item user-select-none text-center',
                                'active' => $pagination === $itemsPerPaginationEnum->value,
                            ])>
                            {{ $itemsPerPaginationEnum->value }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        @if ($paginator->hasPages())
            {{-- PAGINATION --}}
            <div class="d-flex justify-content-end align-items-center">
                <ul class="pagination m-0">
                    {{-- PREVIOUS PAGE --}}
                    <li @class(['page-item', 'disabled' => $paginator->onFirstPage()])>
                        <a class="page-link d-flex align-items-center h-100" data-bs-tooltip="tooltip" data-bs-placement="top"
                            title="{{ __('pagination.previous') }}" aria-label="{{ __('pagination.previous') }}" rel="prev"
                            @if (!$paginator->onFirstPage()) href="{{ request()->fullUrlWithQuery(['page' => $paginator->currentPage() - 1]) }}"
                            @else aria-hidden="true" disabled @endif>
                            <i class="fa-solid fa-chevron-left fa-2xs mt-1"></i>
                        </a>
                    </li>
                    {{-- FIRST PAGE --}}
                    @if ($paginator->currentPage() > 3)
                        <li class="page-item d-none d-sm-block" data-bs-tooltip="tooltip" data-bs-placement="top"
                            title="{{ __('pagination.specific_page', ['id' => 1]) }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                        </li>
                    @endif
                    {{-- HIDE PAGES TOO FAR --}}
                    @if ($paginator->currentPage() > 4)
                        <li class="page-item d-none d-sm-block disabled"><span class="page-link">…</span></li>
                    @endif
                    {{-- SHOW ACTUAL PAGES + N-2/N+2 --}}
                    @foreach (range(1, $paginator->lastPage()) as $i)
                        @if ($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                            @if ($i == $paginator->currentPage())
                                <li class="page-item">
                                    <div class="dropup-center dropup">
                                        <button class="btn btn-primary dropdown-toggle rounded-0" data-bs-toggle="dropdown" type="button"
                                            aria-expanded="false">
                                            {{ $i }}
                                        </button>
                                        <ul class="dropdown-menu">
                                            @for ($i = 1; $i <= $paginator->lastPage(); $i++)
                                                <li>
                                                    <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" role="button"
                                                        @class([
                                                            'dropdown-item user-select-none text-center',
                                                            'active' => $paginator->currentPage() === $i,
                                                        ])>{{ $i }}</a>
                                                </li>
                                            @endfor
                                        </ul>
                                    </div>
                                </li>
                            @else
                                <li class="page-item d-none d-md-block" data-bs-tooltip="tooltip" data-bs-placement="top"
                                    title="{{ __('pagination.specific_page', ['id' => $i]) }}">
                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                </li>
                            @endif
                        @endif
                    @endforeach
                    {{-- HIDE PAGES TOO FAR --}}
                    @if ($paginator->currentPage() < $paginator->lastPage() - 3)
                        <li class="page-item d-none d-sm-block disabled"><span class="page-link">…</span></li>
                    @endif
                    {{-- LAST PAGE --}}
                    @if ($paginator->currentPage() < $paginator->lastPage() - 2)
                        <li class="page-item d-none d-sm-block" data-bs-tooltip="tooltip" data-bs-placement="top"
                            title="{{ __('pagination.specific_page', ['id' => $paginator->lastPage()]) }}">
                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginator->lastPage()]) }}">
                                {{ $paginator->lastPage() }}
                            </a>
                        </li>
                    @endif
                    {{-- NEXT PAGE --}}
                    <li @class(['page-item', 'disabled' => !$paginator->hasMorePages()])>
                        <a class="page-link d-flex align-items-center h-100" data-bs-tooltip="tooltip" data-bs-placement="top"
                            title="{{ __('pagination.next') }}" aria-label="{{ __('pagination.next') }}" rel="next"
                            @if ($paginator->hasMorePages()) href="{{ request()->fullUrlWithQuery(['page' => $paginator->currentPage() + 1]) }}"
                            @else aria-hidden="true" disabled @endif>
                            <i class="fa-solid fa-chevron-right fa-2xs mt-1"></i>
                        </a>
                    </li>
                </ul>
            </div>
        @endif
    </nav>
@endif
