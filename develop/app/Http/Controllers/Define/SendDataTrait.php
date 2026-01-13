<?php

/**
 * 入力項目用 トレイト
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Define;

use App\Consts\SessionConst;
use App\Enums\IsControlInventory;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\ReducedTaxFlagType;
use App\Enums\TaxType;
use App\Enums\TransactionType;
use App\Helpers\TaxHelper;
use App\Models\Inventory\InventoryStockData;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification1;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterConsumptionTax;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Master\MasterDepartment;
use App\Models\Master\MasterEmployee;
use App\Models\Master\MasterKind;
use App\Models\Master\MasterOfficeFacility;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterRole;
use App\Models\Master\MasterRoundingMethod;
use App\Models\Master\MasterSection;
use App\Models\Master\MasterSubCategory;
use App\Models\Master\MasterSummaryGroup;
use App\Models\Master\MasterSupplier;
use App\Models\Master\MasterUnit;
use App\Models\Master\MasterUser;
use App\Models\Master\MasterWarehouse;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use Carbon\Carbon;

/**
 * 入力項目用 トレイト
 */
trait SendDataTrait
{
    use SessionConst;

    /**
     * 仕入伝票
     *
     * @param PurchaseOrder $target_data
     * @return array
     */
    private function sendDataPurchaseOrder(PurchaseOrder $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
                /** 仕入先マスター */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
                /** 単位マスター */
                'units' => MasterUnit::query()->oldest('code')->get(),
                /** 商品マスター */
                'products' => MasterProduct::getProductData(),
                /** 状態 */
                'order_status' => OrderStatus::asSelectArray(),
                /** 税率 */
                'tax_rates' => MasterConsumptionTax::getList(),
                /** 税率リスト */
                'consumption_taxes' => MasterConsumptionTax::getList(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 共通使用セッションキー(URL) */
            'session_common_key' => $this->refURLCommonKey(),
        ];
    }

    /**
     * 支払伝票
     *
     * @param Payment $target_data
     * @return array
     */
    private function sendDataPayment(Payment $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 取引種別データ */
                'transaction_types' => TransactionType::asSelectArray(),
                /** 仕入先データ */
                'suppliers' => MasterSupplier::query()->oldest('name_kana')->get(),
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
     * 在庫調整
     *
     * @param object|null $target_data
     * @param int $productId
     * @param int $wareHouseId
     * @return array
     */
    private function sendDataInventoryStocksData(?object $target_data, int $productId, int $wareHouseId): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 倉庫データ */
                'warehouses' => MasterWarehouse::query()->oldest('code')->get(),
                /** 現在庫データ */
                'inventory_stock_data' => InventoryStockData::query()->where('product_id', $productId)->get(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** 商品データ */
            'product_data' => MasterProduct::query()->find($productId),
            /** 倉庫データ */
            'warehouse_data' => MasterWarehouse::query()->find($wareHouseId),
            /** 在庫処理使用セッションキー(URL) */
            'session_inventory_key' => $this->refURLInventoryKey(),
        ];
    }

