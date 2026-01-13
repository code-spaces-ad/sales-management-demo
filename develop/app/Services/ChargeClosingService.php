<?php

/**
 * 請求締処理ジョブ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services;

use App\Helpers\ClosingDateHelper;
use App\Helpers\TaxHelper;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDataDepositOrder;
use App\Models\Invoice\ChargeDataSalesOrder;
use App\Models\Master\MasterCustomer;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use App\Repositories\Sale\DepositRepository;
use App\Repositories\Sale\OrderRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;
use ReducedTaxFlagType;
use TaxType;

class ChargeClosingService
{
    /**
     * 得意先ID
     *
     * @var ?int
     */
    protected ?int $customer_id = null;

    /**
     * 締処理年月(YYYY-MM形式）
     *
     * @var ?string
     */
    protected ?string $charge_year_month = null;

    /**
     * 締処理区分
     *
     * @var ?int
     */
    protected ?int $closing_date = null;

    /**
     * 請求データID
     *
     * @var ?string
     */
    protected ?string $charge_data_id = null;

    /**
     * 部門ID
     *
     * @var ?int
     */
    protected ?int $department_id = null;

    /**
     * 事業所ID
     *
     * @var ?int
     */
    protected ?int $office_facilities_id = null;

    /**
     * ユーザーID
     *
     * @var ?int
     */
    protected ?int $user_id = null;

    /**
     * 売上管理用リポジトリ
     */
    protected OrderRepository $order_repository;

    /**
     * 入金伝票用リポジトリ
     */
    protected DepositRepository $deposit_repository;

    /**
     * リポジトリをインスタンス
     *
     * @param OrderRepository $order_repository
     * @param DepositRepository $deposit_repository
     */
    public function __construct(OrderRepository $order_repository, DepositRepository $deposit_repository)
    {
        $this->order_repository = $order_repository;
        $this->deposit_repository = $deposit_repository;
    }

    /**
     * 得意先IDのセット
     *
     * @param int $customer_id
     * @return ChargeClosingService
     */
    public function setCustomerId(int $customer_id): self
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * 仕入先IDのセット
     *
     * @param int $supplier_id
     * @return ChargeClosingService
     */
    public function setSupplierId(int $supplier_id): self
    {
        $this->customer_id = $supplier_id;

        return $this;
    }

    /**
     * 請求年月と締め区分のセット
     *
     * @param string $charge_year_month
     * @param int $closing_date
     * @return ChargeClosingService
     */
    public function setClosingDate(string $charge_year_month, int $closing_date): self
    {
        $this->charge_year_month = $charge_year_month;
        $this->closing_date = $closing_date;

        return $this;
    }

    /**
     * 請求データIDのセット
     *
     * @param string $charge_data_id
     * @return ChargeClosingService
     */
    public function setChargeDataId(string $charge_data_id): self
    {
        $this->charge_data_id = $charge_data_id;

        return $this;
    }

    /**
     * 部門IDと事業所IDのセット
     *
     * @param int $department_id
     * @param int $office_facilities_id
     * @return ChargeClosingService
     */
    public function setDepartmentAndOfficeFacilitiesId(int $department_id, int $office_facilities_id): self
    {
        $this->department_id = $department_id;
        $this->office_facilities_id = $office_facilities_id;

        return $this;
    }

    /**
     * ユーザーIDのセット
     *
     * @param int $user_id
     * @return ChargeClosingService
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * 締処理
     *
     * @param array $customer_ids
     * @param string $charge_date
     * @param int $closing_date
     * @param int $department_id
     * @param int $office_facilities_id
     * @return JsonResponse
     */
    public function closingProcess(array $customer_ids, string $charge_date, int $closing_date, int $department_id, int $office_facilities_id): JsonResponse
    {
        $success_count = 0;
        $skip_count = 0;
        $failed_count = 0;

        // 締処理
        foreach ($customer_ids as $customer_id) {
            try {
                $result = $this->setCustomerId($customer_id)
                    ->setClosingDate($charge_date, $closing_date)
                    ->setDepartmentAndOfficeFacilitiesId($department_id, $office_facilities_id)
                    ->closing();

                $content = json_decode($result->content(), true);
                if ($content['message'] === 'success') {
                    ++$success_count;
                }
                if ($content['message'] === 'skip') {
                    ++$skip_count;
                }
                if ($content['message'] === 'failed') {
                    ++$failed_count;
                }

            } catch (Exception $e) {
                Log::error($e->getMessage());
                ++$failed_count;
            }
        }

        $json = [
            'success' => $success_count,
            'skip' => $skip_count,
            'failed' => $failed_count,
        ];

        return response()->json($json);
    }

    /**
     * 締処理実行
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function closing(): JsonResponse
    {
        try {
            // 処理日
            $process_date = Carbon::now();
            // 締日年月と締日区分から締日範囲を取得
            [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate($this->charge_year_month, $this->closing_date);
            // 処理対象の伝票IDを取得
            $sales_order_ids = SalesOrder::getTargetClosingIds($this->customer_id, $start_date, $end_date, $this->department_id, $this->office_facilities_id);
            $deposit_order_ids = $this->deposit_repository->getTargetClosingIds($this->customer_id, $start_date, $end_date, $this->department_id, $this->office_facilities_id);
            //            $account_receivable = MasterCustomer::where('id', $this->customer_id)->value('start_account_receivable_balance'); TODO: 締め処理スキップ処理を入れる
            //
            //            // 処理対象の伝票が無い場合はスキップ
            //            if (count($sales_order_ids) === 0 && count($deposit_order_ids) === 0 && $account_receivable === 0) {
            //                return response()->json(['message' => 'skip']);
            //            }

            DB::beginTransaction();

            // 請求伝票作成
            $this->createChargeData($process_date, $start_date, $end_date,
                $sales_order_ids, $deposit_order_ids);
            // 締処理済み日付スタンプ
            $this->updateClosingAt($process_date, $sales_order_ids, $deposit_order_ids);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'failed'], 422);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * 締処理解除
     *
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function cancel(): JsonResponse
    {

        try {
            // 処理対象の伝票IDを取得
            $sales_order_ids = ChargeDataSalesOrder::query()->where('charge_data_id', $this->charge_data_id)
                ->pluck('sales_order_id')->toArray();
            $deposit_order_ids = ChargeDataDepositOrder::query()->where('charge_data_id', $this->charge_data_id)
                ->pluck('deposit_order_id')->toArray();

            DB::beginTransaction();

            // 伝票リレーション削除
            $this->deleteOrderRelation($this->charge_data_id);
            // 締処理日付をnullセット
            if (count($sales_order_ids) > 0) {
                // 売上伝票に締処理済み日付スタンプ
                SalesOrder::cancelClosingAt($sales_order_ids);
            }
            if (count($deposit_order_ids) > 0) {
                // 入金伝票に締処理済み日付スタンプ
                DepositOrder::cancelClosingAt($deposit_order_ids);
            }

            // 請求伝票削除
            $this->deleteChargeData($this->charge_data_id);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'failed'], 422);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * 請求伝票作成
     *
     * @param Carbon $process_date
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $sales_order_ids
     * @param array $deposit_order_ids
     * @return void
     *
     * @throws Exception
     */
    private function createChargeData(Carbon $process_date, Carbon $start_date, Carbon $end_date,
        array $sales_order_ids, array $deposit_order_ids): void
    {
        // 締日(YYYYMM形式）保存用
        $closing_ym = explode('-', $this->charge_year_month)[0] . explode('-', $this->charge_year_month)[1];

        Log::info('請求伝票作成:処理年月：' . $closing_ym . ' / 得意先ID：' . $this->customer_id . ' ' . MasterCustomer::find($this->customer_id)->name);

        // 請求データ登録
        $charge_data = new ChargeData(
            [
                'charge_start_date' => $start_date->format('Y-m-d'),
                'charge_end_date' => $end_date->format('Y-m-d'),
                'customer_id' => $this->customer_id,
                'closing_ym' => $closing_ym,
                'closing_date' => $this->closing_date,
                'department_id' => $this->department_id,
                'office_facilities_id' => $this->office_facilities_id,
                'closing_user_id' => $this->user_id ?? auth()->id(),
                'closing_at' => $process_date,
                'sales_order_count' => count($sales_order_ids),
                'deposit_order_count' => count($deposit_order_ids),
            ]
        );
        try {
            // 前回請求額の取得
            $charge_data->before_charge_total = MasterCustomer::query()->find($this->customer_id)->start_account_receivable_balance;   // 初回用
            if (ChargeData::query()->where('customer_id', $this->customer_id)->exists()) {
                // 締データがある場合は、取得する
                $charge_data->before_charge_total =
                    ChargeData::getBeforeChargeTotal($this->customer_id, $closing_ym, $this->closing_date);
            }

            // ☆ 請求単位/伝票単位/明細単位　が混在することはない想定だが、それぞれがあっても処理するように用意しておく
            // 対象ユーザ・締処理範囲内の伝票情報(請求単位)を取得する
            $charge_data = $this->getChargeRegistBillingData($charge_data, $start_date, $end_date, $sales_order_ids);
            // 対象ユーザ・締処理範囲内の伝票情報(伝票単位)を取得する
            $charge_data = $this->getChargeRegistOrderData($charge_data, $start_date, $end_date, $sales_order_ids);
            // 対象ユーザ・締処理範囲内の伝票情報(明細単位)を取得する
            $charge_data = $this->getChargeRegistDetailData($charge_data, $start_date, $end_date, $sales_order_ids);

            // 値引調整額
            $charge_data->discount_total = 0;

            // 対象ユーザ・締処理範囲内の入金情報情報を取得する
            $deposit_data = DepositOrder::getTargetClosingData($deposit_order_ids, $start_date, $end_date);
            // 今回入金額
            $charge_data->payment_total = $deposit_data->amount_deposit ?? 0;
            // 調整額
            $charge_data->adjust_amount = $deposit_data->amount_offset ?? 0;
            // 繰越残高
            $charge_data->carryover = $charge_data->before_charge_total - ($charge_data->payment_total + $charge_data->adjust_amount);
            // 今回請求額
            $charge_data->charge_total = $charge_data->carryover + $charge_data->sales_total
                + $charge_data->discount_total + $charge_data->sales_tax_total;

            // 入金予定日(起点の日付)
            $charge_data->planned_deposit_at = ClosingDateHelper::getPlannedDate($start_date, $this->customer_id);
            // 回収方法
            $charge_data->collection_method = MasterCustomer::query()->find($this->customer_id)->collection_method;

            $charge_data->save();

            // 伝票リレーション作成
            $charge_data->makeOrderRelation($sales_order_ids, $deposit_order_ids);

            // 得意先の請求残高更新
            $customer = MasterCustomer::query()->find($this->customer_id);
            $customer->billing_balance = $charge_data->charge_total;
            $customer->save();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 請求単位の伝票締処理
     *
     * @param ChargeData $charge_data
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $sales_order_ids
     * @return ChargeData
     */
    private function getChargeRegistBillingData(ChargeData $charge_data, Carbon $start_date, Carbon $end_date, array $sales_order_ids): ChargeData
    {
        // 請求単位のデータ取得
        $order_data = $this->order_repository->getTargetClosingBillingData($sales_order_ids, $start_date, $end_date);
        if (count($order_data) > 0) {
            Log::info('請求単位の伝票締処理 - 開始');
            // 請求データセット
            $charge_data = $this->setChargeData($charge_data, $order_data);
            Log::info('請求単位の伝票締処理 - 終了');
        }

        return $charge_data;
    }

    /**
     * 伝票単位の伝票締処理
     *
     * @param ChargeData $charge_data
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $sales_order_ids
     * @return ChargeData
     */
    private function getChargeRegistOrderData(ChargeData $charge_data, Carbon $start_date, Carbon $end_date, array $sales_order_ids): ChargeData
    {
        // 伝票求単位のデータ取得
        $order_data = $this->order_repository->getTargetClosingOrderData($sales_order_ids, $start_date, $end_date);
        if (count($order_data) > 0) {
            Log::info('伝票単位の伝票締処理 - 開始');
            // 請求データセット
            $charge_data = $this->setChargeData($charge_data, $order_data);
            Log::info('伝票単位の伝票締処理 - 終了');
        }

        return $charge_data;
    }

    /**
     * 明細単位の伝票締処理
     *
     * @param ChargeData $charge_data
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $sales_order_ids
     * @return ChargeData
     */
    private function getChargeRegistDetailData(ChargeData $charge_data, Carbon $start_date, Carbon $end_date, array $sales_order_ids): ChargeData
    {
        // 明細求単位のデータ取得
        $order_data = $this->order_repository->getTargetClosingDetailData($sales_order_ids, $start_date, $end_date);
        if (count($order_data) > 0) {
            Log::info('明細単位の伝票締処理 - 開始');
            // 請求データセット
            $charge_data = $this->setChargeData($charge_data, $order_data);
            Log::info('明細単位の伝票締処理 - 終了');
        }

        return $charge_data;
    }

    /**
     * 請求データセット
     *
     * @param ChargeData $charge_data
     * @param Collection $order_data
     * @return ChargeData
     */
    private function setChargeData(ChargeData $charge_data, Collection $order_data): ChargeData
    {
        // ログ表示用
        $process_log = [
            'log_sales_total' => 0,                 // 今回売上額
            'log_sales_total_normal_out' => 0,      //   通常税率_外税分
            'log_sales_total_reduced_out' => 0,     //   軽減税率_外税分
            'log_sales_total_normal_in' => 0,       //   通常税率_内税分
            'log_sales_total_reduced_in' => 0,      //   軽減税率_内税分
            'log_sales_tax_total' => 0,             // 消費税額
            'log_sales_tax_normal_out' => 0,        //   通常税率_外税分
            'log_sales_tax_reduced_out' => 0,       //   軽減税率_外税分
            'log_sales_tax_normal_in' => 0,         //   通常税率_内税分
            'log_sales_tax_reduced_in' => 0,        //   軽減税率_内税分
            'log_sales_total_free' => 0,            // 今回売上額_非課税分
        ];

        foreach ($order_data as $order) {
            // 今回売上額
            $charge_data->sales_total += $order->sales_total;
            $process_log['log_sales_total'] += $order->sales_total;

            // 非課税：今回売上額
            if ($order->consumption_tax_rate === 0) {
                $charge_data->sales_total_free += $order->sales_total;
                $process_log['log_sales_total_free'] += $order->sales_total;

                continue;   // 行単位でいずれかしかないのでcontinueする
            }
            // 通常税率：今回売上額と消費税額
            if ($order->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                // 外税
                if ($order->tax_type_id === TaxType::OUT_TAX) {
                    // 今回売上額
                    $charge_data->sales_total_normal_out += $order->sales_total;
                    $process_log['log_sales_total_normal_out'] += $order->sales_total;
                    // 消費税
                    $tax = TaxHelper::getTax($order->sales_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $charge_data->sales_tax_normal_out += $tax;
                    $process_log['log_sales_tax_normal_out'] += $tax;

                    // 外税時のみ加算
                    $charge_data->sales_tax_total += $tax;
                    $process_log['log_sales_tax_total'] += $tax;
                }
                // 内税
                if ($order->tax_type_id === TaxType::IN_TAX) {
                    // 今回売上額
                    $charge_data->sales_total_normal_in += $order->sales_total;
                    $process_log['log_sales_total_normal_in'] += $order->sales_total;
                    // 消費税
                    $tax = TaxHelper::getInTax($order->sales_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $charge_data->sales_tax_normal_in += $tax;
                    $process_log['log_sales_tax_normal_in'] += $tax;
                }

                continue;   // 行単位でいずれかしかないのでcontinueする
            }
            // 軽減税率：今回売上額と消費税額
            if ($order->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                // 外税
                if ($order->tax_type_id === TaxType::OUT_TAX) {
                    // 今回売上額
                    $charge_data->sales_total_reduced_out += $order->sales_total;
                    $process_log['log_sales_total_reduced_out'] += $order->sales_total;
                    // 消費税
                    $tax = TaxHelper::getTax($order->sales_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $charge_data->sales_tax_reduced_out += $tax;
                    $process_log['log_sales_tax_reduced_out'] += $tax;

                    // 外税時のみ加算
                    $charge_data->sales_tax_total += $tax;
                    $process_log['log_sales_tax_total'] += $tax;
                }
                // 内税
                if ($order->tax_type_id === TaxType::IN_TAX) {
                    // 今回売上額
                    $charge_data->sales_total_reduced_in += $order->sales_total;
                    $process_log['log_sales_total_reduced_in'] += $order->sales_total;
                    // 消費税
                    $tax = TaxHelper::getInTax($order->sales_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $charge_data->sales_tax_reduced_in += $tax;
                    $process_log['log_sales_tax_reduced_in'] += $tax;
                }
            }
        }

        if (count($order_data) > 0) {
            Log::info('  今回売上額:' . number_format($process_log['log_sales_total']));
            Log::info('    通常税率_外税分:' . number_format($process_log['log_sales_total_normal_out']));
            Log::info('    軽減税率_外税分:' . number_format($process_log['log_sales_total_reduced_out']));
            Log::info('    通常税率_内税分:' . number_format($process_log['log_sales_total_normal_in']));
            Log::info('    軽減税率_内税分:' . number_format($process_log['log_sales_total_reduced_in']));
            Log::info('  消費税額:' . number_format($process_log['log_sales_tax_total']));
            Log::info('    通常税率_外税分:' . number_format($process_log['log_sales_tax_normal_out']));
            Log::info('    軽減税率_外税分:' . number_format($process_log['log_sales_tax_reduced_out']));
            Log::info('    通常税率_内税分:' . number_format($process_log['log_sales_tax_normal_in']));
            Log::info('    軽減税率_内税分:' . number_format($process_log['log_sales_tax_reduced_in']));
            Log::info('  今回売上額_非課税分:' . number_format($process_log['log_sales_total_free']));
        }

        return $charge_data;
    }

    /**
     * 請求伝票削除
     *
     * @param int $charge_data_id
     *
     * @throws Exception
     */
    private function deleteChargeData(int $charge_data_id): void
    {
        Log::info('請求伝票解除:請求ID：' . $charge_data_id . ' / 得意先ID：' . ChargeData::find($charge_data_id)->customer_id . ' ' . MasterCustomer::find(ChargeData::find($charge_data_id)->customer_id)->name);

        ChargeData::query()->find($charge_data_id)->delete();
    }

    /**
     * 伝票リレーション削除
     *
     * @param int $charge_data_id
     */
    private function deleteOrderRelation(int $charge_data_id): void
    {
        // 請求データ　売上伝票リレーション登録
        ChargeDataSalesOrder::deleteOrderRelation($charge_data_id);
        // 入金データ　入金伝票リレーション登録
        ChargeDataDepositOrder::deleteOrderRelation($charge_data_id);
    }

    /**
     * 締処理済み日付スタンプ
     *
     * @param Carbon $process_date
     * @param array $sales_order_ids
     * @param array $deposit_order_ids
     */
    private function updateClosingAt(Carbon $process_date, array $sales_order_ids, array $deposit_order_ids): void
    {
        // 売上伝票に締処理済み日付スタンプ
        SalesOrder::updateClosingAt($sales_order_ids, $process_date);
        // 入金伝票に締処理済み日付スタンプ
        DepositOrder::updateClosingAt($deposit_order_ids, $process_date);
    }

    /**
     * 部門IDおよび事業所IDで請求データの絞込
     *
     * @param Collection $charge_data
     * @param int|null $department_id
     * @param int|null $office_facility_id
     * @return Collection
     */
    public function filterChargeDataByDepartmentAndOffice(Collection $charge_data, ?int $department_id, ?int $office_facility_id): Collection
    {
        return collect($charge_data)
            ->filter(function ($customer_data) use ($department_id, $office_facility_id) {
                return isset($customer_data->SalesOrder) && collect($customer_data->SalesOrder)->contains(function ($sales_order) use ($department_id, $office_facility_id) {
                    $is_department_match = !$department_id || $sales_order->department_id == $department_id;
                    $is_office_match = !$office_facility_id || $sales_order->office_facilities_id == $office_facility_id;

                    return $is_department_match && $is_office_match;
                });
            })
            ->map(function ($customer_data) use ($department_id, $office_facility_id) {
                $filtered = collect($customer_data->SalesOrder)->filter(function ($sales_order) use ($department_id, $office_facility_id) {
                    $is_department_match = !$department_id || $sales_order->department_id == $department_id;
                    $is_office_match = !$office_facility_id || $sales_order->office_facilities_id == $office_facility_id;

                    return $is_department_match && $is_office_match;
                })->values();

                $customer_data->SalesOrder = $filtered->isNotEmpty() ? [$filtered->first()] : [];

                return $customer_data;
            })
            ->values();
    }
}
