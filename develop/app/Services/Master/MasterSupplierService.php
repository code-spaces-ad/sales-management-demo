<?php

/**
 * 仕入先マスター用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Master;

use App\Consts\SessionConst;
use App\Enums\TaxCalcType;
use App\Helpers\ClosingDateHelper;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Http\Requests\Master\SupplierEditRequest;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSupplier;
use App\Repositories\Master\SupplierRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 仕入先マスター用サービス
 */
class MasterSupplierService
{
    use SessionConst;

    protected SupplierRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param SupplierRepository $repository
     */
    public function __construct(SupplierRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        return [
            /** 検索項目 */
            'search_items' => [
                /** 仕入締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'suppliers' => $this->repository->getSearchResultPagenate($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param MasterSupplier $target_data
     * @return array
     */
    public function create(MasterSupplier $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 税計算区分リスト */
                'tax_calc_types' => TaxCalcType::asSelectArray(),
                /** 端数処理リスト */
                'rounding_methods' => MasterRoundingMethod::query()->get(),
                /** 仕入締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 新規登録処理
     *
     * @param SupplierEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(SupplierEditRequest $request): array
    {
        $error_flag = false;
        $supplier_id = MasterBranch::withTrashed()->max('id') + 1;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'supplier_id' => $supplier_id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 仕入先を登録
            $supplier = $this->repository->createSupplier($request->input());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            $supplier = MasterSupplier::query()
                ->firstOrNew([
                    'code' => $request->code,
                    'name' => $request->name,
                ]);
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $supplier->code_zero_fill, $supplier->name);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param MasterSupplier $target_data
     * @return array
     */
    public function edit(MasterSupplier $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 税計算区分リスト */
                'tax_calc_types' => TaxCalcType::asSelectArray(),
                /** 端数処理リスト */
                'rounding_methods' => MasterRoundingMethod::query()->get(),
                /** 仕入締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 更新処理
     *
     * @param SupplierEditRequest $request
     * @param MasterSupplier $supplier
     * @return array
     *
     * @throws Exception
     */
    public function update(SupplierEditRequest $request, MasterSupplier $supplier): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 仕入先を更新
            $supplier = $this->repository->updateSupplier($supplier, $request->input());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $supplier->code_zero_fill, $supplier->name);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param MasterSupplier $supplier
     * @return array
     *
     * @throws Exception
     */
    public function destroy(MasterSupplier $supplier): array
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($supplier->use_master) {
            $error_flag = true;
            $message = MessageHelper::getMasterDestroyMessage(true, $supplier->code_zero_fill, $supplier->name);

            return [$error_flag, $message];
        }

        DB::beginTransaction();

        try {
            // 仕入先マスター削除
            $this->repository->deleteSupplier($supplier);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $supplier->code_zero_fill, $supplier->name);

        return [$error_flag, $message];
    }

    /**
     * Excelダウンロード
     *
     * @param $search_condition_input_data
     * @return array
     */
    public function downloadExcel($search_condition_input_data): array
    {
        $filename = config('consts.excel.filename.suppliers');
        $headings = [
            'コード',
            '仕入先名',
            '仕入先名かな',
            '郵便番号1',
            '郵便番号2',
            '住所1',
            '住所2',
            '電話番号',
            'FAX番号',
            'メールアドレス',
            '備考',
            '作成日時',
            '更新日時',
        ];

        $suppliers = $this->repository->getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($suppliers) {
                return $suppliers->code_zerofill;
            },
            /** 仕入先名 */
            function ($suppliers) {
                return $suppliers->name;
            },
            /** 仕入先名かな */
            function ($suppliers) {
                return $suppliers->name_kana;
            },
            /** 郵便番号1 */
            function ($suppliers) {
                return $suppliers->postal_code1;
            },
            /** 郵便番号2 */
            function ($suppliers) {
                return $suppliers->postal_code2;
            },
            /** 住所1 */
            function ($suppliers) {
                return $suppliers->address1;
            },
            /** 住所2 */
            function ($suppliers) {
                return $suppliers->address2;
            },
            /** 電話番号 */
            function ($suppliers) {
                return $suppliers->tel_number;
            },
            /** FAX番号 */
            function ($suppliers) {
                return $suppliers->fax_number;
            },
            /** メールアドレス */
            function ($suppliers) {
                return $suppliers->email;
            },
            /** 備考 */
            function ($suppliers) {
                return $suppliers->note;
            },
            /** 作成日時 */
            function ($suppliers) {
                return Carbon::parse($suppliers->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($suppliers) {
                return Carbon::parse($suppliers->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        return [$suppliers, $filename, $headings, $filters];
    }
}
