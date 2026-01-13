<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Http\Requests\System\SettingsRequest;
use App\Services\System\SettingsService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * 設定用コントローラー
 */
class SettingsController extends Controller
{
    protected SettingsService $service;

    /**
     * SettingsController constructor.
     */
    public function __construct(SettingsService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application Settings.
     */
    public function index(): View
    {
        return view('system.settings.index', $this->service->index());
    }

    /**
     * 設定更新処理
     *
     * @throws Exception
     */
    public function store(SettingsRequest $request): RedirectResponse
    {
        return $this->service->store($request);
    }
}
