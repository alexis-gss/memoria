<a href="{{ route('fo.games.show', $gameModel->slug) }}" class="card shadow text-decoration-none text-center border-0 h-100"
    data-bs-tooltip="tooltip" data-bs-placement="top" title="{{ __('fo_access_game', ['gameName' => $gameModel->name]) }}">
    <div class="ratio ratio-16x9 card-img-top overflow-hidden">
        <div class="picture-loader position-absolute top-0 start-0 d-flex justify-content-center align-items-center bg-primary w-100 h-100 z-3">
            <div class="spinner-border text-light" role="status">
                <span class="visually-hidden">
                    {{ __("global_text_loading") }}
                </span>
            </div>
        </div>
        <img src="{{ asset($gameModel->picture) }}" class="img-fluid d-none" alt="{{ __('fo_image_of', ['gameName' => $gameModel->name]) }}">
    </div>
    <div class="card-body d-flex justify-content-center align-items-center">
        <h5 class="card-title title-font-regular m-0">{{ $gameModel->name }}</h5>
    </div>
    <div class="card-footer">
        <p class="text-primary m-0">
            <i class="fa-regular fa-clock"></i>
            {{ $gameModel->published_at->lessThan(Carbon::now()->sub(1, 'day'))
                ? sprintf('%s %s', str(__('validation.custom.published_at'))->ucFirst(), $gameModel->published_at->isoFormat('LL'))
                : sprintf('%s %s', str(__('validation.custom.published'))->ucFirst(), $gameModel->published_at->diffForHumans()) }}
        </p>
    </div>
</a>
