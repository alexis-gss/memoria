<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bo\Users\StoreUserRequest;
use App\Http\Requests\Bo\Users\UpdateUserRequest;
use App\Models\User;
use App\Traits\Controllers\ChangesModelOrder;
use App\Traits\Controllers\UpdateModelPublished;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    use ChangesModelOrder;
    use UpdateModelPublished;

    /**
     * Create the controller instance.
     */
    public function __construct()
    {
        $this->authorizeResource(User::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = User::query();

        /** @var string $search Search field */
        $search = $request->search;
        if ($search) {
            $this->searchQuery(
                $query,
                $search,
                null,
                'first_name',
                'last_name',
                'email',
                'role',
            );
        }
        $searchFields = \implode(', ', [
            trans('validation.attributes.first_name'),
            trans('validation.attributes.last_name'),
            trans('validation.attributes.email'),
            trans('validation.attributes.role'),
        ]);

        /** Sort columns with a query */
        $this->sortQuery($query);

        /** Custom pagination */
        $userModels = $this->paginate($query);

        return view('back.pages.users.index', compact('userModels', 'search', 'searchFields'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function show(User $user): View
    {
        return view('back.pages.users.show', ['userModel' => $user]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(User $user): View
    {
        return view('back.pages.users.create', ['userModel' => $user]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        return DB::transaction(function () use ($request) {
            $user = (new User())->fill($request->validated());

            if ($user->saveOrFail()) {
                return to_route('bo.users.edit', $user)
                    ->with('success', trans('crud.messages.has_been_created', [
                        'model' => Str::of(trans('models.user'))->ucfirst()->value(),
                    ]));
            }

            return back()
                ->with('error', trans('crud.messages.cannot_be_created', [
                    'model' => Str::of(trans('models.user'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return View|RedirectResponse
     */
    public function edit(User $user)
    {
        return view('back.pages.users.edit', ['userModel' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        return DB::transaction(function () use ($request, $user) {
            $user->fill($request->validated());

            if ($user->saveOrFail()) {
                return to_route('bo.users.edit', $user)
                    ->with('success', trans('crud.messages.has_been_updated', [
                        'model' => Str::of(trans('models.user'))->ucfirst()->value(),
                    ]));
            }

            return to_route('bo.users.edit', $user)
                ->with('error', trans('crud.messages.cannot_be_updated', [
                    'model' => Str::of(trans('models.user'))->ucfirst()->value(),
                ]));
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        /** @var array<string,string> */
        $previousQueries = [];
        \parse_str(\parse_url(url()->previous(), \PHP_URL_QUERY), $previousQueries);
        if ($user->deleteOrFail()) {
            return to_route('bo.users.index', $previousQueries)
                ->with('success', trans('crud.messages.has_been_deleted', [
                    'model' => Str::of(trans('models.user'))->ucfirst()->value(),
                ]));
        }

        return back()
            ->with('error', trans('crud.messages.cannot_be_deleted', [
                'model' => Str::of(trans('models.user'))->ucfirst()->value(),
            ]));
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate(User $user): View
    {
        return $this->create($user->replicate());
    }
}
