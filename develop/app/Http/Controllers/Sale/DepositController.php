<?php

/**
 * 入金伝票用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Sale;

use App\Helpers\SearchConditionSetHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Sale\DepositEditRequest;
use App\Http\Requests\Sale\DepositSearchRequest;
use App\Models\Sale\DepositOrder;
use App\Services\Sale\DepositService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * 入金伝票用コントローラー
 */
class DepositController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    protected DepositService $service;

    /**
     * DepositController constructor.
     */
    public function __construct(DepositService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Show the application dashboard.
     *
     * @param DepositSearchRequest $request
     * @return View
     */
    public function index(DepositSearchRequest $request): View
    {
        // リクエストパラメータが空の場合、デフォルトセット
        $search_condition_input_data = SearchConditionSetHelper::setSearchConditionInput($request->query(), $request->defaults());
        Session::put($this->refURLCommonKey(), URL::full());

        return view('sale.deposits.index', $this->service->index($search_condition_input_data));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new DepositOrder();
        $select_order_id = Session::get('select_order_id');
        if ($select_order_id != '') {
            // 選択した伝票をコピー
            $target_record_data = DepositOrder::query()->find($select_order_id);
            Session::put('select_order_id', '');
        }

        SessionHelper::forgetSessionForMismatchURL('*sale/deposits*', $this->refURLCommonKey());

        return view('sale.deposits.create_edit', $this->service->create($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DepositEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(DepositEditRequest $request): RedirectResponse
    {
        [$error_flag, $message] = $this->service->store($request);

        // 新規登録画面へリダイレクト
        return redirect(route('sale.deposits.create'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * 伝票コピー（Createページにリダイレクト）
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function copyOrder(Request $request): RedirectResponse
    {
        $input_data = $request->validated();
        // 選択した伝票IDはセッションで渡す
        Session::put('select_order_id', $input_data['select_order_id']);

        // 新規登録画面へリダイレクト
        return redirect(route('sale.deposits.create'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param DepositOrder $deposit
     * @return View
     */
    public function edit(DepositOrder $deposit): View
    {
        SessionHelper::forgetSessionForMismatchURL('*sale/*', $this->refURLCommonKey(), '*charge_detail/*');

        return view('sale.deposits.create_edit', $this->service->edit($deposit));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DepositEditRequest $request
     * @param DepositOrder $deposit
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(DepositEditRequest $request, DepositOrder $deposit): RedirectResponse
    {
        [$error_flag, $message] = $this->service->update($request, $deposit);

        // 入金一覧画面へリダイレクト
        return redirect(Session::get('reference_url.common_key'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DepositOrder $deposit
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(DepositOrder $deposit): RedirectResponse
    {
        [$error_flag, $message] = $this->service->destroy($deposit);

        // 入金一覧画面へリダイレクト
        return redirect(route('sale.deposits.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
