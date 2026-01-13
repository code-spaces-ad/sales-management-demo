<?php

/**
 * 得意先別単価マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\CustomerPriceEditRequest;
use App\Http\Requests\Master\CustomerPriceSearchRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Master\MasterProduct;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 顧客別単価マスター画面用コントローラー
 */
class MasterCustomerPriceController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterCustomerPriceController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param CustomerPriceSearchRequest $request
     * @return View
     */
    public function index(CustomerPriceSearchRequest $request): View
    {

        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('id')->get(),
                'products' => MasterProduct::query()->oldest('id')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'customer_price' => MasterCustomerPrice::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.customer_price.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterCustomerPrice();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/customer_price*', $this->refURLMasterKey());

        return view('master.customer_price.create_edit', $this->sendDataCustomerPrice($target_record_data));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CustomerPriceEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(CustomerPriceEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $customer_price = new MasterCustomerPrice();

        try {
            // 得意先別単価マスター
            $customer_price->code = $request->code;
            $customer_price->customer_id = $request->customer_id;
            $customer_price->product_id = $request->product_id;
            $customer_price->unit_price = $request->unit_price;
            $customer_price->tax_included = $request->tax_included;
            $customer_price->reduced_tax_included = $request->reduced_tax_included;
            $customer_price->sales_unit_price = $request->sales_unit_price;
            $customer_price->sales_date = $request->sales_date;
            $customer_price->note = $request->note;

            $customer_price->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        $message = MessageHelper::getMasterStoreMessage($error_flag, $customer_price->code, '');

        return redirect(route('master.customer_price.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterCustomerPrice $customer_price
     * @return View
     */
    public function edit(MasterCustomerPrice $customer_price): View
    {
        return view('master.customer_price.create_edit', $this->sendDataCustomerPrice($customer_price));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CustomerPriceEditRequest $request
     * @param MasterCustomerPrice $customer_price
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(CustomerPriceEditRequest $request, MasterCustomerPrice $customer_price): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 得意先別単価マスター
            $customer_price->code = $request->code;
            $customer_price->customer_id = $request->customer_id;
            $customer_price->product_id = $request->product_id;
            $customer_price->unit_price = $request->unit_price;
            $customer_price->tax_included = $request->tax_included;
            $customer_price->reduced_tax_included = $request->reduced_tax_included;
            $customer_price->sales_unit_price = $request->sales_unit_price;
            $customer_price->sales_date = $request->sales_date;
            $customer_price->note = $request->note;

            $customer_price->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.update_failed'));
        }
        $name = optional($customer_price->mCustomer)->name;

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $customer_price->code, $name ?? '');

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterCustomerPrice $customer_price
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterCustomerPrice $customer_price): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 得意先別単価マスター
            $customer_price->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.destroy_failed'));
        }

        $name = optional($customer_price->mCustomer)->name;

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $customer_price->code, $name);

        return redirect(route('master.customer_price.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param CustomerPriceSearchRequest $request
     * @return StreamedResponse
     *
     * @throws Exception
     */
    public function downloadExcel(CustomerPriceSearchRequest $request): RedirectResponse|StreamedResponse
    {

        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.customer_price');
        $headings = [
            'コード',
            '顧客ID',
            '顧客名',
            '商品ID',
            '商品名',
            '最終売上日',
            '最終売上単価',
            '通常税率_税込単価',
            '軽減税率_税込単価',
            '税抜単価',
            '備考',
            '作成日時',
            '更新日時',
            '削除日時',

        ];

        $customer_price = MasterCustomerPrice::getSearchResult($search_condition_input_data);
        $rows = $customer_price->map(function ($row) {
            return [
                $row->code,
                $row->customer_id,
                optional($row->mCustomer)->name,
                $row->product_id,
                optional($row->mProduct)->name,
                optional($row->sales_date)?->format('Y/m/d'),
                $row->sales_unit_price,
                $row->tax_included,
                $row->reduced_tax_included,
                $row->unit_price,
                $row->note,
                optional($row->created_at)?->format('Y/m/d'),
                optional($row->updated_at)?->format('Y/m/d'),
                optional($row->deleted_at)?->format('Y/m/d'),
            ];
        });

        if ($rows->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.customer_price.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $rows->values()->exportExcel($filename, $headings);

    }
}
