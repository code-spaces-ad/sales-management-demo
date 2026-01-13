<?php

namespace App\Services\System;

use App\Http\Requests\System\SettingsRequest;
use App\Models\Master\MasterOfficeFacility;
use App\Models\System\Settings;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    public function __construct() {}

    /**
     * 設定一覧
     *
     * @return array
     */
    public function index(): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'office_facilities' => MasterOfficeFacility::query()->select([
                    DB::raw('m_departments.name AS department_name'),
                    DB::raw('m_departments.id AS department_id'),
                    DB::raw('m_departments.code AS department_code'),
                    DB::raw('m_office_facilities.id AS id'),
                    DB::raw('m_office_facilities.name AS name'),
                    DB::raw('m_office_facilities.code AS code'),
                ])
                    ->leftJoin('m_departments', 'm_office_facilities.department_id', '=', 'm_departments.id')
                    ->leftJoin('m_employees', 'm_office_facilities.manager_id', '=', 'm_employees.id')
                    ->whereNull('m_departments.deleted_at')
                    ->oldest('m_departments.code')
                    ->oldest('m_office_facilities.code')->get(),
            ],
            'settings' => Settings::query()->pluck('value', 'key')->toArray(),
            'env' => file_get_contents(base_path('.env')),
        ];
    }

    /**
     * 設定 登録
     *
     * @param SettingsRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(SettingsRequest $request): RedirectResponse
    {
        $error_flag = false;
        DB::beginTransaction();
        try {

            $settings = Settings::all();

            foreach ($request->validated() as $group => $keys) {
                foreach ($keys as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $setting = $settings
                        ->where('group', $group)
                        ->where('key', $key)
                        ->where('value', '!=', $value)
                        ->first();

                    if ($setting) {
                        $setting->value = $value;
                        $setting->save();
                    }
                }
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $error_flag = true;
        }

        $message = config('consts.message.common.update_success');
        if ($error_flag) {
            $message = config('consts.message.common.update_failed');
        }

        return redirect(route('system.settings.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
