<?php

/**
 * 得意先別単価マスター用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Master;

use App\Consts\SessionConst;
use App\Enums\CollectionMonth;
use App\Enums\DepositMethodType;
use App\Enums\SalesInvoiceFormatType;
use App\Enums\SalesInvoicePrintingMethod;
use App\Enums\TaxCalcType;
use App\Enums\TransactionType;
use App\Helpers\ClosingDateHelper;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Http\Requests\Master\CustomerEditRequest;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterHonorificTitle;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSummaryGroup;
use App\Repositories\Master\CustomerRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 得意先別単価マスター用サービス
 */
class MasterCustomerService
{
    use SessionConst;

    protected CustomerRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param CustomerRepository $repository
     */
    public function __construct(CustomerRepository $repository)
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
                /** 請求締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'customers' => $this->repository->getSearchResultPagenate($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param MasterCustomer $target_data
     * @return array
     */
    public function create(MasterCustomer $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 敬称マスター */
                'honorific_titles' => MasterHonorificTitle::query()->get(),
                /** 税計算区分リスト */
                'tax_calc_types' => TaxCalcType::asSelectArray(),
                /** 端数処理リスト */
                'rounding_methods' => MasterRoundingMethod::query()->get(),
                /** 取引種別 */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 請求締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
                /** 回収月リスト */
                'collection_months' => CollectionMonth::asSelectArray(),
                /** 回収日リスト */
                'collection_day_list' => ClosingDateHelper::getCollectionDayList(),
                /** 回収方法リスト */
                'collection_methods' => DepositMethodType::asSelectArray(),
                /** 請求書書式リスト */
                'sales_invoice_format_types' => SalesInvoiceFormatType::asSelectArray(),
                /** 請求書印刷方式 */
                'sales_invoice_printing_methods' => SalesInvoicePrintingMethod::asSelectArray(),
                /** 請求先データ（得意先マスター） */
                'billing_customers' => $this->repository->getBillingCustomer(),
                /** 社員マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 集計グループマスター */
                'summary_group' => MasterSummaryGroup::query()->oldest('code')->get(),
                /** 支所データ（小売のみ） */
                'office_facilities' => MasterOfficeFacility::query()->where('department_id', 3)->get(),
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
     * @param CustomerEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(CustomerEditRequest $request): array
    {
        $error_flag = false;
        $customer_number = MasterCustomer::withTrashed()->max('id') + 1;
        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'billing_customer_id' => $customer_number,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 得意先を登録
            $customer = $this->repository->createCustomer($request->input());

            // リクエストキーのデフォルトセット
            $default_values = [
                'customer_id' => $customer->id,
                'honorific_title_id' => $request->honorific_title_id,
            ];

            // 得意先_敬称リレーション
            $this->repository->createCustomerHonorificTitle($default_values);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            $customer = MasterCustomer::query()
                ->firstOrNew([
                    'code' => $request->code,
                    'name' => $request->name,
                ]);
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $customer->code_zero_fill, $customer->name);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param MasterCustomer $target_data
     * @return array
     */
    public function edit(MasterCustomer $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 敬称マスター */
                'honorific_titles' => MasterHonorificTitle::query()->get(),
                /** 税計算区分リスト */
                'tax_calc_types' => TaxCalcType::asSelectArray(),
                /** 端数処理リスト */
                'rounding_methods' => MasterRoundingMethod::query()->get(),
                /** 取引種別 */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 請求締日 */
                'closing_date_list' => ClosingDateHelper::getClosingDateList(),
                /** 回収月リスト */
                'collection_months' => CollectionMonth::asSelectArray(),
                /** 回収日リスト */
                'collection_day_list' => ClosingDateHelper::getCollectionDayList(),
                /** 回収方法リスト */
                'collection_methods' => DepositMethodType::asSelectArray(),
                /** 請求書書式リスト */
                'sales_invoice_format_types' => SalesInvoiceFormatType::asSelectArray(),
                /** 請求書印刷方式 */
                'sales_invoice_printing_methods' => SalesInvoicePrintingMethod::asSelectArray(),
                /** 請求先データ（得意先マスター） */
                'billing_customers' => $this->repository->getBillingCustomer(),
                /** 社員マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 集計グループマスター */
                'summary_group' => MasterSummaryGroup::query()->oldest('code')->get(),
                /** 支所データ（小売のみ） */
                'office_facilities' => MasterOfficeFacility::query()->where('department_id', 3)->get(),
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
     * @param CustomerEditRequest $request
     * @param MasterCustomer $customer
     * @return array
     *
     * @throws Exception
     */
    public function update(CustomerEditRequest $request, MasterCustomer $customer): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'billing_customer_id' => $customer->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 得意先を登録
            $customer = $this->repository->updateCustomer($customer, $request->input());
            // リクエストキーのデフォルトセット
            $default_values = [
                'customer_id' => $customer->id,
                'honorific_title_id' => $request->honorific_title_id,
            ];

            // 得意先_敬称リレーション
            $this->repository->updateCustomerHonorificTitle($customer, $default_values);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $customer->code_zero_fill, $customer->name);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param MasterCustomer $customer
     * @return array
     *
     * @throws Exception
     */
    public function destroy(MasterCustomer $customer): array
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($customer->use_master) {
            $error_flag = true;
            $message = MessageHelper::getMasterDestroyMessage($error_flag, $customer->code_zero_fill, $customer->name);

            return [$error_flag, $message];
        }

        DB::beginTransaction();

        try {

            // 支所・納品先を削除
            $customer->mBranches->each(function ($branch_record) {
                $branch_record->mRecipients->each(function ($recipient_record) {
                    $recipient_record->delete(); // 論理削除(納品先)
                });
                $branch_record->delete(); // 論理削除(支所)
            });

            // 得意先マスター削除
            $this->repository->deleteCustomer($customer);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $customer->code_zero_fill, $customer->name);

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
        $filename = config('consts.excel.filename.customers');
        $headings = [
            'コード',
            '得意先名',
            '得意先名かな',
            '敬称',
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

        $customers = $this->repository->getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($customers) {
                return $customers->code_zerofill;
            },
            /** 得意先名 */
            function ($customers) {
                return $customers->name;
            },
            /** 名前かな */
            function ($customers) {
                return $customers->name_kana;
            },
            /** 敬称 */
            function ($customers) {
                return $customers->customer_honorific_title_name;
            },
            /** 郵便番号1 */
            function ($customers) {
                return $customers->postal_code1;
            },
            /** 郵便番号2 */
            function ($customers) {
                return $customers->postal_code2;
            },
            /** 住所1 */
            function ($customers) {
                return $customers->address1;
            },
            /** 住所2 */
            function ($customers) {
                return $customers->address2;
            },
            /** 電話番号 */
            function ($customers) {
                return $customers->tel_number;
            },
            /** FAX番号 */
            function ($customers) {
                return $customers->fax_number;
            },
            /** メールアドレス */
            function ($customers) {
                return $customers->email;
            },
            /** 備考 */
            function ($customers) {
                return $customers->note;
            },
            /** 作成日時 */
            function ($customers) {
                return Carbon::parse($customers->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($customers) {
                return Carbon::parse($customers->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        return [$customers, $filename, $headings, $filters];
    }
}
