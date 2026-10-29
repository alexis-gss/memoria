<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Folders\StoreFolderRequest;
use App\Http\Requests\Bo\Folders\UpdateFolderRequest;
use App\Models\Folder;
use App\Traits\Controllers\ChangesModelOrder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    use ChangesModelOrder;

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(Folder::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Folder::query();

        /** @var string $search Search field */
        $search = $request->search;
        if ($search) {
            $this->searchQuery(
                $query,
                $search,
                null,
                'name',
                'color',
            );
        }
        $searchFields = \implode(', ', [
            trans('validation.attributes.name'),
            trans('validation.custom.color'),
        ]);

        /** Sort columns with a query */
        $this->sortQuery($query);

        /** Custom pagination */
        $folderModels = $this->paginate($query);

        return view('back.pages.folders.index', compact('folderModels', 'search', 'searchFields'));
    }

    /**
     * Show the specified resource.
     */
    public function show(Folder $folder): View
    {
        return view('back.pages.folders.show', ['folderModel' => $folder]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Folder $folder): View
    {
        return view('back.pages.folders.create', ['folderModel' => $folder]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFolderRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $folder = (new Folder())->fill($request->validated());

            if ($folder->saveOrFail()) {
                return to_route('bo.folders.edit', $folder)
                    ->with('success', trans('crud.messages.has_been_created', [
                        'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                    ]));
            }

            return back()
                ->with('error', trans('crud.messages.cannot_be_created', [
                    'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder): View
    {
        return view('back.pages.folders.edit', ['folderModel' => $folder]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFolderRequest $request, Folder $folder): RedirectResponse
    {
        return DB::transaction(function () use ($request, $folder) {
            $folder->fill($request->validated());

            if ($folder->saveOrFail()) {
                return to_route('bo.folders.edit', $folder)
                    ->with('success', trans('crud.messages.has_been_updated', [
                        'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                    ]));
            }

            return to_route('bo.folders.edit', $folder)
                ->with('error', trans('crud.messages.cannot_be_updated', [
                    'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Folder $folder): RedirectResponse
    {
        if ($folder->games->isEmpty()) {
            /** @var array<string,string> */
            $previousQueries = [];
            \parse_str(\parse_url(url()->previous(), \PHP_URL_QUERY), $previousQueries);
            if ($folder->deleteOrFail()) {
                return to_route('bo.folders.index', $previousQueries)
                    ->with('success', trans('crud.messages.has_been_deleted', [
                        'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                    ]));
            }

            return back()
                ->with('error', trans('crud.messages.cannot_be_deleted', [
                    'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
                ]));
        }

        return back()
            ->with('error', trans('crud.messages.cannot_be_deleted_with_children', [
                'model' => Str::of(trans('models.folder'))->ucfirst()->value(),
            ]));
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(Folder $folder): View
    {
        return $this->create($folder->replicate());
    }

    /**
     * Change folder published.
     */
    public function changePublished(Folder $folder): RedirectResponse
    {
        if ($folder->mandatory) {
            if ($folder->getTranslation('name', config('app.fallback_locale'))) {
                $folder->update(['published' => ! $folder->getOriginal('published')]);

                return back()->with('success', trans('crud.messages.publish_status_saved'));
            }

            return back()->with('error', trans('crud.messages.translation_default_required', [
                'fallbackLocale' => config('app.fallback_locale'),
            ]));
        }
        $folder->update(['published' => ! $folder->getOriginal('published')]);

        return back()->with('success', trans('crud.messages.publish_status_saved'));
    }
}
