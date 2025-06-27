<p class="fs-5 fw-semibold my-4 mt-xxl-0 text-center">
    {{ str(__('bo_other_stats_most_visited_pages'))->ucFirst()->value() }}
</p>
@if ($models->isNotEmpty())
    <ul class="list-group border-0">
        @foreach ($models as $key => $visitModel)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                @if ($visitModel->game->published)
                    <a class="btn btn-primary btn-sm" href="{{ route('fo.games.show', $visitModel->game->slug) }}"
                        data-bs-tooltip="tooltip" role="button" target="_blank"
                        title="{{ __('crud.actions_model.show', ['model' => trans_choice('models.game', 1)]) }}">
                    @else
                        <p class="m-0">
                @endif
                    {{ __('bo_other_stats_game_id', ['game' => $visitModel->game->name]) }}
                @if ($visitModel->game->published)
                        <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                    </a>
                @else
                    </p>
                @endif
                <span class="badge rounded-pill text-bg-secondary">{{ $visitModel->count }}</span>
            </li>
        @endforeach
    </ul>
@else
    <p class="fst-italic text-center m-0">{{ __('bo_other_stats_none') }}</p>
@endif
