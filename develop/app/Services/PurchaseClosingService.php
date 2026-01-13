<?php

/**
 * 仕入締処理ジョブ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services;

use App\Helpers\ClosingDateHelper;
use App\Helpers\TaxHelper;
use App\Models\Master\MasterSupplier;
use App\Models\PurchaseInvoice\PurchaseClosing;
use App\Models\PurchaseInvoice\PurchaseClosingPayment;
use App\Models\PurchaseInvoice\PurchaseClosingPurchaseOrder;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;
use ReducedTaxFlagType;
use TaxType;

class PurchaseClosingService
{
    /**
     * 仕入先ID
     *
     * @var int
     */
    protected $supplier_id = null;

    /**
     * 締処理年月(YYYY-MM形式）
     *
     * @var string
     */
    protected $charge_year_month = null;

    /**
     * 締処理区分
     *
     * @var int
     */
    protected $closing_date = null;

    /**
     * 仕入データID
     */
    protected $purchase_data_id = null;

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
     * 仕入先IDのセット
     *
     * @param int $supplier_id
     * @return PurchaseClosingService
     */
    public function setSupplierId(int $supplier_id): self
    {
        $this->supplier_id = $supplier_id;

        return $this;
    }

    /**
     * 仕入年月と締め区分のセット
     *
     * @param string $charge_year_month
     * @param int $closing_date
     * @return PurchaseClosingService
     */
    public function setClosingDate(string $charge_year_month, int $closing_date): self
    {
        $this->charge_year_month = $charge_year_month;
        $this->closing_date = $closing_date;

        return $this;
    }

    /**
     * 仕入データIDのセット
     *
     * @param string $purchase_data_id
     * @return PurchaseClosingService
     */
    public function setPurchaseDataId(string $purchase_data_id): self
    {
        $this->purchase_data_id = $purchase_data_id;

        return $this;
    }

    /**
     * 部門IDと事業所IDのセット
     *
     * @param int $department_id
     * @param int $office_facilities_id
     * @return PurchaseClosingService
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
     * @return PurchaseClosingService
     */
    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
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
            [$start_date, $end_date] = ClosingDateHelper::getChargeCloseTermDate(
                $this->charge_year_month, $this->closing_date
            );
            // 処理対象の伝票IDを取得
            $purchase_order_ids = PurchaseOrder::getTargetClosingIds($this->supplier_id, $start_date, $end_date, $this->department_id, $this->office_facilities_id);
            $payment_ids = Payment::getTargetClosingIds($this->supplier_id, $start_date, $end_date, $this->department_id, $this->office_facilities_id);

            //            // 処理対象の伝票が無い場合はスキップ TODO: 締め処理スキップ処理を入れる
            //            if (count($purchase_order_ids) === 0 && count($payment_ids) === 0) {
            //                return response()->json(['message' => 'skip']);
            //            }

            DB::beginTransaction();
            // 仕入伝票作成
            $purchase_data_id = $this->createPurchaseData($process_date, $start_date, $end_date,
                $purchase_order_ids, $payment_ids);
            // 伝票リレーション作成
            $this->makeOrderRelation($purchase_data_id, $purchase_order_ids, $payment_ids);
            // 締処理済み日付スタンプ
            $this->updateClosingAt($process_date, $purchase_order_ids, $payment_ids);

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
            $purchase_order_ids = PurchaseClosingPurchaseOrder::where('purchase_closing_id', $this->purchase_data_id)
                ->pluck('purchase_order_id')->toArray();
            $payment_ids = PurchaseClosingPayment::where('purchase_closing_id', $this->purchase_data_id)
                ->pluck('payment_id')->toArray();

            DB::beginTransaction();

            // 伝票リレーション削除
            $this->deletePurchaseRelation($this->purchase_data_id);
            // 締処理日付をnullセット
            if (count($purchase_order_ids) > 0) {
                // 仕入伝票に締処理済み日付スタンプ
                PurchaseOrder::cancelClosingAt($purchase_order_ids);
            }
            if (count($payment_ids) > 0) {
                // 支払伝票に締処理済み日付スタンプ
                Payment::cancelClosingAt($payment_ids);
            }

            // 請求伝票削除
            $this->deletePurchaseData($this->purchase_data_id);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'failed'], 422);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * 仕入締伝票作成
     *
     * @param Carbon $process_date
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $purchase_order_ids
     * @param array $deposit_order_ids
     * @return int
     *
     * @throws Exception
     */
    private function createPurchaseData(Carbon $process_date, Carbon $start_date, Carbon $end_date,
        array $purchase_order_ids, array $deposit_order_ids): int
    {
        // 締日(YYYYMM形式）保存用
        $closing_ym = explode('-', $this->charge_year_month)[0] . explode('-', $this->charge_year_month)[1];

        Log::info('仕入伝票作成:処理年月：' . $closing_ym . ' / 仕入先ID：' . $this->supplier_id . ' ' . MasterSupplier::find($this->supplier_id)->name);

        // 仕入データ登録
        $purchase_data = new PurchaseClosing(
            [
                'purchase_closing_start_date' => $start_date,
                'purchase_closing_end_date' => $end_date,
                'supplier_id' => $this->supplier_id,
                'closing_ym' => $closing_ym,
                'closing_date' => $this->closing_date,
                'department_id' => $this->department_id,
                'office_facilities_id' => $this->office_facilities_id,
                'closing_user_id' => $this->user_id ?? auth()->id(),
                'closing_at' => $process_date,
                'purchase_order_count' => count($purchase_order_ids),
                'payment_count' => count($deposit_order_ids),
            ]
        );

        try {
            // 前回仕入額の取得
            $purchase_data->before_purchase_total = MasterSupplier::find($this->supplier_id)->start_account_receivable_balance;   // 初回用
            if (PurchaseClosing::where('supplier_id', $this->supplier_id)->exists()) {
                // 締データがある場合は、取得する
                $purchase_data->before_purchase_total =
                    PurchaseClosing::getBeforePurchaseTotal($this->supplier_id, $closing_ym, $this->closing_date);
            }
            // 仕入は「明細単位」でしか登録していないので、全部取得して処理
            $purchase_data = $this->getChargeRegistData($purchase_data, $start_date, $end_date, $purchase_order_ids);

            // 値引調整額
            $purchase_data->discount_total = 0;

            // 対象ユーザ・締処理範囲内の支払情報情報を取得する
            $payment_data = Payment::getTargetClosingData($deposit_order_ids, $start_date, $end_date);
            // 今回入金額
            $purchase_data->payment_total = $payment_data->amount_deposit ?? 0;
            // 調整額
            $purchase_data->adjust_amount = $payment_data->amount_offset ?? 0;
            // 繰越残高
            $purchase_data->carryover = $purchase_data->before_purchase_total - ($purchase_data->payment_total + $purchase_data->adjust_amount);
            // 今回支払額
            $purchase_data->purchase_closing_total = $purchase_data->carryover + $purchase_data->purchase_total
                + $purchase_data->discount_total + $purchase_data->purchase_tax_total;

            $purchase_data->save();

            // 仕入先の仕入残高更新
            $supplier = MasterSupplier::find($this->supplier_id);
            $supplier->billing_balance = $purchase_data->purchase_closing_total;
            $supplier->save();

        } catch (Exception $e) {
            Log::error($e->getMessage());
            throw new Exception($e->getMessage());
        }

        return $purchase_data->id;
    }

    /**
     * 仕入単位の伝票締処理
     *
     * @param PurchaseClosing $purchase_data
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @param array $purchase_order_ids
     * @return PurchaseClosing
     */
    private function getChargeRegistData(PurchaseClosing $purchase_data, Carbon $start_date, Carbon $end_date, array $purchase_order_ids): PurchaseClosing
    {
        // 仕入単位のデータ取得
        $order_data = PurchaseOrder::getTargetClosingData($purchase_order_ids, $start_date, $end_date);
        if (count($order_data) > 0) {
            Log::info('仕入単位の仕入伝票締処理 - 開始');
            // 仕入データセット
            $purchase_data = $this->setChargeData($purchase_data, $order_data);
            Log::info('仕入単位の仕入伝票締処理 - 終了');
        }

        return $purchase_data;
    }

    /**
     * 仕入データセット
     *
     * @param PurchaseClosing $purchase_data
     * @param Collection $order_data
     * @return PurchaseClosing
     */
    private function setChargeData(PurchaseClosing $purchase_data, Collection $order_data): PurchaseClosing
    {
        // ログ表示用
        $process_log = [
            'log_purchase_total' => 0,                 // 今回仕入額
            'log_purchase_total_normal_out' => 0,      //   通常税率_外税分
            'log_purchase_total_reduced_out' => 0,     //   軽減税率_外税分
            'log_purchase_total_normal_in' => 0,       //   通常税率_内税分
            'log_purchase_total_reduced_in' => 0,      //   軽減税率_内税分
            'log_purchase_tax_total' => 0,             // 消費税額
            'log_purchase_tax_normal_out' => 0,        //   通常税率_外税分
            'log_purchase_tax_reduced_out' => 0,       //   軽減税率_外税分
            'log_purchase_tax_normal_in' => 0,         //   通常税率_内税分
            'log_purchase_tax_reduced_in' => 0,        //   軽減税率_内税分
            'log_purchase_total_free' => 0,            // 今回仕入額_非課税分
        ];

        foreach ($order_data as $order) {
            // 今回仕入額
            $purchase_data->purchase_total += $order->purchase_total;
            $process_log['log_purchase_total'] += $order->purchase_total;
            // 非課税：今回仕入額
            if ($order->consumption_tax_rate === 0) {
                $purchase_data->purchase_total_free += $order->purchase_total;
                $process_log['log_purchase_total_free'] += $order->purchase_total;

                continue;   // 行単位でいずれかしかないのでcontinueする
            }

            // 通常税率：今回仕入額と消費税額
            if ($order->reduced_tax_flag === ReducedTaxFlagType::NOT_REDUCED_TAX) {
                // 外税
                if ($order->tax_type_id === TaxType::OUT_TAX) {
                    // 今回仕入額
                    $purchase_data->purchase_total_normal_out += $order->purchase_total;
                    $process_log['log_purchase_total_normal_out'] += $order->purchase_total;
                    // 消費税
                    $tax = TaxHelper::getTax($order->purchase_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $purchase_data->purchase_tax_normal_out += $tax;
                    $process_log['log_purchase_tax_normal_out'] += $tax;

                    // 外税時のみ加算
                    $purchase_data->purchase_tax_total += $tax;
                    $process_log['log_purchase_tax_total'] += $tax;
                }
                // 内税
                if ($order->tax_type_id === TaxType::IN_TAX) {
                    // 今回仕入額
                    $purchase_data->purchase_total_normal_in += $order->purchase_total;
                    $process_log['log_purchase_total_normal_in'] += $order->purchase_total;
                    // 消費税
                    $tax = TaxHelper::getInTax($order->purchase_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $purchase_data->purchase_tax_normal_in += $tax;
                    $process_log['log_purchase_tax_normal_in'] += $tax;
                }

                continue;   // 行単位でいずれかしかないのでcontinueする
            }

            // 軽減税率：今回仕入額と消費税額
            if ($order->reduced_tax_flag === ReducedTaxFlagType::REDUCED_TAX) {
                // 外税
                if ($order->tax_type_id === TaxType::OUT_TAX) {
                    // 今回仕入額
                    $purchase_data->purchase_total_reduced_out += $order->purchase_total;
                    $process_log['log_purchase_total_reduced_out'] += $order->purchase_total;
                    // 消費税
                    $tax = TaxHelper::getTax($order->purchase_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $purchase_data->purchase_tax_reduced_out += $tax;
                    $process_log['log_purchase_tax_reduced_out'] += $tax;

                    // 外税時のみ加算
                    $purchase_data->purchase_tax_total += $tax;
                    $process_log['log_purchase_tax_total'] += $tax;
                }
                // 内税
                if ($order->tax_type_id === TaxType::IN_TAX) {
                    // 今回仕入額
                    $purchase_data->purchase_total_reduced_in += $order->purchase_total;
                    $process_log['log_purchase_total_reduced_in'] += $order->purchase_total;
                    // 消費税
                    $tax = TaxHelper::getInTax($order->purchase_total, $order->consumption_tax_rate, $order->rounding_method_id);
                    $purchase_data->purchase_tax_reduced_in += $tax;
                    $process_log['log_purchase_tax_reduced_in'] += $tax;
                }
            }
        }

        if (count($order_data) > 0) {
            Log::info('  今回仕入額:' . number_format($process_log['log_purchase_total']));
            Log::info('    通常税率_外税分:' . number_format($process_log['log_purchase_total_normal_out']));
            Log::info('    軽減税率_外税分:' . number_format($process_log['log_purchase_total_reduced_out']));
            Log::info('    通常税率_内税分:' . number_format($process_log['log_purchase_total_normal_in']));
            Log::info('    軽減税率_内税分:' . number_format($process_log['log_purchase_total_reduced_in']));
            Log::info('  消費税額:' . number_format($process_log['log_purchase_tax_total']));
            Log::info('    通常税率_外税分:' . number_format($process_log['log_purchase_tax_normal_out']));
            Log::info('    軽減税率_外税分:' . number_format($process_log['log_purchase_tax_reduced_out']));
            Log::info('    通常税率_内税分:' . number_format($process_log['log_purchase_tax_normal_in']));
            Log::info('    軽減税率_内税分:' . number_format($process_log['log_purchase_tax_reduced_in']));
            Log::info('  今回仕入額_非課税分:' . number_format($process_log['log_purchase_total_free']));
        }

        return $purchase_data;
    }

    /**
     * 仕入伝票削除
     *
     * @param int $purchase_data_id
     */
    private function deletePurchaseData(int $purchase_data_id): void
    {
        Log::info('仕入伝票解除:仕入ID：' . $purchase_data_id .
            ' / 仕入先ID：' . PurchaseClosing::find($purchase_data_id)->supplier_id .
            ' ' . MasterSupplier::find(PurchaseClosing::find($purchase_data_id)->supplier_id)->name);

        PurchaseClosing::find($purchase_data_id)->delete();
    }

    /**
     * 伝票リレーション作成
     *
     * @param int $purchase_data_id
     * @param array $purchase_order_ids
     * @param array $payment_ids
     */
    private function makeOrderRelation(int $purchase_data_id, array $purchase_order_ids, array $payment_ids): void
    {
        // 仕入データ　仕入伝票リレーション登録
        PurchaseClosingPurchaseOrder::createPurchaseRelation($purchase_order_ids, $purchase_data_id);
        // 支払データ　支払伝票リレーション登録
        PurchaseClosingPayment::createPaymentRelation($payment_ids, $purchase_data_id);
    }

    /**
     * 伝票リレーション削除
     *
     * @param int $purchase_data_id
     */
    private function deletePurchaseRelation(int $purchase_data_id): void
    {
        // 仕入データ　仕入伝票リレーション登録
        PurchaseClosingPurchaseOrder::deletePurchaseRelation($purchase_data_id);
        // 支払データ　支払伝票リレーション登録
        PurchaseClosingPayment::deletePaymentRelation($purchase_data_id);
    }

    /**
     * 締処理済み日付スタンプ
     *
     * @param Carbon $process_date
     * @param array $purchase_order_ids
     * @param array $payment_ids
     * @return void
     */
    private function updateClosingAt(Carbon $process_date, array $purchase_order_ids, array $payment_ids): void
    {
        // 仕入伝票に締処理済み日付スタンプ
        PurchaseOrder::updateClosingAt($purchase_order_ids, $process_date);
        // 支払伝票に締処理済み日付スタンプ
        Payment::updateClosingAt($payment_ids, $process_date);
    }
}
