<?php

/**
 * 入金伝票用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Sale;

use App\Consts\SessionConst;
use App\Enums\TransactionType;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Http\Requests\Sale\DepositEditRequest;
use App\Models\Invoice\ChargeData;
use App\Models\Invoice\ChargeDataDepositOrder;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\DepositOrderBill;
use App\Models\Sale\DepositOrderDetail;
use App\Repositories\Sale\DepositRepository;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * 入金伝票用サービス
 */
class DepositService
{
    use SessionConst;

    protected DepositRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param DepositRepository $repository
     */
    public function __construct(DepositRepository $repository)
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
        // 入金合計額を取得
        $deposit_total = $this->repository->getDepositTotal($input_data);

        return [
            /** 検索項目 */
            'search_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'deposit_orders' => $this->repository->getSearchResult($input_data),
                'deposit_total' => $deposit_total[0],
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param DepositOrder $target_data
     * @return array
     */
    public function create(DepositOrder $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 共通使用セッションキー(URL) */
            'session_common_key' => $this->refURLCommonKey(),
        ];
    }

    /**
     * 新規登録処理
     *
     * @param DepositEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(DepositEditRequest $request): array
    {
        $error_flag = false;

        $order_number = DepositOrder::withTrashed()->max('id') + 1; // 伝票番号

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'order_number' => $order_number,
                'billing_customer_id' => MasterCustomer::query()
                    ->find($request->customer_id)
                    ->billing_customer_id,
                'creator_id' => Auth::user()->id,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }
            // 入金伝票
            $this->repository->createDepositOrder($request->input());

            // 入金伝票詳細
            $default_values = [
                'deposit_order_id' => $order_number,
                'amount_cash' => 0,
                'amount_check' => 0,
                'amount_transfer' => 0,
                'amount_bill' => 0,
                'amount_offset' => 0,
                'amount_discount' => 0,
                'amount_fee' => 0,
                'amount_other' => 0,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }
            // 入金伝票詳細を登録
            $deposit = $this->repository
                ->createDepositOrderDetail($request->input());

            // 入金伝票_手形リレーション
            if ($request->amount_bill > 0) {
                $default_values = [
                    'deposit_order_id' => $deposit->id,
                ];
                foreach ($default_values as $key => $value) {
                    $request->filled($key) ?: $request->merge([$key => $value]);
                }

                $billData = (new DepositOrderBill())->fill($request->input())->toArray();
                $this->updateDepositOrderBill($deposit, $billData);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            // フラッシュメッセージ用にインスタンス作成
            $deposit = DepositOrder::query()
                ->firstOrNew(['order_number' => $order_number]);

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getDepositStoreMessage($error_flag, $deposit->order_number_zero_fill);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param DepositOrder $target_data
     * @return array
     */
    public function edit(DepositOrder $target_data): array
    {
        return $this->create($target_data);
    }

    /**
     * 更新処理
     *
     * @param DepositEditRequest $request
     * @param DepositOrder $deposit
     * @return array
     *
     * @throws Exception
     */
    public function update(DepositEditRequest $request, DepositOrder $deposit): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'billing_customer_id' => MasterCustomer::query()
                    ->find($request->customer_id)
                    ->billing_customer_id,
                'updated_id' => Auth::user()->id,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }
            // 入金伝票を更新
            $deposit = $this->repository->updateDepositOrder($deposit, $request->input());

            // リクエストキーのデフォルトセット
            $default_values = [
                'amount_cash' => 0,
                'amount_check' => 0,
                'amount_transfer' => 0,
                'amount_bill' => 0,
                'amount_offset' => 0,
                'amount_discount' => 0,
                'amount_fee' => 0,
                'amount_other' => 0,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }
            // 入金伝票詳細を更新
            $this->repository
                ->updateDepositOrderDetail($deposit, (new DepositOrderDetail())->fill($request->input())->toArray());

            // リクエストキーのデフォルトセット
            $default_values = [
                'deposit_order_id' => $deposit->id,
                'deleted_at' => null,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }
            // 手形が0より大きい場合、入金伝票_手形リレーションを更新
            if ($request->amount_bill > 0) {
                $billData = (new DepositOrderBill())->fill($request->input())->toArray();
                $this->updateDepositOrderBill($deposit, $billData);
            }

            // 更新時に手形を0以下にしていた場合、入金伝票_手形リレーションを削除
            if ($request->amount_bill <= 0) {
                $this->deleteDepositOrderBill($deposit);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getDepositUpdateMessage($error_flag, $deposit->order_number_zero_fill);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param DepositOrder $deposit
     * @return array
     *
     * @throws Exception
     */
    public function destroy(DepositOrder $deposit): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // 入金伝票削除
            $this->repository->deleteDepositOrder($deposit);

            /** 請求データ_売上伝票リレーション削除 */
            $charge_data_deposit_order = ChargeDataDepositOrder::query()
                ->where('deposit_order_id', $deposit->id)
                ->first();
            if (!is_null($charge_data_deposit_order)) {
                $charge_data_deposit_order->delete();

                /** 請求データ修正 */
                $charge_data = ChargeData::query()
                    ->where('id', $charge_data_deposit_order->charge_data_id)
                    ->first();
                $charge_data->payment_total -= $deposit->deposit; // 今回入金額（回収金額）
                $charge_data->charge_total = $charge_data->calculated_charge_total; // 今回請求額
                $charge_data->save();
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getDepositDestoryMessage($error_flag, $deposit->order_number_zero_fill);

        return [$error_flag, $message];
    }

    /**
     * 入金伝票_手形リレーションの更新処理
     *
     * @param DepositOrder $deposit
     * @param array $billData
     * @return void
     */
    private function updateDepositOrderBill(DepositOrder $deposit, array $billData): void
    {
        // 既存のレコードを取得（削除済みも含む）
        $existingBill = $deposit->depositOrderBill()->withTrashed()->first();

        // 既存データがある場合
        if ($existingBill) {
            // 削除されていれば復元
            if ($existingBill->trashed()) {
                $existingBill->restore();
            }
            // 更新
            $existingBill->update($billData);
        }

        // 既存データがない場合
        if (!$existingBill) {
            // 新規作成
            $deposit->depositOrderBill()->create($billData);
        }
    }

    /**
     * 入金伝票_手形リレーションの削除処理
     *
     * @param DepositOrder $deposit
     * @return void
     */
    private function deleteDepositOrderBill(DepositOrder $deposit): void
    {
        $bill = $deposit->depositOrderBill;
        if ($bill) {
            $bill->delete();
        }
    }
}
