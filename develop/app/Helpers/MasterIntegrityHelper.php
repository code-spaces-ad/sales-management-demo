<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Inventory\InventoryData;
use App\Models\Inventory\InventoryDataDetail;
use App\Models\Invoice\ChargeData;
use App\Models\Master\MasterProduct;
use App\Models\Receive\OrdersReceived;
use App\Models\Receive\OrdersReceivedDetail;
use App\Models\Sale\DepositOrder;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Models\Trading\Payment;
use App\Models\Trading\PurchaseOrder;
use App\Models\Trading\PurchaseOrderDetail;

/**
 * マスター使用判定用ヘルパークラス
 */
class MasterIntegrityHelper
{
    /**
     * 商品マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterProduct(int $id): bool
    {
        $column = 'product_id';
        // 受注伝票詳細
        if (OrdersReceivedDetail::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票詳細
        if (SalesOrderDetail::query()->where($column, $id)->exists()) {
            return true;
        }
        // 仕入伝票詳細
        if (PurchaseOrderDetail::query()->where($column, $id)->exists()) {
            return true;
        }
        // 在庫データ詳細
        if (InventoryDataDetail::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * カテゴリーマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterCategory(int $id): bool
    {
        $column = 'category_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 得意先マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterCustomer(int $id): bool
    {
        $column = 'customer_id';
        // 受注伝票
        if (OrdersReceived::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 入金伝票
        if (DepositOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 支所マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterBranch(int $id): bool
    {
        $column = 'branch_id';
        // 受注伝票
        if (OrdersReceived::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 納品先マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterRecipient(int $id): bool
    {
        $column = 'recipient_id';
        // 受注伝票
        if (OrdersReceived::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 仕入先マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterSupplier(int $id): bool
    {
        $column = 'supplier_id';
        // 仕入伝票
        if (PurchaseOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 支払伝票
        if (Payment::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 担当マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterEmployee(int $id): bool
    {
        $column = 'employee_id';
        // 受注伝票
        if (OrdersReceived::query()->where($column, $id)->exists()) {
            return true;
        }
        // 在庫データ
        if (InventoryData::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 倉庫マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterWarehouse(int $id): bool
    {
        $column = 'warehouse_id';
        // 受注伝票詳細
        if (OrdersReceivedDetail::query()->where($column, $id)->exists()) {
            return true;
        }
        // 在庫データ(移動元)
        if (InventoryData::query()->where('from_' . $column, $id)->exists()) {
            return true;
        }
        // 在庫データ(移動先)
        if (InventoryData::query()->where('to_' . $column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 種別マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterKind(int $id): bool
    {
        $column = 'kind_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 管理部署マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterSection(int $id): bool
    {
        $column = 'section_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 分類1マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterClassification1(int $id): bool
    {
        $column = 'classification1_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 分類2マスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterClassification2(int $id): bool
    {
        $column = 'classification2_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * サブカテゴリーマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseSubMasterCategory(int $id): bool
    {
        $column = 'sub_category_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 経理コードマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterAccountingCode(int $id): bool
    {
        $column = 'accounting_code_id';
        // 商品マスター
        if (MasterProduct::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 集計グループマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterSummaryGroup(int $id): bool
    {
        return false;
    }

    /**
     * 部門コードマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterDepartment(int $id): bool
    {
        $column = 'department_id';
        // 請求データ
        if (ChargeData::query()->where($column, $id)->exists()) {
            return true;
        }
        // 入金伝票
        if (DepositOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 支払伝票
        if (Payment::query()->where($column, $id)->exists()) {
            return true;
        }
        // 仕入伝票
        if (PurchaseOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 事業所コードマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterOfficeFacility(int $id): bool
    {
        $column = 'office_facilities_id';
        // 請求データ
        if (ChargeData::query()->where($column, $id)->exists()) {
            return true;
        }
        // 入金伝票
        if (DepositOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 支払伝票
        if (Payment::query()->where($column, $id)->exists()) {
            return true;
        }
        // 仕入伝票
        if (PurchaseOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * 部門コードマスタの使用有無チェック
     *
     * @param int $id
     * @return bool
     */
    public static function existsUseMasterCustomerPrice(int $id): bool
    {
        $column = 'department_id';
        // 請求データ
        if (ChargeData::query()->where($column, $id)->exists()) {
            return true;
        }
        // 入金伝票
        if (DepositOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 支払伝票
        if (Payment::query()->where($column, $id)->exists()) {
            return true;
        }
        // 仕入伝票
        if (PurchaseOrder::query()->where($column, $id)->exists()) {
            return true;
        }
        // 売上伝票
        if (SalesOrder::query()->where($column, $id)->exists()) {
            return true;
        }

        return false;
    }
}
