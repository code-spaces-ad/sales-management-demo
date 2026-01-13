<?php

/**
 * Api 共通用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class ApiCommonController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * API通信(GET型式)
     *
     * @param string $url
     * @param array $data
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function apiBasicCommunicationTypeGet(string $url, array $data): ResponseInterface
    {
        $client = new Client();

        return $client->get($url, [
            'query' => $data,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * API通信(POST型式)
     *
     * @param string $url
     * @param array $data
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    public function apiBasicCommunicationTypePost(string $url, array $data): ResponseInterface
    {
        $client = new Client();

        return $client->post($url, [
            'body' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
