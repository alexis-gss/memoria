{{-- NAME --}}
<div class="col-12 col-md-6 mb-3">
    <fieldset class="bg-body-tertiary border rounded-3 p-3">
        <legend class="fw-bold fst-italic">
            <i class="fa-solid fa-gears"></i>
            {{ __('bo_title_general_informations') }}
        </legend>
        <div class="row mb-3">
            <div class="col-12 form-group">
                <label class="col-form-label" for="name">
                    <b>{{ __('bo_label_identification') }}&nbsp;*</b>
                    <span data-bs-tooltip="tooltip" data-bs-placement="top" title="{{ __('bo_tooltip_name_tag') }}">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>
                </label>
                <div class="word-counter" data-json='@json(['id' => 'name'])'></div>
                @if (config('app.locale') !== config('app.fallback_locale'))
                    <div class="default-translation-value">
                        {!! nl2br(
                            __('validation.rule.default_field', [
                                'field' => str(__('validation.attributes.name'))->ucFirst(),
                                'value' => empty(!$tagModel->getTranslation('name', config('app.fallback_locale')))
                                    ? $tagModel->getTranslation('name', config('app.fallback_locale'))
                                    : '…',
                            ]),
                        ) !!}
                    </div>
                @endif
                <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text"
                    value="{{ old('name', $tagModel->name ?? '') }}" required placeholder="{{ __('validation.attributes.name') }}*">
                <small class="text-body-secondary">
                    {{ __('validation.between.string', [
                        'attribute' => __('validation.attributes.name'),
                        'min' => 3,
                        'max' => 255,
                    ]) }}
                </small>
                <x-back.input-error inputName="name"/>
            </div>
        </div>
    </fieldset>
</div>
{{-- PUBLISHED --}}
<div class="col-12 col-md-6 mb-3">
    <fieldset class="bg-body-tertiary border rounded-3 p-3">
        <legend class="fw-bold fst-italic">
            <i class="fa-solid fa-eye"></i>
            {{ __('bo_title_visibility') }}
        </legend>
        <div class="row mb-3">
            <div class="col-12 form-check form-switch">
                <div class="form-check form-switch">
                    <input class="form-check-input @error('published') is-invalid @enderror" id="flexSwitchCheckDefault"
                        name="published" type="checkbox" value="1" role="button" @checked(old('published', $tagModel->published ?? ''))>
                    <label class="form-check-label" for="flexSwitchCheckDefault" role="button">
                        <b>{{ str(__('validation.custom.publishment'))->ucfirst() }}</b>
                    </label>
                    <br>
                    <small class="form-text text-body-secondary">
                        {{ __('validation.boolean', ['attribute' => __('validation.custom.publishment')]) }}
                    </small>
                </div>
                <x-back.input-error inputName="published"/>
            </div>
        </div>
    </fieldset>
</div>
