<script @if (!empty($nonce)) nonce="{{ $nonce }}" @endif>
    if (!window.__SYSTEM) {
        window.__SYSTEM = {};
    }
    window.__SYSTEM._locale = '{{ app()->getLocale() }}';
    window.__SYSTEM._translations = @json(cache(sprintf('translations.%s', app()->getLocale())) ?? []);
    if (!window.__SYSTEM._routes) {
        window.__SYSTEM._routes = {}
    }
    window.__SYSTEM._routes.fo = {
        games: {
            show: "{{ route('fo.games.show', ['slug' => 'SLUG']) }}",
            pictures: "{{ route('fo.games.pictures', ['slug' => 'SLUG']) }}",
            related: "{{ route('fo.games.related', ['slug' => 'SLUG']) }}",
            filtered: "{{ route('fo.games.filtered') }}",
        },
        music: {
            options: "{{ route('fo.music.options') }}",
        },
        ratings: {
            update: "{{ route('fo.ratings.update', ['picture_id' => 'PICTUREID', 'picture_place' => 'PICTUREPLACE']) }}",
        },
    };
</script>
