<?php

/**
 * API送信用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Api;

use App\Helpers\CodeHelper;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class SendController extends Controller
{
    private $api_server;

    public function __construct()
    {
        parent::__construct();
        $this->api_server = env('INTERNAL_API_URL');
    }

    /**
     * 住所検索
     *
     * @param Request $request
     * @return string
     *
     * @throws GuzzleException
     */
    public function searchAddress(Request $request): string
    {

        $url = $this->api_server . '/api/search_address';
        $data = [
            'post_code' => $request->postal_code,
        ];
        $response = (new ApiCommonController())->apiBasicCommunicationTypeGet($url, $data);
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        return $response->getBody()->getContents();
    }

    /**
     * 空番検索
     *
     * @param Request $request
     * @return string
     */
    public function searchAvailableNumber(Request $request): string
    {
        $parent_key = $request->parent_key ?? null;
        $parent_id = $request->parent_id ?? null;
        [$length, $code_list] = CodeHelper::getCodeList($request->type, $parent_key, $parent_id);
        $result = CodeHelper::getAvailableNumber($code_list, intval($request->available_number));

        return sprintf("%0{$length}d", $result);
    }

    /**
     * 空番検索
     *
     * @param Request $request
     * @return string
     *
     * @throws GuzzleException
     */
    public function searchAvailableSortNumber(Request $request): string
    {
        [$sort_list] = CodeHelper::getSortList($request->type);
        $result = CodeHelper::getAvailableNumber($sort_list, intval($request->available_number));

        return $result;
    }
}
