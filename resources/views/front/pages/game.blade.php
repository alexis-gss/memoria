@extends('front.layout', ['brParam' => $gameModel])

@section('title', $gameModel->name)
@section('description', __('fo_game_description', ['game' => $gameModel->name]))
@section('breadcrumb', request()->route()->getName())

@section('content')
    <section class="main-page" data-aos="fade">
        <div class="col-12 py-5">
            <h1 class="title-font-regular position-relative mx-auto mb-3 w-fit px-5 py-1 text-center">
                {{ $gameModel->name }}
                <span class="d-none d-sm-block angles"></span>
            </h1>
            <div class="d-flex flex-column flex-lg-row justify-content-center align-items-center user-select-none w-100 text-center">
                <div class="d-flex flex-row justify-content-center align-items-center pb-2 pb-lg-0">
                    <a
                        href="{{ route('fo.games.index') }}"
                        class="btn btn-primary text-decoration-none text-white border-0 rounded-2 px-2 py-0"
                        data-bs-tooltip="tooltip" title="{{ __('fo_other_back_home') }}"
                    >
                        <i class="fa fa-arrow-left"></i>
                    </a>
                </div>
                <span class="d-none d-lg-block mx-1">-</span>
                <div class="d-flex flex-row justify-content-center align-items-center pb-2 pb-lg-0">
                    <button
                        class="game-folder btn btn-primary text-decoration-none text-white border-0 rounded-2 px-2 py-0"
                        style="background-color:{{ $gameModel->folder->color }}"
                        name="folder"
                        value="{{ $gameModel->folder->slug }}"
                        data-bs-tooltip="tooltip" title="{{ __('fo_search_filter_by_folder', ['folder' => $gameModel->folder->name]) }}"
                    >
                        {{ $gameModel->folder->name }}
                    </button>
                    @if ($gameModel->tags->isNotEmpty() && $gameModel->tags->contains('published', true))
                        <span class="ms-1">-</span>
                        @foreach ($gameModel->tags->sortBy('name') as $tag)
                            @if ($tag->published)
                                <button
                                    class="game-tags btn btn-secondary text-decoration-none text-white border-0 rounded-2 px-2 py-0 ms-1"
                                    name="tag" value="{{ $tag->slug }}"
                                    data-bs-tooltip="tooltip" title="{{ __('fo_search_filter_by_tag', ['tag' => $tag->name]) }}"
                                >
                                    {{ $tag->name }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                </div>
                <span class="d-none d-lg-block mx-1">-</span>
                <div class="d-flex flex-row justify-content-center align-items-center">
                    @php
                        $dataGame = [
                            'gameId' => $gameModel->igdb_id,
                            'gameName' => $gameModel->name,
                        ];
                    @endphp
                    <div class="details-button" data-json='@json($dataGame)'></div>
                </div>
            </div>
            <div class="d-flex flex-column flex-sm-row-reverse justify-content-center align-items-center w-100 mt-3 px-1">
                <p class="text-secondary mb-3 ms-sm-5 m-sm-0">
                    <i class="fa-regular fa-eye"></i>
                    {{ sprintf(
                        '%s %s',
                        $gameModel->visits->count(),
                        $gameModel->visits->isNotEmpty() ? str(__('models.visit'))->plural() : __('models.visit'),
                    ) }}
                </p>
                <p class="text-secondary m-0">
                    <i class="fa-regular fa-clock"></i>
                    {{ $gameModel->published_at->lessThan(Carbon::now()->sub(1, 'day'))
                        ? sprintf('%s %s', str(__('validation.custom.published_at'))->ucFirst(), $gameModel->published_at->isoFormat('LL'))
                        : sprintf('%s %s', str(__('validation.custom.published'))->ucFirst(), $gameModel->published_at->diffForHumans()) }}
                </p>
            </div>
        </div>
        <div class="col-12">
            @php
                $dataGame = [
                    'gameName' => $gameModel->name,
                    'gameSlug' => $gameModel->slug,
                    'pictureModels' => $pictureModels,
                    'ratingModels' => $ratingModels,
                    'routeName' => 'fo.games.pictures',
                    'relatedGamesViews' => $relatedGamesViews,
                ];
            @endphp
            <div class="game-pictures" data-json='@json($dataGame)'></div>
        </div>
    </section>
@endsection

@push('scripts')
    {!! $gameModel->setPersonSchema()->toScript() !!}
    {!! $gameModel->setWebsiteSchema()->toScript() !!}
    {!! $gameModel->toSchemaOrg()->toScript() !!}
@endpush
