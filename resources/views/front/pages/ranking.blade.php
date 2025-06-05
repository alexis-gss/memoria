@extends('front.layout', ['brParam' => $rankModels])

@section('title', $staticPageModel->seo_title ?? __('fo_ranking_title'))
@section('description', $staticPageModel->seo_description ?? __('fo_ranking_description'))
@section('breadcrumb', request()->route()->getName())

@section('content')
    <section class="main-page mx-md-5 px-md-5 m-0 p-0" data-aos="fade">
        <div class="row w-100 mx-auto">
            <div class="col-12">
                <div class="my-5">
                    <h1 class="title-font-regular position-relative mx-auto mb-3 w-fit px-5 py-1 text-center">
                        {{ __('fo_ranking_title') }}
                        <span class="d-none d-sm-block angles"></span>
                    </h1>
                    <div class="d-flex justify-content-center align-items-center user-select-none w-100 flex-row text-center">
                        <a class="btn btn-secondary bg-primary rounded-2 text-decoration-none border-0 shadow px-2 py-0 text-white"
                            data-bs-tooltip="tooltip" data-bs-placement="top" href="{{ route('fo.games.index') }}"
                            title="{{ __('fo_back_to_homepage', ['model' => trans_choice('models.game', \INF)]) }}">
                            <i class="fa fa-arrow-left"></i>
                        </a>
                        <span class="mx-1">-</span>
                        <p class="bg-secondary rounded-2 shadow m-0 px-2 py-0 text-white">
                            {{ str(__('fo_ranking_details', ['number' => $rankModels->count()]))->ucFirst() }}
                        </p>
                    </div>
                </div>
                @if ($rankModels->count())
                    <ul class="bg-secondary rounded-3 shadow p-2">
                        @foreach ($rankModels as $key => $rankModel)
                            <li class="list-group-item rounded-2 border-0 bg-transparent p-0">
                                <a class="btn btn-secondary position-relative d-flex justify-content-start align-items-center btn text-decoration-none w-100 flex-row border-0 p-1 text-white"
                                    href="{{ route('fo.games.show', $rankModel->game->slug) }}">
                                    <div class="d-flex justify-content-start align-items-center flex-row">
                                        <span class="list-group-item-span z-0"
                                            style="background-color: {{ $rankModel->game->folder()->firstOrFail()->color }};"></span>
                                        <span class="title-font-regular z-1 ps-1">
                                            @php $rank = $key + 1; @endphp
                                            {{ $rank < 10 ? 0 . $rank : $rank }}&nbsp;-&nbsp;
                                        </span>
                                    </div>
                                    <p class="z-1 my-1">{{ $rankModel->game->name }}</p>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="m-0">{{ __('fo_ranking_no_games') }}</p>
                @endif
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    {!! $staticPageModel->setPersonSchema()->toScript() !!}
    {!! $staticPageModel->setWebsiteSchema()->toScript() !!}
    {!! $staticPageModel->toSchemaOrg()->toScript() !!}
@endpush
