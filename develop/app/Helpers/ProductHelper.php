<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\RoundingMethodType;
use App\Enums\TaxType;
use App\Http\Controllers\Api\SendController;
use App\Models\Master\MasterCustomerPrice;
use App\Models\Master\MasterCustomerProduct;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterSupplierProduct;
use App\Models\Sale\SalesOrderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * 商品マスタ用ヘルパークラス
 */
class ProductHelper
{
    /**
     * 商品マスタの単価一致チェック(マスタ単価チェック時は単位を条件に含まない）
     *
     * @param int $product_id
     * @param float $unit_price
     * @return bool
     */
    public static function existMasterProductUnitPrice(int $product_id, float $unit_price): bool
    {
        return MasterProduct::where('id', $product_id)
            ->where('unit_price', $unit_price)
            ->exists();
    }

    /**
     * 商品マスタの単価一致チェック(マスタ単価チェック時は単位を条件に含まない）
     *
     * @param int $product_id
     * @param float $unit_price
     * @return bool
     */
    public static function existMasterProductPurchaseUnitPrice(int $product_id, float $unit_price): bool
    {
        return MasterProduct::where('id', $product_id)
            ->where('purchase_unit_price', $unit_price)
            ->exists();
    }

    /**
     * 得意先単価の存在チェック
     *
     * @param int $customer_id
     * @param int $product_id
     * @return bool
     */
    public static function existCustomerPrice(int $customer_id, int $product_id): bool
    {
        return MasterCustomerPrice::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->exists();
    }

    /**
     * 得意先単価の存在チェック
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @return bool
     */
    public static function existCustomerUnitPrice(int $customer_id, int $product_id, string $unit_name): bool
    {
        return MasterCustomerProduct::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->exists();
    }

