<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\StaticPages\UpdateStaticPageRequest;
use App\Models\StaticPage;
use App\Traits\Controllers\ChangesModelOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaticPageController extends Controller
{
    use ChangesModelOrder;

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(StaticPage::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = StaticPage::query();

        /** @var string $search Search field */
        $search = $request->search;
        if ($search) {
            $this->searchQuery(
                $query,
                $search,
                null,
                'seo_title',
                'seo_description',
                'title',
            );
        }
        $searchFields = \implode(', ', [
            trans('validation.custom.seo_title'),
            trans('validation.custom.seo_description'),
            trans('validation.attributes.title'),
        ]);

        /** Sort columns with a query */
        $this->sortQuery($query);

        /** Custom pagination */
        $staticPageModels = $this->paginate($query);

        return view('back.pages.static_pages.index', compact('staticPageModels', 'search', 'searchFields'));
    }

    /**
     * Show the specified resource.
     */
    public function show(StaticPage $static_page): View
    {
        return view('back.pages.static_pages.show', ['staticPageModel' => $static_page]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StaticPage $static_page): View
    {
        return view('back.pages.static_pages.edit', ['staticPageModel' => $static_page]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStaticPageRequest $request, StaticPage $static_page): RedirectResponse
    {
        return DB::transaction(function () use ($request, $static_page) {
            $static_page->fill($request->validated());

            if ($static_page->saveOrFail()) {
                return to_route('bo.static_pages.edit', $static_page)
                    ->with('success', trans('crud.messages.has_been_updated', [
                        'model' => Str::ucfirst(trans_choice('models.static_page', 1)),
                    ]));
            }

            return to_route('bo.static_pages.edit', $static_page)
                ->with('error', trans('crud.messages.cannot_be_updated', [
                    'model' => Str::ucfirst(trans_choice('models.static_page', 1)),
                ]));
        });
    }
}
