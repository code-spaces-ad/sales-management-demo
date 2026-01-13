<?php

namespace App\Repositories\System;

use App\Http\Requests\System\SettingsRequest;
use App\Models\System\Settings;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SettingsRepository
{
    public function __construct() {}

    /**
     * 設定一覧
     */
    public function index(): array
    {
        return [
            'settings' => Settings::query()->pluck('value', 'key')->toArray(),
            'env' => file_get_contents(base_path('.env')),
        ];
    }

    /**
     * 設定 登録
     */
    public function store(SettingsRequest $request): RedirectResponse
    {
        $error_flag = false;
        DB::beginTransaction();
        try {

            $settings = Settings::all();

            foreach ($request->validated() as $group => $keys) {
                foreach ($keys as $key => $value) {
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
        $message = !$error_flag ?
            ['success' => __('Settings updated successfully.')] :
            ['failed' => __('Settings updated failed.')];

        return redirect()->route('system.settings.index')->with($message);
    }
}
