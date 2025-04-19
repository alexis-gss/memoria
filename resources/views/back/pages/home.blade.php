@extends('back.layout')

@section('title', __('bo_other_back_office'))
@section('description', __('bo_other_back_office_desc'))

@section('content')
    <div class="row">
        <div class="col-12">
            <h1 class="h2 fw-bold m-0 pb-3">{{ __('bo_other_back_office') }}</h1>
        </div>
        <div class="col-12 mb-3">
            <div class="card bg-body-tertiary p-5 rounded-3 text-center">
                <h5 class="fw-bold fst-italic">
                    {{ __('bo_other_home_hello', ['first_name' => auth()->user()->first_name, 'last_name' => auth()->user()->last_name]) }}
                </h5>
                <p class="m-0">
                    {{ __('bo_other_home_welcome') }}
                </p>
                <p class="m-0">
                    {{ __('bo_other_home_contact') }}
                </p>
            </div>
        </div>
        <div class=" pb-3">
            <h2 class="h2 fw-bold m-0">{{ __('bo_other_about') }}</h2>
        </div>
        <div class="col-12">
            <div class="row">
                @php
                    $arrayCards = [
                        [
                            'icon' => 'fa-solid fa-globe',
                            'title' => __('bo_other_home_live'),
                            'text' => __('bo_other_home_live_details'),
                            'link' => route('fo.games.index'),
                            'linkTitle' => __('bo_tooltip_home_live'),
                            'linkText' => __('bo_other_home_live_label', ['projectName' => config('app.name')]),
                        ],
                        [
                            'icon' => 'fa-solid fa-book',
                            'title' => __('bo_other_home_documentation'),
                            'text' => __('bo_other_home_documentation_details'),
                            'link' => 'https://doc-memoria.alexis-gousseau.com',
                            'linkTitle' => __('bo_tooltip_home_documentation'),
                            'linkText' => __('bo_other_home_documentation_label', ['projectName' => config('app.name')]),
                        ],
                        [
                            'icon' => 'fa-brands fa-github',
                            'title' => __('global_acces_github'),
                            'text' => __('bo_other_home_github_details'),
                            'link' => 'https://github.com/alexis-gss/memoria',
                            'linkTitle' => __('bo_tooltip_home_github'),
                            'linkText' => __('bo_other_home_github_label', ['projectName' => config('app.name')]),
                        ],
                    ]
                @endphp
                @foreach($arrayCards as $card)
                    <x-back.home-card :card="$card"/>
                @endforeach
            </div>
        </div>
    </div>
@endsection