    /**
     * 仕入先単価の存在チェック
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param string $unit_name
     * @return bool
     */
    public static function existSupplierUnitPrice(int $supplier_id, int $product_id, string $unit_name): bool
    {
        return MasterSupplierProduct::where('supplier_id', $supplier_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->exists();
    }

    /**
     * 得意先単価(同額)の存在チェック
     *
     * @param int $customer_id
     * @param int $product_id
     * @param float $unit_price
     * @return bool
     */
    public static function existCustomerPriceSameAmount(int $customer_id, int $product_id, float $unit_price): bool
    {
        return MasterCustomerPrice::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->where('sales_unit_price', $unit_price)
            ->exists();
    }

    /**
     * 得意先単価(同額)の存在チェック
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @param float $unit_price
     * @return bool
     */
    public static function existCustomerUnitPriceSameAmount(int $customer_id, int $product_id, string $unit_name, float $unit_price): bool
    {
        return MasterCustomerProduct::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->where('last_unit_price', $unit_price)
            ->exists();
    }

    /**
     * 仕入先単価(同額)の存在チェック
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param string $unit_name
     * @param float $unit_price
     * @return bool
     */
    public static function existSupplierUnitPriceSameAmount(int $supplier_id, int $product_id, string $unit_name, float $unit_price): bool
    {
        return MasterSupplierProduct::where('supplier_id', $supplier_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->where('last_unit_price', $unit_price)
            ->exists();
    }

    /**
     * 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
     *
     * @param int $customer_id
     * @param int $product_id
     * @return JsonResponse
     */
    public static function getCustomerPrice(int $customer_id, int $product_id): JsonResponse
    {
        // 商品マスタの単価を取得
        $sales_unit_price = MasterProduct::find($product_id)->unit_price ?? 0;
        // 得意先ID/商品IDで単価が存在するか
        if (self::existCustomerPrice($customer_id, $product_id)) {
            // 得意先ID/商品IDが持つ単価をセット
            $sales_unit_price = MasterCustomerPrice::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->value('sales_unit_price');
        }

        return response()->json([$sales_unit_price]);
    }

    /**
     * 得意先毎の単価を返す（登録されていない場合はマスタの単価を返す）
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @return JsonResponse
     */
    public static function getCustomerUnitPrice(int $customer_id, int $product_id, string $unit_name): JsonResponse
    {
        // 商品マスタの単価を取得
        $last_unit_price = MasterProduct::find($product_id)->unit_price ?? 0;
        // 得意先ID/商品ID/単位名で単価が存在するか
        if (self::existCustomerUnitPrice($customer_id, $product_id, $unit_name)) {
            // 得意先ID/商品ID/単位が持つ単価をセット
            $last_unit_price = MasterCustomerProduct::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->where('unit_name', $unit_name)
                ->value('last_unit_price');
        }

        return response()->json([$last_unit_price]);
    }

    /**
     * 仕入先毎の単価を返す（登録されていない場合はマスタの単価を返す）
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param string $unit_name
     * @return JsonResponse
     */
    public static function getSupplierUnitPrice(int $supplier_id, int $product_id, string $unit_name): JsonResponse
    {
        // 商品マスタの単価を取得
        $last_unit_price = MasterProduct::find($product_id)->purchase_unit_price ?? 0;
        // 得意先ID/商品ID/単位名で単価が存在するか
        if (self::existSupplierUnitPrice($supplier_id, $product_id, $unit_name)) {
            // 得意先ID/商品ID/単位が持つ単価をセット
            $last_unit_price = MasterSupplierProduct::where('supplier_id', $supplier_id)
                ->where('product_id', $product_id)
                ->where('unit_name', $unit_name)
                ->value('last_unit_price');
        }

        return response()->json([$last_unit_price]);
    }

    /**
     * 得意先毎の単価を返す（デコード）
     *
     * @param int $customer_id
     * @param int $product_id
     * @return array
     */
    public static function getCustomerPriceArray(int $customer_id, int $product_id): array
    {
        return json_decode(self::getCustomerPrice($customer_id, $product_id)->getContent(), true);
    }

    /**
     * 得意先毎の単価を返す（デコード）
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @return array
     */
    public static function getCustomerUnitPriceArray(int $customer_id, int $product_id, string $unit_name): array
    {
        return json_decode(self::getCustomerUnitPrice($customer_id, $product_id, $unit_name)->getContent(), true);
    }

    /**
     * 得意先単価を登録/更新する
     *   upsertはdelete_atを考慮してないので使用しない
     *
     * @param int $customer_id
     * @param string $order_date
     * @param SalesOrderDetail $detail
     * @return JsonResponse
     */
    public static function upsertCustomerPrice(int $customer_id, string $order_date, SalesOrderDetail $detail): JsonResponse
    {
        $product_id = $detail->product_id;
        $sales_unit_price = $detail->unit_price;
        $tax_type_id = $detail->tax_type_id;
        $consumption_tax_rate = $detail->consumption_tax_rate;

        $tax_included = 0;
        $reduced_tax_included = 0;
        $unit_price = 0;
        $master_prodact = MasterProduct::where('id', $product_id)->first();
        $rounding = $master_prodact->amount_rounding_method_id;

        if ($rounding === RoundingMethodType::ROUND_DOWN && $tax_type_id === TaxType::OUT_TAX) {
            // 切り捨て・外税
            $unit_price = floor($sales_unit_price * 100) / 100;
        }
        if ($rounding === RoundingMethodType::ROUND_DOWN && $tax_type_id === TaxType::IN_TAX) {
            // 切り捨て・内税
            $unit_price = floor(($sales_unit_price / (1 + $consumption_tax_rate / 100)) * 100) / 100;
        }
        if ($rounding === RoundingMethodType::ROUND_DOWN && $tax_type_id === TaxType::TAX_EXEMPT) {
            // 切り捨て・非課税
            $unit_price = floor($sales_unit_price * 100) / 100;
        }

        if ($rounding === RoundingMethodType::ROUND_UP && $tax_type_id === TaxType::OUT_TAX) {
            // 切り上げ・外税
            $unit_price = ceil($sales_unit_price * 100) / 100;
        }
        if ($rounding === RoundingMethodType::ROUND_UP && $tax_type_id === TaxType::IN_TAX) {
            // 切り上げ・内税
            $unit_price = ceil(($sales_unit_price / (1 + $consumption_tax_rate / 100)) * 100) / 100;
        }
        if ($rounding === RoundingMethodType::ROUND_UP && $tax_type_id === TaxType::TAX_EXEMPT) {
            // 切り捨て・非課税
            $unit_price = ceil($sales_unit_price * 100) / 100;
        }

        if ($rounding === RoundingMethodType::ROUND_OFF && $tax_type_id === TaxType::OUT_TAX) {
            // 四捨五入・外税
            $unit_price = round($sales_unit_price, 2);
        }
        if ($rounding === RoundingMethodType::ROUND_OFF && $tax_type_id === TaxType::IN_TAX) {
            // 四捨五入・内税
            $unit_price = round($sales_unit_price / (1 + $consumption_tax_rate / 100), 2);
        }
        if ($rounding === RoundingMethodType::ROUND_OFF && $tax_type_id === TaxType::TAX_EXEMPT) {
            // 四捨五入・非課税
            $unit_price = round($sales_unit_price, 2);
        }

        if (self::existCustomerPrice($customer_id, $product_id)) {
            $sales_date = MasterCustomerPrice::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->value('sales_date');

            if ($sales_date < $order_date) {
                // 存在する場合、update
                $customer_price = MasterCustomerPrice::where('customer_id', $customer_id)
                    ->where('product_id', $product_id)
                    ->update([
                        'sales_unit_price' => $sales_unit_price,
                        'sales_date' => $order_date,
                        'tax_included' => $tax_included,
                        'reduced_tax_included' => $reduced_tax_included,
                        'unit_price' => $unit_price,
                    ]);

                return response()->json([$customer_price]);
            }

            return response()->json([]);
        }

        $request = new Request();
        $request->type = 'customer_price';
        $request->available_number = 1;
        $code = (new SendController())->searchAvailableNumber($request);

        // 存在しない場合、insert
        $customer_price = MasterCustomerPrice::create([
            'code' => $code,
            'customer_id' => $customer_id,
            'product_id' => $product_id,
            'sales_date' => $order_date,
            'sales_unit_price' => $sales_unit_price,
            'tax_included' => $tax_included,
            'reduced_tax_included' => $reduced_tax_included,
            'unit_price' => $unit_price,
        ]);

        return response()->json([$customer_price]);
    }

    /**
     * 得意先単価を登録/更新する
     *   upsertはdelete_atを考慮してないので使用しない
     * 　※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @param float $unit_price
     * @return JsonResponse
     */
    public static function upsertCustomerUnitPrice(int $customer_id, int $product_id, string $unit_name, float $unit_price): JsonResponse
    {
        if (self::existCustomerUnitPrice($customer_id, $product_id, $unit_name)) {
            // 存在する場合、update
            $customer_product = MasterCustomerProduct::where('customer_id', $customer_id)
                ->where('product_id', $product_id)
                ->where('unit_name', $unit_name)
                ->update(['last_unit_price' => $unit_price]);

            return response()->json([$customer_product]);
        }

        // 存在しない場合、insert
        $customer_product = MasterCustomerProduct::create(
            ['customer_id' => $customer_id, 'product_id' => $product_id, 'unit_name' => $unit_name, 'last_unit_price' => $unit_price]
        );

        return response()->json([$customer_product]);
    }

    /**
     * 仕入先単価を登録/更新する
     *   upsertはdelete_atを考慮してないので使用しない
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param string $unit_name
     * @param float $unit_price
     * @return JsonResponse
     */
    public static function upsertSupplierUnitPrice(int $supplier_id, int $product_id, string $unit_name, float $unit_price): JsonResponse
    {
        if (self::existSupplierUnitPrice($supplier_id, $product_id, $unit_name)) {
            // 存在する場合、update
            $supplier_product = MasterSupplierProduct::where('supplier_id', $supplier_id)
                ->where('product_id', $product_id)
                ->where('unit_name', $unit_name)
                ->update(['last_unit_price' => $unit_price]);

            return response()->json([$supplier_product]);
        }

        // 存在しない場合、insert
        $supplier_product = MasterSupplierProduct::create(
            ['supplier_id' => $supplier_id, 'product_id' => $product_id, 'unit_name' => $unit_name, 'last_unit_price' => $unit_price]
        );

        return response()->json([$supplier_product]);
    }

    /**
     * 得意先単価を削除(論理）する
     *
     * @param int $customer_id
     * @param int $product_id
     * @return JsonResponse
     */
    public static function deleteCustomerPrice(int $customer_id, int $product_id): JsonResponse
    {
        MasterCustomerPrice::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->delete();

        return response()->json(['delete successed']);
    }

    /**
     * 得意先単価を削除(論理）する
     * ※CodeSpacesでは使用しない
     *
     * @param int $customer_id
     * @param int $product_id
     * @param string $unit_name
     * @return JsonResponse
     */
    public static function deleteCustomerUnitPrice(int $customer_id, int $product_id, string $unit_name): JsonResponse
    {
        MasterCustomerProduct::where('customer_id', $customer_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->delete();

        return response()->json(['delete successed']);
    }

    /**
     * 仕入先単価を削除(論理）する
     *
     * @param int $supplier_id
     * @param int $product_id
     * @param string $unit_name
     * @return JsonResponse
     */
    public static function deleteSupplierUnitPrice(int $supplier_id, int $product_id, string $unit_name): JsonResponse
    {
        MasterSupplierProduct::where('supplier_id', $supplier_id)
            ->where('product_id', $product_id)
            ->where('unit_name', $unit_name)
            ->delete();

        return response()->json(['delete successed']);
    }
}
