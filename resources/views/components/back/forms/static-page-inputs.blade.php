{{-- NAME/SEO_TITLE/SEO_DESCRIPTION --}}
<div class="col-12 mb-3">
    <fieldset class="bg-body-tertiary border rounded-3 p-3">
        <legend class="fw-bold fst-italic">
            <i class="fa-solid fa-gears"></i>
            {{ __('bo_title_general_informations') }}
        </legend>
        <div class="row">
            <div class="col-12 col-md-6 form-group mb-3">
                <label class="col-form-label" for="folder_id">
                    <b>{{ __('bo_label_seo_title') }}&nbsp;*</b>
                    <span data-bs-tooltip="tooltip" data-bs-placement="top" title="{{ __('bo_tooltip_SEO_title_page') }}">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </label>
                <div class="word-counter" data-json='@json(['id' => 'seo_title'])'></div>
                @if (config('app.locale') !== config('app.fallback_locale'))
                    <div class="default-translation-value">
                        {!! nl2br(
                            __('validation.rule.default_field', [
                                'field' => str(__('validation.custom.seo_title'))->ucFirst(),
                                'value' => empty(!$staticPageModel->getTranslation('seo_title', config('app.fallback_locale')))
                                    ? $staticPageModel->getTranslation('seo_title', config('app.fallback_locale'))
                                    : '…',
                            ]),
                        ) !!}
                    </div>
                @endif
                <input class="form-control @error('seo_title') is-invalid @enderror" id="seo_title" name="seo_title" type="text"
                    value="{{ old('seo_title', $staticPageModel->seo_title ?? '') }}" required
                    placeholder="{{ __('validation.custom.seo_title') }}">
                <small class="text-body-secondary">
                    {{ __('validation.between.string', [
                        'attribute' => __('validation.custom.seo_title'),
                        'min' => 3,
                        'max' => 70,
                    ]) }}
                </small>
                <x-back.input-error inputName="seo_title"/>
            </div>
            <div class="col-12 col-md-6 form-group mb-3">
                <label class="col-form-label" for="folder_id">
                    <b>{{ __('bo_label_seo_description') }}&nbsp;*</b>
                    <span data-bs-tooltip="tooltip" data-bs-placement="top" title="{{ __('bo_tooltip_SEO_description_page') }}">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </label>
                <div class="word-counter" data-json='@json(['id' => 'seo_description'])'></div>
                @if (config('app.locale') !== config('app.fallback_locale'))
                    <div class="default-translation-value">
                        {!! nl2br(
                            __('validation.rule.default_field', [
                                'field' => str(__('validation.custom.seo_description'))->ucFirst(),
                                'value' => empty(!$staticPageModel->getTranslation('seo_description', config('app.fallback_locale')))
                                    ? $staticPageModel->getTranslation('seo_description', config('app.fallback_locale'))
                                    : '…',
                            ]),
                        ) !!}
                    </div>
                @endif
                <input class="form-control @error('seo_description') is-invalid @enderror" id="seo_description" name="seo_description"
                    type="text" value="{{ old('seo_description', $staticPageModel->seo_description ?? '') }}" required
                    placeholder="{{ __('validation.custom.seo_description') }}">
                <small class="text-body-secondary">
                    {{ __('validation.between.string', [
                        'attribute' => __('validation.custom.seo_description'),
                        'min' => 50,
                        'max' => 160,
                    ]) }}
                </small>
                <x-back.input-error inputName="seo_description"/>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6 form-group mb-3">
                <label class="col-form-label" for="folder_id">
                    <b>{{ __('bo_label_identification') }}&nbsp;*</b>
                    <span data-bs-tooltip="tooltip" data-bs-placement="top" title="{{ __('bo_tooltip_title_page') }}">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </label>
                <div class="word-counter" data-json='@json(['id' => 'title'])'></div>
                @if (config('app.locale') !== config('app.fallback_locale'))
                    <div class="default-translation-value">
                        {!! nl2br(
                            __('validation.rule.default_field', [
                                'field' => str(__('validation.attributes.title'))->ucFirst(),
                                'value' => empty(!$staticPageModel->getTranslation('title', config('app.fallback_locale')))
                                    ? $staticPageModel->getTranslation('title', config('app.fallback_locale'))
                                    : '…',
                            ]),
                        ) !!}
                    </div>
                @endif
                <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" type="text"
                    value="{{ old('title', $staticPageModel->title ?? '') }}" required
                    placeholder="{{ __('validation.attributes.title') }}">
                <small class="text-body-secondary">
                    {{ __('validation.between.string', [
                        'attribute' => __('validation.attributes.title'),
                        'min' => 3,
                        'max' => 255,
                    ]) }}
                </small>
                <x-back.input-error inputName="title"/>
            </div>
        </div>
    </fieldset>
</div>
