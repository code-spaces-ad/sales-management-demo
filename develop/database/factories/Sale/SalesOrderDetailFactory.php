<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace Database\Factories\Sale;

use App\Enums\ReducedTaxFlagType;
use App\Models\Master\MasterProductUnit;
use App\Models\Master\MasterUnit;
use App\Models\Sale\SalesOrder;
use App\Models\Sale\SalesOrderDetail;
use App\Models\Master\MasterProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Helpers\TaxHelper;
use App\Enums\SalesClassification;
use App\Enums\TaxType;

class SalesOrderDetailFactory extends Factory
{
    protected $model = SalesOrderDetail::class;

    public function definition()
    {
        // 売上伝票取得（もしくは作成）
        $salesOrder = SalesOrder::inRandomOrder()->first() ?? SalesOrder::factory()->create();

        // 商品+単位情報取得
        $product = MasterProduct::inRandomOrder()->first();
        $productUnit = MasterProductUnit::where('product_id', $product->id)->first();
        $unit = MasterUnit::where('id', $productUnit->unit_id)->first();

        $unitPrice = $product->unit_price;
        $unitName = $unit->name;

        // 数量
        $quantity = $this->faker->numberBetween(1, 10);

        // 売上分類によって変更
        $magnification = -1;
        $discount = 0;
        if ($salesOrder->sales_classification_id == SalesClassification::CLASSIFICATION_SALE) {
            $magnification = 1;
            $discount = $this->faker->numberBetween(0, ($unitPrice - 1));
        }

        // 売上分類によって変更
        $subTotal = $quantity * $unitPrice * $magnification;
        $grossProfit = 0;
        if ($salesOrder->sales_classification_id == SalesClassification::CLASSIFICATION_SALE) {
            $grossProfit = $subTotal - ($product->purchase_unit_price * $quantity);
        }

        $tax_list = TaxHelper::getTaxRate($salesOrder->order_date);
        $set_tax = ($product->reduced_tax_flag == ReducedTaxFlagType::REDUCED_TAX)
            ? $tax_list['reduced_tax_rate']
            : $tax_list['normal_tax_rate'];

        // 税区分によって変更
        if ($product->tax_type_id == TaxType::OUT_TAX) {
            // 外税
            $subTotalTax = TaxHelper::getTax(
                $unitPrice,
                $set_tax,
                $product->amount_rounding_method_id
            ) * $quantity * $magnification;
        }
        if ($product->tax_type_id == TaxType::IN_TAX) {
            // 内税
            $subTotalTax = TaxHelper::getInTax(
                $unitPrice,
                $set_tax,
                $product->amount_rounding_method_id
            ) * $quantity * $magnification;
        }
        if ($product->tax_type_id == TaxType::TAX_EXEMPT) {
            // 非課税
            $subTotalTax = 0;
        }

        return [
            // 売上伝票ID
            'sales_order_id' => $salesOrder->id,
            // 商品ID
            'product_id' => $product->id,
            // 商品名
            'product_name' => $product->name,
            // 単価小数桁数
            'unit_price_decimal_digit' => $product->unit_price_decimal_digit,
            // 数量小数桁数
            'quantity_decimal_digit' => $product->quantity_decimal_digit,
            // 数量端数処理
            'quantity_rounding_method_id' => $product->quantity_rounding_method_id,
            // 金額端数処理
            'amount_rounding_method_id' => $product->amount_rounding_method_id,
            // 数量
            'quantity' => $quantity,
            // 単位
            'unit_name' => $unitName,
            // 単価
            'unit_price' => $unitPrice,
            // 値引額
            'discount' => $discount,
            // 小計金額
            'sub_total' => $subTotal,
            // 小計税額
            'sub_total_tax' => $subTotalTax,
            // 税区分
            'tax_type_id' => $product->tax_type_id,
            // 仕入単価
            'purchase_unit_price' => $product->purchase_unit_price,
            // 消費税率
            'consumption_tax_rate' => $set_tax,
            // 軽減税率対象フラグ
            'reduced_tax_flag' => $product->reduced_tax_flag,
            // 消費税端数処理方法
            'rounding_method_id' => $product->amount_rounding_method_id,
            // 粗利
            'gross_profit' => $grossProfit,
            // 備考
            'note' => '※factoryにて生成。',
            // ソート
            'sort' => 1,
        ];
    }
}
