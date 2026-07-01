<x-breadcrumbs.breadcrumb-body :brParam="$brParam" />
@php
    $breadcrumbHtml = ob_get_clean();
    $navData = [
        'visible'        => (request()->routeIs('fo.games.show') && isset($gameModel) && !is_null($gameModel))
            || request()->routeIs('fo.ranks.index'),
        'breadcrumbHtml' => $breadcrumbHtml,
        'gameModels'     => $gameModels,
        'folderModels'   => $folderModels,
        'tagModels'      => $tagModels,
        'params'         => [
            'text'   => request()->text,
            'folder' => request()->folder,
            'tag'    => request()->tag,
        ],
        'music'          => $music,
    ];
@endphp

<div data-json='@json($navData)' class="nav-bar"></div>
