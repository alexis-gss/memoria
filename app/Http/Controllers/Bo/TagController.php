<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Tags\StoreTagRequest;
use App\Http\Requests\Bo\Tags\UpdateTagRequest;
use App\Models\Tag;
use App\Traits\Controllers\ChangesModelOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TagController extends Controller
{
    use ChangesModelOrder;

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Tag::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Tag::query();

        /** @var string $search Search field */
        $search = $request->search;
        if ($search) {
            $this->searchQuery(
                $query,
                $search,
                null,
                'name',
            );
        }
        $searchFields = trans('validation.attributes.name');

        /** Sort columns with a query */
        $this->sortQuery($query);

        /** Custom pagination */
        $tagModels = $this->paginate($query);

        return view('back.pages.tags.index', compact('tagModels', 'search', 'searchFields'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Tag $tag): View
    {
        return view('back.pages.tags.show', ['tagModel' => $tag]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Tag $tag): View
    {
        return view('back.pages.tags.create', ['tagModel' => $tag]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTagRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $tag = (new Tag())->fill($request->validated());

            if ($tag->saveOrFail()) {
                return to_route('bo.tags.edit', $tag)
                    ->with('success', trans('crud.messages.has_been_created', [
                        'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
                    ]));
            }

            return back()
                ->with('error', trans('crud.messages.cannot_be_created', [
                    'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag): View
    {
        return view('back.pages.tags.edit', ['tagModel' => $tag]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        return DB::transaction(function () use ($request, $tag) {
            $tag->fill($request->validated());

            if ($tag->saveOrFail()) {
                return to_route('bo.tags.edit', $tag)
                    ->with('success', trans('crud.messages.has_been_updated', [
                        'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
                    ]));
            }

            return to_route('bo.tags.edit', $tag)
                ->with('error', trans('crud.messages.cannot_be_updated', [
                    'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        /** @var array<string,string> */
        $previousQueries = [];
        \parse_str(\parse_url(url()->previous(), \PHP_URL_QUERY), $previousQueries);
        if ($tag->deleteOrFail()) {
            return to_route('bo.tags.index', $previousQueries)
                ->with('success', trans('crud.messages.has_been_deleted', [
                    'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
                ]));
        }

        return back()
            ->with('error', trans('crud.messages.cannot_be_deleted', [
                'model' => Str::of(trans('models.tag'))->ucfirst()->value(),
            ]));
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Tag $tag): View
    {
        return $this->create($tag->replicate());
    }

    /**
     * Change tag published.
     */
    public function changePublished(Tag $tag): RedirectResponse
    {
        if ($tag->getTranslation('name', config('app.fallback_locale'))) {
            $tag->update(['published' => ! $tag->getOriginal('published')]);

            return back()->with('success', trans('crud.messages.publish_status_saved'));
        }

        return back()->with('error', trans('crud.messages.translation_default_required', [
            'fallbackLocale' => config('app.fallback_locale'),
        ]));
    }
}
