@if ($breadcrumbs->isNotEmpty())
    <ol @class([
        'breadcrumb flex-nowrap',
        'border-top-0 border-end-0 border-bottom-0 border-secondary border w-100 h-100 m-0' => !request()->is('bo/*'),
        'm-0' => request()->is('bo/*'),
    ])>
        @foreach ($breadcrumbs as $breadcrumb)
            @if ($breadcrumb->url && !$loop->last)
                <li class="breadcrumb-item d-flex">
                    <a href="{{ $breadcrumb->url }}" @class([
                        'link-light link-offset-2 link-opacity-75 link-opacity-50-hover link-underline-opacity-75 link-underline-opacity-50-hover rounded-0 p-3 pe-2' => !request()->is('bo/*'),
                        'h2 text-primary ps-2 m-0 fw-bold' => request()->is('bo/*'),
                    ])>
                        {{ $breadcrumb->title }}
                    </a>
                </li>
            @else
                <li @class([
                    'breadcrumb-item position-relative d-flex align-items-center flex-grow-1',
                    'btn-breadcrumbs text-white p-0' => !request()->is('bo/*'),
                    'h2 text-body m-0 fw-bold' => request()->is('bo/*'),
                ])>
                    <p @class([
                        'm-0',
                        'breadcrumb-resize-btn position-absolute overflow-hidden m-0 py-3 ps-2 pe-3 h-100' => !request()->is('bo/*'),
                    ]) @if(!request()->is('bo/*')) role="button" @endif>
                        {{ $breadcrumb->title }}
                    </p>
                </li>
            @endif
        @endforeach
    </ol>
@endif
