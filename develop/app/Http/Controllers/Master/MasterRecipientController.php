<?php

/**
 * 納品先マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\RecipientEditRequest;
use App\Http\Requests\Master\RecipientSearchRequest;
use App\Models\Master\MasterRecipient;
use App\Services\Master\MasterRecipientService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 納品先マスター画面用コントローラー
 */
class MasterRecipientController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected MasterRecipientService $service;

    /**
     * MasterRecipientController constructor.
     *
     * @param MasterRecipientService $service
     */
    public function __construct(MasterRecipientService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param RecipientSearchRequest $request
     * @return View
     */
    public function index(RecipientSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        return view('master.recipients.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Excelダウンロード
     *
     * @param RecipientSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(RecipientSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        [$recipients, $filename, $headings, $filters] = $this->service->downloadExcel($search_condition_input_data);

        if ($recipients->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.recipients.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $recipients->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterRecipient();
        $target_record_data->id = MasterRecipient::max('id') + 1;

        SessionHelper::forgetSessionForMismatchURL('*master/recipients*', $this->refURLMasterKey());

        return view('master.recipients.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param RecipientEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(RecipientEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 一覧画面へリダイレクト
        return redirect(route('master.recipients.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterRecipient $recipient
     * @return View
     */
    public function edit(MasterRecipient $recipient): View
    {
        return view('master.recipients.create_edit', $this->service->edit($recipient));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param RecipientEditRequest $request
     * @param MasterRecipient $recipient
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(RecipientEditRequest $request, MasterRecipient $recipient): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $recipient);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterRecipient $recipient
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterRecipient $recipient): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($recipient);

        // 一覧画面へリダイレクト
        return redirect(route('master.recipients.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
