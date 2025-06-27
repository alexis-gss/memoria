<p class="fs-5 fw-semibold mb-4 text-center">
    {{ str(__('bo_other_stats_most_liked_pictures'))->ucFirst()->value() }}
</p>
@if ($models->isNotEmpty())
    <ul class="list-group border-0">
        @foreach ($models as $key => $ratingModel)
            @php
                $pictureExist = Storage::disk('public')->exists(
                    sprintf('pictures/%s/%s.webp', $ratingModel->picture->game->slug, $ratingModel->picture->uuid),
                );
            @endphp
            <li class="list-group-item d-flex justify-content-between align-items-center">
                @if ($pictureExist)
                    <button class="btn btn-primary btn-sm" data-bs-tooltip="tooltip" data-bs-toggle="modal"
                        data-bs-target="#ModalViewPicture{{ $key }}" type="button"
                        title="{{ __('crud.actions_model.show', ['model' => __('models.picture')]) }}">
                @else
                    <p class="m-0">
                @endif
                {{ __('bo_other_stats_picture_id', [
                    'game' => $ratingModel->picture->game->name,
                ]) }}
                @if ($pictureExist)
                    </button>
                @else
                    </p>
                @endif
                <span class="badge rounded-pill text-bg-secondary">{{ $ratingModel->count }}</span>
                @if ($pictureExist)
                    <x-back.modal-view-picture id="ModalViewPicture{{ $key }}" :pictureAlt="$ratingModel->picture->label"
                        :pictureTitle="__('bo_other_stats_picture_id', [
                            'game' => $ratingModel->picture->game->name,
                        ])" :pictureSrc="sprintf(
                            '%s/storage/pictures/%s/%s.webp',
                            config('app.url'),
                            $ratingModel->picture->game->slug,
                            $ratingModel->picture->uuid,
                        )" />
                @endif
            </li>
        @endforeach
    </ul>
@else
    <p class="fst-italic text-center m-0">{{ __('bo_other_stats_none') }}</p>
@endif
