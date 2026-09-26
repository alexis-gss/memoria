<button class="game-folder btn btn-primary text-decoration-none text-white border-0 rounded-2 px-2 py-0"
    style="background-color:{{ $folder->color }}" name="folder" value="{{ $folder->slug }}" data-bs-tooltip="tooltip"
    title="{{ __('fo_search_filter_by_folder', ['folder' => $folder->name]) }}">
    {{ $folder->name }}
</button>

@if ($publishedTags->isNotEmpty())
    <span class="ms-1">-</span>

    @foreach ($publishedTags as $tag)
        <button class="game-tags btn btn-secondary text-decoration-none text-white border-0 rounded-2 px-2 py-0 ms-1"
            name="tag" value="{{ $tag->slug }}" data-bs-tooltip="tooltip"
            title="{{ __('fo_search_filter_by_tag', ['tag' => $tag->name]) }}">
            {{ $tag->name }}
        </button>
    @endforeach
@endif
