<?php

/**
 * POS API用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Api;

use App\Consts\API\PosApiConst;
use App\Enums\InventoryType;
use App\Enums\SalesClassification;
use App\Enums\TaxCalcType;
use App\Helpers\SettingsHelper;
use App\Http\Controllers\Api\ApiCommonController;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterWarehouse;
use App\Services\TeamsService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * POS連携用 APIサービス
 */
class PosApiServices
{
    /** 取込スキップフラグ */
    private bool $is_skip_import = false;

    /**
     * コンストラクタ
     */
    public function __construct() {}

    /**
     * 商品マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendProductMaster(Request $request): mixed
    {
        Log::channel('pos_info')->info('==============================');
        Log::channel('pos_info')->info(' sendProductMaster：商品マスタ送信');
        Log::channel('pos_info')->info(' 開始日時 ' . Carbon::now()->format('Y/m/d H:i:s'));
        Log::channel('pos_info')->info('==============================');

        try {
            // 送信されたリクエストから取得条件を取得
            $isResult = $this->requestParamCheck($request);
            if (!$isResult) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];
                Log::channel('pos_info')->info('取得条件が正しくありません');

                return response()->json($arrResult);
            }

            // 商品マスタから対象データを取得
            $limit_count = $request->limit_count ?? PosApiConst::POS_SEND_LIMIT_COUNT;
            $arrProduct = MasterProduct::getProductDataByPos($request->target_date, $limit_count);
            Log::channel('pos_info')->info('対象件数は ' . count($arrProduct ?? 0) . '件 です');

            if (empty($arrProduct)) {
                $arrResult = [
                    'result' => true,
                    'result_msg' => '取得件数が0件です',
                ];

                return response()->json($arrResult);
            }

            // POSへ送信
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . count($arrProduct) . '件です',
                'result_data' => json_encode($arrProduct),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            Log::channel('pos_err')->error('【Error】sendProductMaster :　' . $e->getMessage());
            $arrResult = [
                'result' => false,
                'result_msg' => $e->getMessage(),
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 得意先マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendCustomerMaster(Request $request)
    {
        Log::channel('pos_info')->info('==============================');
        Log::channel('pos_info')->info(' sendCustomerMaster：得意先マスタ送信');
        Log::channel('pos_info')->info(' 開始日時 ' . Carbon::now()->format('Y/m/d H:i:s'));
        Log::channel('pos_info')->info('==============================');

        try {
            // 送信されたリクエストから取得条件を取得
            $isResult = $this->requestParamCheck($request);
            if (!$isResult) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];
                Log::channel('pos_info')->info('取得条件が正しくありません');

                return response()->json($arrResult);
            }

            // 得意先マスタから対象データを取得
            $limit_count = $request->limit_count ?? PosApiConst::POS_SEND_LIMIT_COUNT;
            $arrCustomer = MasterCustomer::getCustomerDataByPos($request->target_date, $limit_count);
            Log::channel('pos_info')->info(' 対象件数は ' . count($arrCustomer ?? 0) . '件 です');

            if (empty($arrCustomer)) {
                $arrResult = [
                    'result' => true,
                    'result_msg' => '取得件数が0件です',
                ];

                return response()->json($arrResult);
            }

            // POSへ送信
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . count($arrCustomer) . '件です',
                'result_data' => json_encode($arrCustomer),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            Log::channel('pos_err')->error('【Error】sendCustomerMaster :　' . $e->getMessage());
            $arrResult = [
                'result' => false,
                'result_msg' => $e->getMessage(),
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 得意先別単価マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendUnitPriceByCustomerMaster(Request $request)
    {
        Log::channel('pos_info')->info('==============================');
        Log::channel('pos_info')->info(' sendUnitPriceByCustomerMaster：得意先別単価マスタ送信');
        Log::channel('pos_info')->info(' 開始日時 ' . Carbon::now()->format('Y/m/d H:i:s'));
        Log::channel('pos_info')->info('==============================');

        try {
            // 送信されたリクエストから取得条件を取得
            $isResult = $this->requestParamCheck($request);
            if (!$isResult) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];
                Log::channel('pos_info')->info('取得条件が正しくありません');

                return response()->json($arrResult);
            }

            // 得意先別単価マスタマスタから対象データを取得
            $limit_count = $request->limit_count ?? PosApiConst::POS_SEND_LIMIT_COUNT;
            $arrCustomerPrice = MasterCustomerPrice::getCustomerPriceDataByPos($request->target_date, $limit_count);
            Log::channel('pos_info')->info('対象件数は ' . count($arrCustomerPrice ?? 0) . '件 です');

            if (empty($arrCustomerPrice)) {
                $arrResult = [
                    'result' => true,
                    'result_msg' => '取得件数が0件です',
                ];

                return response()->json($arrResult);
            }

            // POSへ送信
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . count($arrCustomerPrice) . '件です',
                'result_data' => json_encode($arrCustomerPrice),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            Log::channel('pos_err')->error('【Error】sendUnitPriceByCustomerMaster :　' . $e->getMessage());
            $arrResult = [
                'result' => false,
                'result_msg' => $e->getMessage(),
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 担当者マスタ送信
     *
     * @param Request $request
     * @return mixed
     */
    public function sendEmployeesMaster(Request $request)
    {
        Log::channel('pos_info')->info('==============================');
        Log::channel('pos_info')->info(' sendEmployeesMaster：担当者マスタ送信');
        Log::channel('pos_info')->info(' 開始日時 ' . Carbon::now()->format('Y/m/d H:i:s'));
        Log::channel('pos_info')->info('==============================');

        try {
            // 送信されたリクエストから取得条件を取得
            $isResult = $this->requestParamCheck($request);
            if (!$isResult) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];
                Log::channel('pos_info')->info('取得条件が正しくありません');

                return response()->json($arrResult);
            }

