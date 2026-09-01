<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class IgdbController extends Controller
{
    /**
     * Get the URL of a game from IGDB by its id.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function url(Request $request): \Illuminate\Http\JsonResponse
    {
        $id = (int) $request->input('id');

        if (! $id) {
            return response()->json(['url' => null], 422);
        }

        $token = Cache::remember('igdb.access_token', now()->addDays(30), function () {
            $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', [
                'client_id'     => config('services.igdb.client_id'),
                'client_secret' => config('services.igdb.client_secret'),
                'grant_type'    => 'client_credentials',
            ]);

            return $response->ok() ? $response->json('access_token') : null;
        });

        if (! $token) {
            return response()->json(['url' => null], 502);
        }

        $url = Cache::remember("igdb.game.{$id}.url", now()->addWeek(), function () use ($id, $token) {
            $response = Http::withHeaders([
                'Client-ID'     => config('services.igdb.client_id'),
                'Authorization' => "Bearer {$token}",
            ])
                ->withBody("fields url; where id = {$id};", 'text/plain')
                ->post('https://api.igdb.com/v4/games');

            return $response->ok() ? $response->json('0.url') : null;
        });

        return response()->json(['url' => $url]);
    }
}
