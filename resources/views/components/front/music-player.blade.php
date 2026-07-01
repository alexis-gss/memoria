@if (request()->routeIs('fo.games.show') && isset($gameModel) && !is_null($gameModel) && isset($gameModel->music) && !is_null($gameModel->music))
    @php
        $dataMusicPlayer = [
            'title' => sprintf("%s - %s", $gameModel->name, pathinfo($gameModel->music, PATHINFO_FILENAME)),
            'src' => asset($gameModel->music),
            'options' => $musicOptions,
            'picture' => asset($gameModel->picture),
        ];
    @endphp
    <div data-json='@json($dataMusicPlayer)' @class([
        'music-player mt-2',
    ])></div>
@endif
