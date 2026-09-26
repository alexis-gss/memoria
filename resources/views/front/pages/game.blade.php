@extends('front.layout', ['brParam' => $gameModel])

@section('title', $gameModel->name)
@section('description', __('fo_game_description', ['game' => $gameModel->name]))
@section('breadcrumb', request()->route()->getName())

@php
    $publishedTags = $gameModel->tags
        ->where('published', true)
        ->sortBy('name')
        ->values();

    $dataGame = [
        'gameId' => $gameModel->igdb_id,
        'gameName' => $gameModel->name,
    ];

    $publicationLabel = $gameModel->published_at->lessThan(Carbon::now()->subDay())
        ? sprintf('%s %s', str(__('validation.custom.published_at'))->ucFirst(), $gameModel->published_at->isoFormat('LL'))
        : sprintf('%s %s', str(__('validation.custom.published'))->ucFirst(), $gameModel->published_at->diffForHumans());

    $visitCount = $gameModel->visits->count();
    $visitLabel = sprintf('%s %s', $visitCount, $visitCount === 1
        ? __('models.visit')
        : str(__('models.visit'))->plural()
    );

    $pictureData = [
        'gameName' => $gameModel->name,
        'gameSlug' => $gameModel->slug,
        'pictureModels' => $pictureModels,
        'ratingModels' => $ratingModels,
        'routeName' => 'fo.games.pictures',
        'relatedGamesViews' => $relatedGamesViews,
    ];
@endphp

@section('content')
    <section class="main-page" data-aos="fade">
        <div class="col-12 py-5">
            <h1 class="title-font-regular position-relative mx-auto mb-3 w-fit px-5 py-1 text-center">
                {{ $gameModel->name }}
                <span class="d-none d-sm-block angles"></span>
            </h1>
            {{-- MOBILE --}}
            <div class="d-flex d-lg-none flex-column align-items-center user-select-none w-100 text-center">
                <div class="d-flex flex-row justify-content-center align-items-center mb-2">
                    <x-front.back-button />
                    <span class="mx-1">-</span>
                    <div class="details-button" data-json='@json($dataGame)'></div>
                </div>
                <div class="d-flex flex-row flex-wrap justify-content-center align-items-center">
                    <x-front.game-filters :folder="$gameModel->folder" :publishedTags="$publishedTags" />
                </div>
            </div>
            {{-- DESKTOP --}}
            <div class="d-none d-lg-flex flex-row justify-content-center align-items-center user-select-none w-100 text-center">
                <x-front.back-button />
                <span class="mx-1">-</span>
                <x-front.game-filters :folder="$gameModel->folder" :publishedTags="$publishedTags" />
                <span class="mx-1">-</span>
                <div class="details-button" data-json='@json($dataGame)'></div>
            </div>
            <div class="d-flex flex-column flex-sm-row-reverse justify-content-center align-items-center w-100 mt-3 px-1">
                <p class="text-secondary mb-3 ms-sm-5 m-sm-0">
                    <i class="fa-regular fa-eye"></i>
                    {{ $visitLabel }}
                </p>
                <p class="text-secondary m-0">
                    <i class="fa-regular fa-clock"></i>
                    {{ $publicationLabel }}
                </p>
            </div>
        </div>
        <div class="col-12">
            <div
                class="game-pictures"
                data-json='@json($pictureData)'
            ></div>
        </div>
    </section>
@endsection

@push('scripts')
    {!! $gameModel->setPersonSchema()->toScript() !!}
    {!! $gameModel->setWebsiteSchema()->toScript() !!}
    {!! $gameModel->toSchemaOrg()->toScript() !!}
@endpush
