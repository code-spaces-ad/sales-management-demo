<?php

/**
 * 商品マスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Consts\DB\Master\MasterUnitConst;
use App\Enums\ReducedTaxFlagType;
use App\Enums\TaxType;
use App\Helpers\CollectionHelper;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\ProductEditRequest;
use App\Http\Requests\Master\ProductSearchRequest;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification1;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterConsumptionTax;
use App\Models\Master\MasterKind;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterProductUnit;
use App\Models\Master\MasterSubCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 商品マスター画面用コントローラー
 */
class MasterProductController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param ProductSearchRequest $request
     * @return View
     */
    public function index(ProductSearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** 税率リスト */
                'consumption_taxes' => MasterConsumptionTax::getList(),
                /** 税区分リスト */
                'tax_types' => TaxType::asSelectArray(),
                /** 税区分リスト */
                'reduced_tax_flag_types' => ReducedTaxFlagType::asSelectArray(),
                /** カテゴリーリスト */
                'categories' => MasterCategory::query()->oldest('code')->get(),
                /** サブカテゴリーリスト */
                'sub_categories' => MasterSubCategory::query()->oldest('code')->get(),
                /** 種別リスト */
                'kinds' => MasterKind::query()->oldest('code')->get(),
                /** 分類１リスト */
                'classifications1' => MasterClassification1::query()->oldest('code')->get(),
                /** 分類２リスト */
                'classifications2' => MasterClassification2::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'products' => MasterProduct::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.products.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterProduct();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/products*', $this->refURLMasterKey());

        return view('master.products.create_edit', $this->sendDataProduct($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(ProductEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $product = new MasterProduct();

        try {
            // 商品マスター
            $product->code = $request->code;
            $product->name = $request->name;
            $product->name_kana = $request->name_kana;
            $product->customer_product_code = $request->customer_product_code;
            $product->jan_code = $request->jan_code;
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->unit_price = $request->unit_price ?? 0;
            $product->unit_price_decimal_digit = $request->unit_price_decimal_digit;
            $product->quantity_decimal_digit = $request->quantity_decimal_digit;

            $product->tax_type_id = $request->tax_type_id;
            $product->reduced_tax_flag = ReducedTaxFlagType::NOT_REDUCED_TAX;
            if ($request->reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX) {
                $product->reduced_tax_flag = ReducedTaxFlagType::REDUCED_TAX;
            }

            $product->quantity_rounding_method_id = $request->quantity_rounding_method_id;
            $product->amount_rounding_method_id = $request->amount_rounding_method_id;
            $product->purchase_unit_price = $request->purchase_unit_price ?? 0;
            $product->accounting_code_id = $request->accounting_code_id;
            $product->supplier_id = $request->supplier_id;
            $product->note = $request->note;
            $product->specification = $request->specification;
            $product->kind_id = $request->kind_id;
            $product->section_id = $request->section_id;
            $product->purchase_unit_weight = $request->purchase_unit_weight ?? 0;
            $product->classification1_id = $request->classification1_id;
            $product->classification2_id = $request->classification2_id;
            $product->product_status = $request->product_status;
            $product->rack_address = $request->rack_address;

            $product->save();

            // 商品_単位リレーション
            $product_unit = new MasterProductUnit();
            $product_unit->product_id = $product->id;
            // 固定値 1：pcs をセット
            $product_unit->unit_id = MasterUnitConst::UNIT_ID_FIXED_VALUE;
            $product_unit->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $product->code_zero_fill, $product->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.products.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterProduct $product
     * @return View
     */
    public function edit(MasterProduct $product): View
    {
        return view('master.products.create_edit', $this->sendDataProduct($product));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductEditRequest $request
     * @param MasterProduct $product
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(ProductEditRequest $request, MasterProduct $product): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 商品マスター
            $product->code = $request->code;
            $product->name = $request->name;
            $product->name_kana = $request->name_kana;
            $product->customer_product_code = $request->customer_product_code;
            $product->jan_code = $request->jan_code;
            $product->category_id = $request->category_id;
            $product->sub_category_id = $request->sub_category_id;
            $product->unit_price = $request->unit_price ?? 0;
            $product->unit_price_decimal_digit = $request->unit_price_decimal_digit;
            $product->quantity_decimal_digit = $request->quantity_decimal_digit;

            $product->tax_type_id = $request->tax_type_id;
            $product->reduced_tax_flag = ReducedTaxFlagType::NOT_REDUCED_TAX;
            if ($request->reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX) {
                $product->reduced_tax_flag = ReducedTaxFlagType::REDUCED_TAX;
            }

            $product->quantity_rounding_method_id = $request->quantity_rounding_method_id;
            $product->amount_rounding_method_id = $request->amount_rounding_method_id;
            $product->purchase_unit_price = $request->purchase_unit_price ?? 0;
            $product->accounting_code_id = $request->accounting_code_id;
            $product->supplier_id = $request->supplier_id;
            $product->note = $request->note;
            $product->specification = $request->specification;
            $product->kind_id = $request->kind_id;
            $product->section_id = $request->section_id;
            $product->purchase_unit_weight = $request->purchase_unit_weight ?? 0;
            $product->classification1_id = $request->classification1_id;
            $product->classification2_id = $request->classification2_id;
            $product->product_status = $request->product_status;
            $product->rack_address = $request->rack_address;

            $product->save();

            // 商品_単位リレーション
            if (is_null($product->mProductUnit)) {
                // 単位が新規登録の場合
                $product_unit = new MasterProductUnit();
                $product_unit->product_id = $product->id;
                // 固定値 1：pcs をセット
                $product_unit->unit_id = MasterUnitConst::UNIT_ID_FIXED_VALUE;
                $product_unit->save();
            } else {
                // 単位が登録済みの場合
                $product->mProductUnit->unit_id = MasterUnitConst::UNIT_ID_FIXED_VALUE;
                $product->mProductUnit->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $product->code_zero_fill, $product->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterProduct $product
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterProduct $product): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($product->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $product->code_zero_fill, $product->name);

            return redirect(route('master.products.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            if ($product->mProductUnit) {
                // 商品_単位リレーション
                $product->mProductUnit->delete();   // 論理削除
            }
            // 商品マスター
            $product->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $product->code_zero_fill, $product->name);

        return redirect(route('master.products.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param ProductSearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(ProductSearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.products');
        $headings = [
            '商品CD',
            '商品名ｶﾅ',
            '商品名',
            '商品名(略)',
            '商品名入力',
            '相手先商品番号',
            'JANｺｰﾄﾞ',
            '大分類',
            '中分類',
            '課税区分',
            '在庫管理',
            '入荷残管理',
            '売上残管理',
            '入数',
            '単位',
            '標準在庫数',
            '標準原価',
            '定価',
            '登録日',
            '最終更新日',
            '経理ｺｰﾄﾞ',
            '科目名',
            '仕入先ｺｰﾄﾞ',
            '仕入先名',
            '備考',
            '仕様',
            '種別コード',
            '種別名',
            '管理部署コード',
            '管理部署名',
            '棚番',
            '品　名',
            '軽減税率区分',
            '平均売値',
            '単重',
            '分類１CD',
            '分類１名',
            '分類２CD',
            '分類２名',
            '分類３CD',
            '分類３名',
            '分類４CD',
            '分類４名',
        ];

        $products = MasterProduct::getSearchResult($search_condition_input_data);
        $filters = [
            CollectionHelper::getData($products, 'code'),
            CollectionHelper::getData($products, 'name_kana'),
            CollectionHelper::getData($products, 'name'),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, 'customer_product_code'),
            CollectionHelper::getData($products, 'jan_code'),
            CollectionHelper::getData($products, ['category_code', 'category_name']),
            CollectionHelper::getData($products, ['sub_category_code', 'sub_category_name']),
            CollectionHelper::getData($products, 'tax_type_id',
                fn ($flag) => $flag . ' ' . (TaxType::asSelectArray()[$flag] ?? '')),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, 'purchase_unit_price'),
            CollectionHelper::getData($products, 'unit_price_floor'),
            CollectionHelper::getData($products, 'created_at', fn ($row) => $row ? Carbon::parse($row)->format('Y/m/d') : ''),
            CollectionHelper::getData($products, 'updated_at', fn ($row) => $row ? Carbon::parse($row)->format('Y/m/d') : ''),
            CollectionHelper::getData($products, 'accounting_code_code'),
            CollectionHelper::getData($products, 'accounting_code_name'),
            CollectionHelper::getData($products, 'supplier_code'),
            CollectionHelper::getData($products, 'supplier_name'),
            CollectionHelper::getData($products, 'note'),
            CollectionHelper::getData($products, 'specification'),
            CollectionHelper::getData($products, 'kind_code'),
            CollectionHelper::getData($products, 'kind_name'),
            CollectionHelper::getData($products, 'section_code'),
            CollectionHelper::getData($products, 'section_name'),
            CollectionHelper::getData($products, 'rack_address'),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, 'reduced_tax_flag',
                fn ($flag) => $flag . ' ' . (ReducedTaxFlagType::asSelectArray()[$flag] ?? '')),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, 'purchase_unit_weight'),
            CollectionHelper::getData($products, 'classification1_code'),
            CollectionHelper::getData($products, 'classification1_name'),
            CollectionHelper::getData($products, 'classification2_code'),
            CollectionHelper::getData($products, 'classification2_name'),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
            CollectionHelper::getData($products, [], fn () => ''),
        ];

        if ($products->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.products.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $products->exportExcel($filename, $headings, $filters, 2);
    }
}
