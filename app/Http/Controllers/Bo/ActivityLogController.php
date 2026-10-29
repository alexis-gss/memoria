<?php

declare(strict_types=1);

namespace App\Http\Controllers\Bo;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('isConceptor');

        $query = ActivityLog::query()->with('user');

        /** @var \App\Models\User|null $userModel */
        $userModel = $request->route()->parameter('user');
        if ($userModel) {
            $query->where('user_id', $userModel->getKey());
        }

        /** @var string $search Search field */
        $search = $request->search;
        if ($search) {
            $this->searchQuery(
                $query,
                $search,
                null,
                'model_class',
            );
        }
        $searchFields = trans('validation.custom.model');

        /** Sort columns with a query */
        $this->sortQuery($query);

        /** Custom pagination */
        $activitylogModels = $this->paginate($query);

        return view('back.pages.activity_logs.index', compact(
            'activitylogModels',
            'search',
            'searchFields',
            'userModel'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function show(ActivityLog $activity_log): View
    {
        /** Authorize action */
        $this->authorize('isConceptor');

        $targetModelClass = $activity_log->model_class;
        $targetModel      = $activity_log->model_class::where(
            (new $targetModelClass())->getRouteKeyName(),
            $activity_log->model_id
        )->first();

        return view('back.pages.activity_logs.show', [
            'activitylogModel' => $activity_log,
            'targetModel'      => $targetModel,
        ]);
    }
}
