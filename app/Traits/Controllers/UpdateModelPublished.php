<?php

declare(strict_types=1);

namespace App\Traits\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * This trait works with Route::bind in App service provider
 * to resolve the model and the DB row
 * It does not work with binder Service
 */
trait UpdateModelPublished
{
    /**
     * Change model published.
     */
    public function changePublished(Model $model): RedirectResponse
    {
        $model->update(['published' => ! $model->getOriginal('published')]);

        return back()->with('success', trans('crud.messages.publish_status_saved'));
    }
}
