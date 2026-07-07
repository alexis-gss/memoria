@extends('front.layout')

@section('title', $staticPageModel->seo_title)
@section('description', $staticPageModel->seo_description)

@section('content')
    <section class="main-home d-flex justify-content-center align-items-center mx-md-5 px-md-5 m-0 p-0 py-5" data-aos="fade">
        <div class="row w-100">
            <div class="col-12">
                <h1 class="title-font-regular position-relative text-primary text-center w-fit px-sm-5 mx-auto mb-3 p-0 py-1">
                    {{ config('app.name') }}
                    <span class="d-none d-sm-block angles"></span>
                </h1>
                <x-front.games-search :gameModels="$gameModels" :folderModels="$folderModels" :tagModels="$tagModels" />
            </div>
            <div class="col-12 d-flex flex-column flex-lg-row justify-content-between align-items-center pt-2">
                <div class="main-home-latest d-flex justify-content-start align-items-center pb-lg-0 pb-2">
                    <p class="fw-bold mw-100 m-0">{{ __('fo_last_games_added') }}</p>
                    <div class="home-text-content position-relative w-100 overflow-hidden ms-1">
                        <div class="position-relative w-100 h-100">
                            <p class="d-inline-block opacity-1 mw-100 m-0">
                                @foreach($gameLatestModels as $gameLatestModel)
                                    <a href="{{ route('fo.games.show', $gameLatestModel->slug) }}"
                                        data-bs-tooltip="tooltip" data-bs-placement="top"
                                        title="{{ __('fo_access_game', ['gameName' => $gameLatestModel->name]) }}">{{ $gameLatestModel->name }}</a>
                                    <span>{{ (!$loop->last) ? '/' : '…' }}</span>
                                @endforeach
                            </p>
                            <div class="home-text-content-transition position-absolute top-0 end-0 h-100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    {!! $staticPageModel->setPersonSchema()->toScript() !!}
    {!! $staticPageModel->setWebsiteSchema()->toScript() !!}
    {!! $staticPageModel->toSchemaOrg()->toScript() !!}
@endpush
