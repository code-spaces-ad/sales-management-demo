<?php

/**
 * 支所マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\BranchEditRequest;
use App\Http\Requests\Master\BranchSearchRequest;
use App\Models\Master\MasterBranch;
use App\Services\Master\MasterBranchService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 支所マスター画面用コントローラー
 */
class MasterBranchController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected MasterBranchService $service;

    /**
     * MasterBranchController constructor.
     *
     * @param MasterBranchService $service
     */
    public function __construct(MasterBranchService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param BranchSearchRequest $request
     * @return View
     */
    public function index(BranchSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        return view('master.branches.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Excelダウンロード
     *
     * @param BranchSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(BranchSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        [$branches, $filename, $headings, $filters] = $this->service->downloadExcel($search_condition_input_data);

        if ($branches->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.branches.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $branches->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterBranch();
        $target_record_data->id = MasterBranch::max('id') + 1;

        SessionHelper::forgetSessionForMismatchURL('*master/branches*', $this->refURLMasterKey());

        return view('master.branches.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BranchEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(BranchEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 一覧画面へリダイレクト
        return redirect(route('master.branches.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterBranch $branch
     * @return View
     */
    public function edit(MasterBranch $branch): View
    {
        return view('master.branches.create_edit', $this->service->edit($branch));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param BranchEditRequest $request
     * @param MasterBranch $branch
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(BranchEditRequest $request, MasterBranch $branch): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $branch);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterBranch $branch
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterBranch $branch): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($branch);

        // 一覧画面へリダイレクト
        return redirect(route('master.branches.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
