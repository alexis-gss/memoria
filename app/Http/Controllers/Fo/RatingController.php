<?php

namespace App\Http\Controllers\Fo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fo\UpdateRatingRequest;
use App\Models\Picture;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RatingController extends Controller
{
    /**
     * Update the specified resource.
     *
     * @param \App\Http\Requests\Fo\UpdateRatingRequest $request
     * @param \App\Models\Rating                        $rating
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRatingRequest $request, Rating $rating): \Illuminate\Http\JsonResponse
    {
        return DB::transaction(function () use ($request, $rating) {
            $validatedData = $request->validated();

            $cookie = cookie(
                'rating-uuid',
                $validatedData['uuid'],
                10 * 365 * 24 * 60
            );

            // Check if the rating already exists.
            $ratingExist = Rating::query()->where([
                ['uuid', $validatedData['uuid']],
                ['picture_id', $validatedData['picture_id']],
            ])->first();

            // Update rating.
            ($ratingExist) ? $ratingExist->delete() : $rating->fill($validatedData)->saveOrFail();

            /** @var string $toastId Toast message uuid */
            $toastId = Str::uuid()->toString();

            $picture = Picture::query()
                ->where('id', $validatedData['picture_id'])
                ->firstOrFail();

            return response()->json([
                'view' => view('components.front.toast-template', [
                    'gameName'     => $picture->game->name,
                    'picturePlace' => $request->picture_place,
                    'toastId'      => $toastId,
                    'likeStatus'   => ($ratingExist) ? false : true,
                ])->render(),
                'toastId'   => $toastId,
                'pictureId' => $validatedData['picture_id'],
            ])->withCookie($cookie);
        });
    }
}
