<?php

/**
 * Autocomplete用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers;

use App\Models\Master\MasterRecipient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutocompleteController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListRecipientName(Request $request): JsonResponse
    {
        $d_arr = [];

        $data = MasterRecipient::query()
            ->where('branch_id', '=', $request->get('branch'))
            ->where(function ($query) use ($request) {
                $query->where('name', 'LIKE', $request->get('search') . '%')
                    ->orWhere('name_kana', 'LIKE', $request->get('search') . '%');
            })
            ->get('name');

        if ($data) {
            foreach ($data as $d) {
                $d_arr[] = $d->name;
            }
        }

        return response()->json(['data' => $d_arr, 'csrf_token' => csrf_token()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getListRecipientNameKana(Request $request): JsonResponse
    {
        $data = MasterRecipient::query()
            ->where('branch_id', '=', $request->get('branch'))
            ->where('name', '=', $request->get('search'))
            ->first('name_kana');

        if (empty($data)) {
            return response()->json(['data' => '', 'csrf_token' => csrf_token()]);
        }

        return response()->json(['data' => $data->name_kana, 'csrf_token' => csrf_token()]);
    }
}
