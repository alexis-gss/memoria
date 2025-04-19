<footer @class([
    'bg-secondary text-light shadow pt-5',
    'footer-home m-0' => request()->routeIs('fo.games.index'),
    'footer-page' => !request()->routeIs('fo.games.index'),
])>
    <div class="container">
        <div class="row w-100 mx-auto">
            {{-- PROJECT DETAILS --}}
            <div
                class="col-12 col-lg-3 d-flex flex-column justify-content-start align-items-start align-items-lg-center pb-5 pb-lg-0">
                <div>
                    <p class="title-font-regular m-0">{{ config('app.name') }}</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent border-0 text-light pt-2 pb-1">
                            <p class="text-light m-0">{!! nl2br(
                                __('fo_footer_details', [
                                    'conceptor' =>
                                        '<a class="link-light link-offset-2 link-opacity-75-hover" data-bs-tooltip="tooltip" data-bs-placement="top" href="https://www.alexis-gousseau.com" title="' .
                                        __('global_access_website') .
                                        '" target="_blank">' .
                                        config('app.conceptor') .
                                        '</a>',
                                    'appName' => config('app.name'),
                                ]),
                            ) !!}</p>
                        </li>
                        <li class="list-group-item bg-transparent border-0 text-light py-1">
                            <span>{{ __('global_all_rights_reserved') }} © 2022 - {{ date('Y') }}</span>
                        </li>
                        @if (isset($globalVersion))
                            <li class="list-group-item bg-transparent border-0 text-light pt-1 pb-0">
                                {{ __('fo_footer_currently') }} v{{ $globalVersion }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
            {{-- SITEMAP --}}
            <div
                class="col-12 col-lg-3 d-flex flex-column justify-content-start align-items-start align-items-lg-center pb-5 pb-lg-0">
                <div>
                    <p class="title-font-regular m-0">{{ __('fo_footer_sitemap') }}</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent border-0 pt-2 pb-1">
                            <a class="link-light link-offset-2 link-opacity-75-hover" data-bs-tooltip="tooltip"
                                role="button" href="{{ route('fo.games.index') }}"
                                title="{{ __('fo_tooltip_footer_access_page', ['pageName' => str(__('fo_homepage'))->lower()]) }}">
                                {{ __('fo_homepage') }}
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent border-0 pt-1 pb-0">
                            <a class="link-light link-offset-2 link-opacity-75-hover" data-bs-tooltip="tooltip"
                                role="button" href="{{ route('fo.ranks.index') }}"
                                title="{{ __('fo_tooltip_footer_access_page', ['pageName' => str(__('fo_footer_rank'))->lower()]) }}">
                                {{ __('fo_footer_rank') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            {{-- LANGUAGES --}}
            <div
                class="col-12 col-lg-3 d-flex flex-column justify-content-start align-items-start align-items-lg-center pb-5 pb-lg-0">
                <div>
                    <p class="title-font-regular m-0">{{ __('fo_footer_languages') }}</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent border-0 pt-2 pb-1">
                            <form class="text-center" id="lang-selector" action="{{ route('fo.lang.set') }}"
                                method="POST">
                                @push('scripts')
                                    <script @if (!empty($nonce)) nonce="{{ $nonce }}" @endif>
                                        document.addEventListener('DOMContentLoaded', function() {
                                            const langSelector = document.getElementById('lang-selector');
                                            if (langSelector) {
                                                langSelector.addEventListener('change', function() {
                                                    this.submit();
                                                });
                                            }
                                        });
                                    </script>
                                @endpush
                                @csrf
                                <select class="form-select bg-primary text-bg-primary border-0 w-fit cursor-pointer"
                                    aria-label="{{ __('fo_footer_languages_choose') }}" name="lang">
                                    @foreach (config('app.locales') as $key => $locale)
                                        <option value="{{ $locale }}" @selected($locale === app()->getLocale())>
                                            {{ str($locale)->upper() }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
            {{-- ABOUT --}}
            <div
                class="col-12 col-lg-3 d-flex flex-column justify-content-start align-items-start align-items-lg-center">
                <div>
                    <p class="title-font-regular m-0">{{ __('fo_footer_about') }}</p>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent border-0 pt-2 pb-1">
                            <a class="link-light link-offset-2 link-opacity-75-hover" href="{{ route('bo.login') }}"
                                title="{{ __('fo_tooltip_footer_back') }}" data-bs-tooltip="tooltip" target="_blank">
                                {{ __('fo_footer_back_label') }}
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent border-0 py-1">
                            <a class="link-light link-offset-2 link-opacity-75-hover"
                                href="https://docs-memoria.alexis-gousseau.com" target="_blank"
                                title="{{ __('bo_tooltip_home_documentation') }}" data-bs-tooltip="tooltip">
                                {{ __('fo_footer_documentation_label') }}
                            </a>
                        </li>
                        <li class="list-group-item bg-transparent border-0 pt-1 pb-0">
                            <a class="link-light link-offset-2 link-opacity-75-hover"
                                href="https://github.com/alexis-gss/memoria" target="_blank"
                                title="{{ __('bo_tooltip_home_github') }}" data-bs-tooltip="tooltip">
                                {{ __('fo_footer_github_label') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</footer>
