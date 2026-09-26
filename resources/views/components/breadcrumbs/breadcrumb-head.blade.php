@unless (request()->routeIs('errors.*'))
    @hasSection('breadcrumb')
        @if (Breadcrumbs::exists(app()->view->getSections()['breadcrumb']))
            {{ call_user_func_array(
                'Breadcrumbs::view',
                array_merge(['breadcrumbs::json-ld', app()->view->getSections()['breadcrumb']], Arr::wrap($brParam)),
            ) }}
        @endif
    @elseif(request()->route() != null)
        @if (Breadcrumbs::exists(request()->route()->getName()))
            {{ Breadcrumbs::view('breadcrumbs::json-ld', request()->route()->getName()) }}
        @endif
    @endif
@endunless