            // 担当マスタから対象データを取得
            $limit_count = $request->limit_count ?? PosApiConst::POS_SEND_LIMIT_COUNT;
            $arrEmployee = MasterEmployee::getEmployeeDataByPos($request->target_date, $limit_count);
            Log::channel('pos_info')->info('対象件数は ' . count($arrEmployee ?? 0) . '件 です');

            if (empty($arrEmployee)) {
                $arrResult = [
                    'result' => true,
                    'result_msg' => '取得件数が0件です',
                ];

                return response()->json($arrResult);
            }

            // POSへ送信
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . count($arrEmployee) . '件です',
                'result_data' => json_encode($arrEmployee),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            Log::channel('pos_err')->error('【Error】sendEmployeesMaster :　' . $e->getMessage());
            $arrResult = [
                'result' => false,
                'result_msg' => $e->getMessage(),
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 販売データ受信
     *
     * @param Request $request
     * @return mixed
     *
     * @throws Exception
     */
    public function receiveSales(Request $request): mixed
    {
        // 連携先セット
        $url = PosApiConst::RECEIVE_SALES_URL_PROD;
        if (!app()->isProduction()) {
            // 本番環境以外
            $url = PosApiConst::RECEIVE_SALES_URL_ST;
        }

        // todo 本番一時停止
        if (app()->isProduction()) {
            $arrResult = [
                'result' => false,
                'result_msg' => 'データ連携を停止しています',
            ];

            return response()->json($arrResult);
        }

        try {
            // 取得条件を取得
            $target_date = $request->target_date ?? null;
            if (is_null($target_date)) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];

                return response()->json($arrResult);
            }
            $store_id = $request->store_id ?? null;
            $regi_id = $request->regi_id ?? null;

            // 要求ループ用
            $this->is_skip_import = false;
            $limit_count = PosApiConst::POS_RECEIVE_LIMIT_COUNT;
            $count_prev_receive = $limit_count;
            $count_all_receive = 0;
            $count_cycle = 0;

            while ($count_prev_receive === $limit_count) {
                ++$count_cycle;
                $target_date = new Carbon($target_date)->format('Y-m-d H:i:s');

                Log::channel('pos_info')->info('==============================');
                Log::channel('pos_info')->info(' receiveSales');
                Log::channel('pos_info')->info(' $store_id = ' . $store_id);
                Log::channel('pos_info')->info(' $target_date = ' . $target_date);
                Log::channel('pos_info')->info(' 要求回数 = ' . $count_cycle);
                Log::channel('pos_info')->info('==============================');

                // パラメータ設定
                $data = [
                    'sales_request_data' => [
                        'latest_datetime' => $target_date,
                        'store_id' => $store_id,
                        'regi_id' => $regi_id,
                        'limit_count' => $limit_count,
                    ],
                ];

                $response = (new ApiCommonController())->apiBasicCommunicationTypePost($url, $data);
                $body = json_decode($response->getBody()->getContents());

                // エラー処理
                [$result, $msg] = $this->checkReceiveBody($body);
                if (strlen($msg) > 0) {
                    Log::channel('pos_info')->error('販売データの連携処理 レスポンスエラー');
                    Log::channel('pos_info')->error($msg);
                    $arrResult = [
                        'result' => $result,
                        'result_msg' => $msg,
                    ];

                    return response()->json($arrResult);
                }

                if (count($body->RESULT_DATA) === 0) {
                    break;
                }

                // 取得データを DB に登録する
                [$count_prev_receive, $this->is_skip_import] = $this->processOrderData($body->RESULT_DATA, $target_date);
                // 総件数の足しこみ
                $count_all_receive += $count_prev_receive;
            }

            Log::channel('pos_info')->info('販売データの連携処理 完了');
            Log::channel('pos_info')->info('取得件数は ' . ($count_all_receive ?? 0) . '件 です');

            // 取込スキップ発生時Teams通知(スキップが発生した時かつsettingsの設定に値が設定されている場合)
            $send_teams_url = SettingsHelper::getErrorTeamsWebhookUrl();
            if ($this->is_skip_import && $send_teams_url) {
                $teams = new TeamsService();
                $teams->sendToTeams(
                    $teams->makeTeamsSkipTitle(),
                    "販売データの連携時に取込をスキップした伝票があります。\n詳細はログを確認してください。",
                    $send_teams_url
                );
            }

            // 返却値
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . $count_all_receive . '件です',
                'result_data' => json_encode($body->RESULT_DATA),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            $msg = '【POS Receive】receiveSales :　' . $e->getMessage();
            Log::channel('pos_err')->error($msg);
            $arrResult = [
                'result' => false,
                'result_msg' => $msg,
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * POS連携データ(販売データ)の処理
     *
     * @param array $data_list
     * @param string $target_date
     * @return array
     *
     * @throws Exception
     */
    private function processOrderData(array $data_list, string &$target_date): array
    {
        $this->is_skip_import = false;
        // インスタンス化しておき、処理速度向上
        $import_sales_data = new ImportSalesData($this->is_skip_import);
        // 取得データを DB に登録する
        foreach ($data_list as $order_data) {
            $torihiki_status = intval($order_data->torihiki_status);

            // 最終データの更新日を退避する
            $target_date = $order_data->update_date;

            // 売上・返品
            if ($torihiki_status === SalesClassification::CLASSIFICATION_SALE
                || $torihiki_status === SalesClassification::CLASSIFICATION_RETURN) {
                // 売上伝票処理 ⇒ 出庫処理
                $this->is_skip_import = $this->handleSalesData($import_sales_data, (array) $order_data);

                continue;
            }
            // 取消
            if ($torihiki_status === SalesClassification::CLASSIFICATION_CANCEL) {
                // 売上伝票 削除 ⇒ 出庫処理
                $this->is_skip_import = $import_sales_data->deleteSalesOrder((array) $order_data);

                continue;
            }
            // サービス・試飲・その他
            if ($torihiki_status === SalesClassification::CLASSIFICATION_SERVICE
                || $torihiki_status === SalesClassification::CLASSIFICATION_TASTING
                || $torihiki_status === SalesClassification::CLASSIFICATION_OTHER
            ) {
                // 売上処理 ⇒ 出庫処理
                $this->is_skip_import = $import_sales_data->insertSalesOrder((array) $order_data);

                continue;
            }
            // 入庫(6)～資材(19)
            if ($torihiki_status >= 6 && $torihiki_status <= 19) {
                // 入出庫データ作成
                $this->is_skip_import = (new ImportInventoryData())->createInventoryData((array) $order_data);

                continue;
            }

            // 取込スキップフラグ
            $this->is_skip_import = true;
            // その他 取り込みなし
            Log::channel('pos_info')->info('POS連携 販売データ：取込スキップ 取引形態=' . $torihiki_status
                . ', 店舗コード=' . $order_data->store_no . ', 伝票番号=' . $order_data->order_id
                . ', 更新日時=' . $order_data->update_date);
        }

        return [count($data_list), $this->is_skip_import];
    }

    /**
     * 売上伝票の作成(条件により分岐)
     *
     * @param ImportSalesData $import_sales_data
     * @param array $order_data
     * @return bool
     *
     * @throws Exception
     */
    private function handleSalesData(ImportSalesData $import_sales_data, array $order_data): bool
    {
        // 税抜単価
        if ($this->isTaxExcluded($order_data)) {
            // 売上処理 ⇒ 出庫処理
            return $import_sales_data->insertSalesOrder($order_data);
        }
        // 税込単価
        if ($this->isTaxIncluded($order_data)) {
            // 税込から税抜へ変換
            $order_data['total'] = $order_data['total'] - $order_data['total_tax'];
            foreach ($order_data['details'] as $detail) {
                // 税込から税抜へ変換
                $detail->price = $detail->price - $detail->tax;
            }

            // 売上処理 ⇒ 出庫処理
            return $import_sales_data->insertSalesOrder($order_data);
        }
        // order税込、detail税抜
        if ($this->isTaxMixed($order_data)) {
            // 税込から税抜へ変換
            $order_data['total'] = $order_data['total'] - $order_data['total_tax'];

            // 売上処理 ⇒ 出庫処理
            return $import_sales_data->insertSalesOrder($order_data);
        }

        return false;
    }

    /**
     * 税抜か判定
     *
     * @param array $order
     * @return bool
     */
    private function isTaxExcluded(array $order): bool
    {
        // 配列内の値を全て数値に変換
        $order = array_map('intval', $order);
        // 請求単位、無処理、店頭販売
        if ($order['tax_class'] === TaxCalcType::BILLING
            || $order['tax_class'] === TaxCalcType::NONE
            || $order['tax_class'] === TaxCalcType::STORE_SALES
        ) {
            return true;
        }

        // 伝票単位、明細単位 かつ 現売
        return ($order['tax_class'] === TaxCalcType::ORDER || $order['tax_class'] === TaxCalcType::DETAIL)
            && $order['uriage_status'] === 0;
    }

    /**
     * 税込か判定
     *
     * @param array $order
     * @return bool
     */
    private function isTaxIncluded(array $order): bool
    {
        // 配列内の値を全て数値に変換
        $order = array_map('intval', $order);
        // 伝票単位、明細単位 かつ
        if ($order['tax_class'] !== TaxCalcType::ORDER && $order['tax_class'] !== TaxCalcType::DETAIL) {
            return false;
        }
        // 売掛 かつ
        if ($order['uriage_status'] !== 1) {
            return false;
        }

        // 得意先ID 1～99 または 100000以上
        return ($order['customer_id'] >= 1 && $order['customer_id'] <= 99) || $order['customer_id'] >= 100000;
    }

    /**
     * 税込と税抜が混在しているか判定
     *
     * @param array $order
     * @return bool
     */
    private function isTaxMixed(array $order): bool
    {
        // 配列内の値を全て数値に変換
        $order = array_map('intval', $order);
        // 伝票単位、明細単位 かつ
        if ($order['tax_class'] !== TaxCalcType::ORDER && $order['tax_class'] !== TaxCalcType::DETAIL) {
            return false;
        }
        // 売掛 かつ
        if ($order['uriage_status'] !== 1) {
            return false;
        }

        // 得意先ID 100～99999
        return $order['customer_id'] >= 100 && $order['customer_id'] <= 99999;
    }

    /**
     * 棚卸データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receiveInventory(Request $request): mixed
    {
        // 連携先セット
        $url = PosApiConst::RECEIVE_INVENTORY_URL_PROD;
        if (!app()->isProduction()) {
            // 本番環境以外
            $url = PosApiConst::RECEIVE_INVENTORY_URL_ST;
        }

        // todo 本番一時停止
        if (app()->isProduction()) {
            $arrResult = [
                'result' => false,
                'result_msg' => 'データ連携を停止しています',
            ];

            return response()->json($arrResult);
        }

        try {
            // 取得条件を取得
            $target_date = $request->target_date ?? null;
            if (is_null($target_date)) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];

                return response()->json($arrResult);
            }
            $store_id = $request->store_id ?? null;
            $inventory_status = $request->inventory_status ?? null;

            // 倉庫データを取得
            $warehouse_list = MasterWarehouse::query()
                ->where('code', '<', InventoryType::INVENTORY_ADJUST)->get();
            if (!is_null($store_id)) {
                $warehouse_list = MasterWarehouse::query()
                    ->where('code', $store_id)->get();
            }

            // 要求ループ用
            $this->is_skip_import = false;
            $count_all_receive = 0;
            $count_prev_receive = 0;
            $count_cycle = 0;

            foreach ($warehouse_list as $warehouse) {
                ++$count_cycle;

                Log::channel('pos_info')->info('==============================');
                Log::channel('pos_info')->info(' receiveInventory');
                Log::channel('pos_info')->info(' $store_id = ' . $warehouse['code']);
                Log::channel('pos_info')->info(' 要求回数 = ' . $count_cycle);
                Log::channel('pos_info')->info('==============================');

                // パラメータ設定
                $data = [
                    'inventory_request_data' => [
                        'store_id' => $warehouse['code'],
                        'inventory_status' => PosApiConst::POS_RECEIVE_INVENTORY_STATUS_REQUESTABLE,
                        'limit_count' => '',
                    ],
                ];

                $response = (new ApiCommonController())->apiBasicCommunicationTypePost($url, $data);
                $body = json_decode($response->getBody()->getContents());

                // エラー処理
                [$result, $msg] = $this->checkReceiveBodyReceiveInventory($body);
                if (strlen($msg) > 0) {
                    Log::channel('pos_info')->error('棚卸データの連携処理 レスポンスエラー');
                    Log::channel('pos_info')->error($msg);
                    $arrResult = [
                        'result' => $result,
                        'result_msg' => $msg,
                    ];

                    return response()->json($arrResult);
                }

                // データゼロ件の場合は次の店舗へ
                if (count($body->RESULT_DATA) === 0) {
                    continue;
                }

                // 取得データを DB に登録する
                $count_prev_receive = 0;
                $processed_inventory_dates = []; // 処理済み棚卸日付を記録
                foreach ($body->RESULT_DATA as $inventory_data) {
                    ++$count_prev_receive;

                    // 棚卸処理
                    $this->is_skip_import = (new ImportInventoryData())->setInventoryAdjustment((array) $inventory_data);

                    // 棚卸日付を記録（POS通知用）
                    $inventory_date = $inventory_data->inventory_date;
                    if (!in_array($inventory_date, $processed_inventory_dates)) {
                        $processed_inventory_dates[] = $inventory_date;
                    }
                }

                // この店舗で処理したすべての棚卸日付に対してPOS通知を実行
                foreach ($processed_inventory_dates as $inventory_date) {
                    $notification_response = $this->inventoryCompletionToPos($inventory_date, $warehouse['code']);

                    Log::channel('pos_info')->info('POS通知完了: 店舗=' . $warehouse['code'] . ', 棚卸日付=' . $inventory_date);
                    // POS通知でエラーが発生した場合は、そのレスポンスを返す
                    $notification_data = json_decode($notification_response->getContent(), true);
                    if (!$notification_data['result']) {
                        return $notification_response;
                    }
                }

                // 総件数の足しこみ
                $count_all_receive += $count_prev_receive;
            }

            Log::channel('pos_info')->info('棚卸データの連携処理 完了');
            Log::channel('pos_info')->info('取得件数は ' . ($count_all_receive ?? 0) . '件 です');

            // 取込スキップ発生時Teams通知
            if ($this->is_skip_import) {
                $teams = new TeamsService();
                $teams->sendToTeams(
                    $teams->makeTeamsSkipTitle(),
                    "棚卸データの連携時に取込をスキップした伝票があります。\n詳細はログを確認してください。",
                    SettingsHelper::getErrorTeamsWebhookUrl()
                );
            }

            // 返却値
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . $count_all_receive . '件です',
                'result_data' => json_encode($body->RESULT_DATA),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            $msg = '【POS Receive】receiveInventory :　' . $e->getMessage();
            Log::channel('pos_err')->error($msg);
            $arrResult = [
                'result' => false,
                'result_msg' => $msg,
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 工場出庫データ受信
     *
     * @param Request $request
     * @return mixed
     */
    public function receiveFactoryShipping(Request $request): mixed
    {
        // 連携先セット
        $url = PosApiConst::RECEIVE_FACTORY_SHIPPING_URL_PROD;
        if (!app()->isProduction()) {
            $url = PosApiConst::RECEIVE_FACTORY_SHIPPING_URL_ST;
        }

        // todo 本番一時停止
        if (app()->isProduction()) {
            $arrResult = [
                'result' => false,
                'result_msg' => 'データ連携を停止しています',
            ];

            return response()->json($arrResult);
        }

        try {
            // 取得条件を取得
            $target_date = $request->target_date ?? null;
            if (is_null($target_date)) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];

                return response()->json($arrResult);
            }
            $store_id = $request->store_id ?? null;
            $regi_id = $request->regi_id ?? null;

            // 要求ループ用
            $this->is_skip_import = false;
            $limit_count = PosApiConst::POS_RECEIVE_LIMIT_COUNT;
            $count_prev_receive = $limit_count;
            $count_all_receive = 0;
            $count_cycle = 0;

            while ($count_prev_receive === $limit_count) {
                ++$count_cycle;
                $target_date = new Carbon($target_date)->format('Y-m-d H:i:s');

                Log::channel('pos_info')->info('==============================');
                Log::channel('pos_info')->info(' receiveFactoryShipping');
                Log::channel('pos_info')->info(' $target_date = ' . $target_date);
                Log::channel('pos_info')->info(' 要求回数 = ' . $count_cycle);
                Log::channel('pos_info')->info('==============================');

                // パラメータ設定
                $data = [
                    'shipment_request_data' => [
                        'latest_datetime' => $target_date,
                        'store_id' => $store_id,
                        'regi_id' => $regi_id,
                        'limit_count' => $limit_count,
                    ],
                ];

                $response = (new ApiCommonController())->apiBasicCommunicationTypePost($url, $data);
                $body = json_decode($response->getBody()->getContents());

                // エラー処理
                [$result, $msg] = $this->checkReceiveBody($body);
                if (strlen($msg) > 0) {
                    Log::channel('pos_info')->error('工場出庫データの連携処理 レスポンスエラー');
                    Log::channel('pos_info')->error($msg);
                    $arrResult = [
                        'result' => $result,
                        'result_msg' => $msg,
                    ];

                    return response()->json($arrResult);
                }

                // データゼロ件の場合
                if (count($body->RESULT_DATA) === 0) {
                    break;
                }

                $count_prev_receive = 0;
                foreach ($body->RESULT_DATA as $order_data) {
                    ++$count_prev_receive;
                    // TODO: 取得データを DB に登録する
                    // 現行の販売管理システム上で、このデータを使用していないので、使い道がわからないとのこと
                    // CodeSpaces様から「取込なし」との判断があったため、連携機能は残すが、登録はしていない

                    // 最終データの更新日を退避する
                    $target_date = $order_data->update_date;
                }

                // 総件数の足しこみ
                $count_all_receive += $count_prev_receive;
            }

            Log::channel('pos_info')->info('工場出庫データの連携処理 完了');
            Log::channel('pos_info')->info('取得件数は ' . ($count_all_receive ?? 0) . '件 です');

            // 返却値
            $arrResult = [
                'result' => true,
                //                'result_msg' => '取得件数' . count($body->RESULT_DATA) . '件です',
                'result_msg' => '取得件数' . $count_all_receive . '件です',
                'result_data' => json_encode($body->RESULT_DATA),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            $msg = '【POS Receive】receiveFactoryShipping :　' . $e->getMessage();
            Log::channel('pos_err')->error($msg);
            $arrResult = [
                'result' => false,
                'result_msg' => $msg,
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * 仕入データ受信
     *
     * @param Request $request
     * @return mixed
     *
     * @throws Exception
     */
    public function receivePurchase(Request $request): mixed
    {
        $url = PosApiConst::RECEIVE_PURCHASE_URL_PROD;
        if (!app()->isProduction()) {
            $url = PosApiConst::RECEIVE_PURCHASE_URL_ST;
        }

        // todo 本番一時停止
        if (app()->isProduction()) {
            $arrResult = [
                'result' => false,
                'result_msg' => 'データ連携を停止しています',
            ];

            return response()->json($arrResult);
        }

        try {
            // 取得条件を取得
            $target_date = $request->target_date ?? null;
            if (is_null($target_date)) {
                $arrResult = [
                    'result' => false,
                    'result_msg' => '取得条件が正しくありません',
                ];

                return response()->json($arrResult);
            }
            $store_id = $request->store_id ?? null;
            $regi_id = $request->regi_id ?? null;

            // 要求ループ用
            $this->is_skip_import = false;
            $limit_count = PosApiConst::POS_RECEIVE_LIMIT_COUNT;
            $count_prev_receive = $limit_count;
            $count_all_receive = 0;
            $count_cycle = 0;

            while ($count_prev_receive === $limit_count) {
                ++$count_cycle;
                $target_date = new Carbon($target_date)->format('Y-m-d H:i:s');

                Log::channel('pos_info')->info('==============================');
                Log::channel('pos_info')->info(' receivePurchase');
                Log::channel('pos_info')->info(' $target_date = ' . $target_date);
                Log::channel('pos_info')->info(' 要求回数 = ' . $count_cycle);
                Log::channel('pos_info')->info('==============================');

                // パラメータ設定
                $data = [
                    'purchase_request_data' => [
                        'latest_datetime' => $target_date,
                        'store_id' => $store_id,
                        'regi_id' => $regi_id,
                        'limit_count' => $limit_count,
                    ],
                ];

                $response = (new ApiCommonController())->apiBasicCommunicationTypePost($url, $data);
                $body = json_decode($response->getBody()->getContents());

                // エラー処理
                [$result, $msg] = $this->checkReceiveBody($body);
                if (strlen($msg) > 0) {
                    Log::channel('pos_info')->error('仕入データの連携処理 レスポンスエラー');
                    Log::channel('pos_info')->error($msg);
                    $arrResult = [
                        'result' => $result,
                        'result_msg' => $msg,
                    ];

                    return response()->json($arrResult);
                }

                // データゼロ件の場合
                if (count($body->RESULT_DATA) === 0) {
                    break;
                }

                // 取得データを DB に登録する
                $count_prev_receive = 0;
                foreach ($body->RESULT_DATA as $purchase_data) {
                    ++$count_prev_receive;
                    // 入庫処理
                    $this->is_skip_import = (new ImportInventoryData())->setInventoryReceipt((array) $purchase_data);

                    // 最終データの更新日を退避する
                    $target_date = $purchase_data->update_date;
                }

                // 総件数の足しこみ
                $count_all_receive += $count_prev_receive;
            }

            Log::channel('pos_info')->info('仕入データの連携処理 完了');
            Log::channel('pos_info')->info('取得件数は ' . ($count_all_receive ?? 0) . '件 です');

            // 取込スキップ発生時Teams通知
            if ($this->is_skip_import) {
                $teams = new TeamsService();
                $teams->sendToTeams(
                    $teams->makeTeamsSkipTitle(),
                    "仕入データの連携時に取込をスキップした伝票があります。\n詳細はログを確認してください。",
                    SettingsHelper::getErrorTeamsWebhookUrl()
                );
            }

            // 返却値
            $arrResult = [
                'result' => true,
                'result_msg' => '取得件数' . $count_all_receive . '件です',
                'result_data' => json_encode($body->RESULT_DATA),
            ];

            return response()->json($arrResult);

        } catch (GuzzleException|Exception $e) {
            $msg = '【POS Receive】receivePurchase :　' . $e->getMessage();
            Log::channel('pos_err')->error($msg);
            $arrResult = [
                'result' => false,
                'result_msg' => $msg,
            ];

            return response()->json($arrResult);
        }
    }

    /**
     * パラメータチェック
     *
     * @param Request $request
     * @return bool
     */
    public function requestParamCheck(Request $request): bool
    {
        // 送信されたリクエストから取得条件を取得
        $date = $request->target_date ?? null;
        if (is_null($date)) {
            return false;
        }

        try {
            $format = 'Y-m-d H:i:s';
            $dt = Carbon::createFromFormat($format, $date);

            return $dt->format($format) === $date;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * API通信の返却値チェック
     *
     * @param $body
     * @return array
     */
    public function checkReceiveBody($body): array
    {
        $msg = '';
        $result = true;
        if ($body->RESULT !== 'OK') {
            $result = false;
            $msg = 'データの受信処理に失敗しました (Result : NG)';
        }
        if ($body->RESULT_CODE !== '200') {
            $result = false;
            $msg = 'データの受信処理に失敗しました (Result Code : ' . $body->RESULT_CODE . ')';
        }

        return [$result, $msg];
    }

    /**
     * API通信の返却値チェック(棚卸)受信
     *
     * @param $body
     * @return array
     */
    public function checkReceiveBodyReceiveInventory($body): array
    {
        $msg = '';
        $result = true;
        if ($body->RESULT !== 'OK') {
            $result = false;
            $msg = 'データの受信処理に失敗しました (Result : NG)';
        }
        if ($body->RESULT_CODE !== '200') {
            $result = false;
            $msg = 'データの受信処理に失敗しました (Result Code : ' . $body->RESULT_CODE . ')';
        }

        return [$result, $msg];
    }

    /**
     * API通信の返却値チェック(棚卸)送信
     *
     * @param $body
     * @return array
     */
    private function checkSendBodySendInventory($body): array
    {
        $msg = '';
        $result = true;

        if (!isset($body->RESULT) || $body->RESULT !== 'OK') {
            $result = false;
            $msg = 'データの送信処理に失敗しました (Result : ' . ($body->RESULT ?? 'Unknown') . ')';
        }

        if (!isset($body->RESULT_CODE) || $body->RESULT_CODE !== '200') {
            $result = false;
            $msg = 'データの送信処理に失敗しました (Result Code : ' . ($body->RESULT_CODE ?? 'Unknown') . ')';
        }

        return [$result, $msg];
    }

    /**
     * POS側へ棚卸データ受信完了を通知
     *
     * @param Carbon $inventory_date 棚卸日付
     * @param string $store_code 店舗コード
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws Exception
     */
    private function inventoryCompletionToPos($inventory_date, $store_code): \Illuminate\Http\JsonResponse
    {
        // 連携先セット
        $url = PosApiConst::SEND_INVENTORY_URL_PROD;
        if (!app()->isProduction()) {
            $url = PosApiConst::SEND_INVENTORY_URL_ST;
        }

        $data = [
            'inventory_result_data' => [
                'inventory_date' => $inventory_date,
                'store_no' => $store_code,
            ],
        ];

        try {
            Log::channel('pos_info')->info('POS通知データ : ' . json_encode($data));
            $response = (new ApiCommonController())->apiBasicCommunicationTypePost($url, $data);
            $body = json_decode($response->getBody()->getContents());

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON decode error: ' . json_last_error_msg());
            }

            // エラー処理
            [$result, $msg] = $this->checkSendBodySendInventory($body);

            if (!$result) {
                // API通信エラー時
                return response()->json([
                    'result' => $result,
                    'result_msg' => $msg,
                ]);
            }

            // 成功時のレスポンス
            Log::channel('pos_info')->info('POS通知成功 : 在庫調整処理が正常に完了しました');

            return response()->json([
                'result' => true,
                'result_msg' => '在庫調整処理が正常に完了しました',
            ]);

        } catch (GuzzleException $e) {
            Log::channel('pos_err')->error('API通信エラー : ' . $e->getMessage());

            return response()->json([
                'result' => false,
                'result_msg' => 'POS側への通知処理に失敗しました',
            ]);
        }
    }
}
