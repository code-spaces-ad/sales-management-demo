<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

/**
 * 住所検索用ヘルパークラス
 */
class AddressHelper
{
    /**
     * 住所検索（外部API）
     *
     * @param int $zipcode
     * @return JsonResponse
     *
     * @throws GuzzleException
     */
    public static function searchAddress(int $zipcode): JsonResponse
    {
        $client = new Client();
        $api = config('consts.default.api.address_search_url') . $zipcode;
        $response = $client->get($api);

        return response()->json(json_decode($response->getBody(), true));
    }
}