    /**
     * 商品マスター
     *
     * @param MasterProduct $target_data
     * @return array
     */
    private function sendDataProduct(MasterProduct $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 単位マスター */
                'units' => MasterUnit::query()->get(),
                /** 単価小数桁数リスト */
                'unit_price_decimal_digits' => MasterProduct::getUnitPriceDecimalDigitList(),
                /** 数量小数桁数リスト */
                'quantity_decimal_digits' => MasterProduct::getQuantityDecimalDigitList(),
                /** 税区分リスト */
                'tax_types' => TaxType::asSelectArray(),
                /** 税率区分リスト */
                'tax_rate_types' => ReducedTaxFlagType::asSelectArray(),
                /** 税率リスト */
                'default_tax_list' => TaxHelper::getTaxRate(Carbon::now()->format('Y-m-d')),
                /** 端数処理リスト */
                'rounding_methods' => MasterRoundingMethod::query()->get(),
                /** カテゴリーリスト */
                'categories' => MasterCategory::query()->oldest('code')->get(),
                /** サブカテゴリーリスト */
                'sub_categories' => MasterSubCategory::query()->oldest('code')->get(),
                /** 仕入先マスター */
                'suppliers' => MasterSupplier::query()->oldest('code')->get(),
                /** 種別リスト */
                'kinds' => MasterKind::query()->oldest('code')->get(),
                /** 管理部署リスト */
                'sections' => MasterSection::query()->oldest('code')->get(),
                /** 分類１リスト */
                'classifications1' => MasterClassification1::query()->oldest('code')->get(),
                /** 分類２リスト */
                'classifications2' => MasterClassification2::query()->oldest('code')->get(),
                /** 経理コードマスター */
                'accounting_code' => MasterAccountingCode::query()->oldest('code')->get(),
                /** 商品区分 */
                'product_status' => ProductStatus::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * カテゴリーマスター
     *
     * @param MasterCategory $target_data
     * @return array
     */
    private function sendDataCategory(MasterCategory $target_data): array
    {
        return [
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 担当者マスター
     *
     * @param MasterEmployee $target_data
     * @return array
     */
    private function sendDataEmployee(MasterEmployee $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 支所データ */
                'office_facilities' => MasterOfficeFacility::query()->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 倉庫マスター
     *
     * @param MasterWarehouse $target_data
     * @return array
     */
    private function sendDataWarehouse(MasterWarehouse $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * ユーザーマスター
     *
     * @param MasterUser $target_data
     * @return array
     */
    private function sendDataUser(MasterUser $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'employees' => MasterEmployee::query()->get(),
                'role_id' => MasterRole::query()->where('id', '>=', auth()->user()->role_id)->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** システム設定使用セッションキー(URL) */
            'session_system_key' => $this->refURLSystemKey(),
        ];
    }

    /**
     * 種別マスター
     *
     * @param MasterKind $target_data
     * @return array
     */
    private function sendDataKind(MasterKind $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 管理部署マスター
     *
     * @param MasterSection $target_data
     * @return array
     */
    private function sendDataSection(MasterSection $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 分類1マスター
     *
     * @param MasterClassification1 $target_data
     * @return array
     */
    private function sendDataClassification1(MasterClassification1 $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 分類2マスター
     *
     * @param MasterClassification2 $target_data
     * @return array
     */
    private function sendDataClassification2(MasterClassification2 $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * サブカテゴリマスター
     *
     * @param MasterSubCategory $target_data
     * @return array
     */
    private function sendDataSubCategory(MasterSubCategory $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** カテゴリーリスト */
                'categories' => MasterCategory::query()->oldest('code')->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 経理コードマスター
     *
     * @param MasterAccountingCode $target_data
     * @return array
     */
    private function sendDataAccountingCode(MasterAccountingCode $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 部門コードマスター
     *
     * @param MasterDepartment $target_data
     * @return array
     */
    private function sendDataDepartment(MasterDepartment $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 事業所コードマスター
     *
     * @param MasterOfficeFacility $target_data
     * @return array
     */
    private function sendDataOfficeFacility(MasterOfficeFacility $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
                /** 部門データ */
                'departments' => MasterDepartment::query()->oldest('code')->get(),
                /** 担当者マスター */
                'employees' => MasterEmployee::query()->oldest('code')->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 集計グループマスター
     *
     * @param MasterSummaryGroup $target_data
     * @return array
     */
    private function sendDataSummaryGroup(MasterSummaryGroup $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'is_control_inventory' => IsControlInventory::asSelectArray(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 得意先別単価マスター
     *
     * @param MasterCustomerPrice $target_data
     * @return array
     */
    private function sendDataCustomerPrice(MasterCustomerPrice $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                'customer_price' => MasterCustomerPrice::query()->get(),
                'customers' => MasterCustomer::query()->get(),
                'products' => MasterProduct::query()->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }
}
