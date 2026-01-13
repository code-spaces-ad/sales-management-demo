<?php

/**
 * 会社情報設定画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\System;

use App\Consts\DB\System\HeadOfficeInfoConst;
use App\Helpers\ImageHelper;
use App\Helpers\LogHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\HeadOfficeInfoEditRequest;
use App\Models\System\HeadOfficeInfo;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * 会社情報設定画面用コントローラー
 */
class HeadOfficeInfoController extends Controller
{
    /**
     * HeadOfficeInfoController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param HeadOfficeInfo $head_office_info
     * @return View
     */
    public function edit(HeadOfficeInfo $head_office_info): View
    {
        $data = [
            /** 対象レコード */
            'target_record_data' => $head_office_info,
        ];

        return view('system.head_office_info.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param HeadOfficeInfoEditRequest $request
     * @param HeadOfficeInfo $head_office_info
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(HeadOfficeInfoEditRequest $request, HeadOfficeInfo $head_office_info): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $head_office_info->company_name = $request->company_name;
            $head_office_info->representative_name = $request->representative_name;
            $head_office_info->postal_code1 = $request->postal_code1;
            $head_office_info->postal_code2 = $request->postal_code2;
            $head_office_info->address1 = $request->address1;
            $head_office_info->address2 = $request->address2;
            $head_office_info->tel_number = $request->tel_number;
            $head_office_info->fax_number = $request->fax_number;
            $head_office_info->tel_number2 = $request->tel_number2;
            $head_office_info->email = $request->email;
            $head_office_info->fiscal_year = $request->fiscal_year;

            if ($request->company_seal_image_del_flag) {
                $head_office_info->company_seal_image_file_name = null;
                $head_office_info->company_seal_image = null;
                $request->company_seal_image = null;
            }

            if (isset($request->company_seal_image)) {
                $file = $request->company_seal_image;

                // リサイズ
                $image = ImageHelper::resizeImage($file);

                $file_name = $file->getClientOriginalName();
                $head_office_info->company_seal_image = $image->encode();
                $head_office_info->company_seal_image_file_name = $file_name;
            }

            $head_office_info->invoice_number = $request->invoice_number;
            $head_office_info->bank_account1 = $request->bank_account1;
            $head_office_info->bank_account2 = $request->bank_account2;
            $head_office_info->bank_account3 = $request->bank_account3;
            $head_office_info->bank_account4 = $request->bank_account4;

            $head_office_info->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        $message = config('consts.message.common.update_success');
        if ($error_flag) {
            $message = config('consts.message.common.update_failed');
        }

        // 編集画面へリダイレクト
        $company_id = HeadOfficeInfoConst::COMPANY_ID;

        return redirect(route('system.head_office_info.edit', $company_id))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
