<?php

declare(strict_types=1);

namespace App\Traits\Controllers;

use App\Http\Requests\Bo\UpdateChangeOrderRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

trait ChangesModelOrder
{
    /**
     * Change order.
     *
     * @throws RuntimeException If the route parameter name does not match the model used.
     */
    public function changeOrder(
        UpdateChangeOrderRequest $request,
        Model $currentModel
    ): RedirectResponse {
        $params    = collect(request()->route()->parameterNames);
        $className = Str::of($params->get($params->count() - 2))->lower()->ucfirst()->value();

        /** Check if the model has a multiple words class name */
        if (str_contains($className, '_')) {
            $classNameExplode = explode('_', $className);
            $className        = '';
            foreach ($classNameExplode as $string) {
                $className .= Str::of($string)->ucfirst()->value();
            }
        }
        $className = "\\App\\Models\\$className";
        throw_unless(class_exists($className), RuntimeException::class, "The $className class doesn't exist.");

        /** @var Model|null */
        $currentModel = $className::where($currentModel->getRouteKeyName(), $currentModel->getRouteKey())->first();

        if (! $currentModel) {
            return back()->with('error', trans('crud.messages.order_not_changed'));
        }

        return DB::transaction(function () use ($request, $className, $currentModel) {
            /** @var Model|null */
            $targetModel = $className::where(
                'order',
                $request->direction ? '<' : '>',
                $currentModel->getOriginal('order')
            )
                ->orderBy('order', $request->direction ? 'DESC' : 'ASC')->first();

            if (! $targetModel) {
                return back()->with('error', trans('crud.messages.order_not_changed'));
            }
            $newOrder = $currentModel->getOriginal('order');
            $currentModel->update(['order' => $targetModel->getOriginal('order')]);
            $targetModel->update(['order' => $newOrder]);

            return back()->with('success', trans('crud.messages.order_changed'));
        });
    }
}
