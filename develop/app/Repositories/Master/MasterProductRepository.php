<?php

/**
 * 商品マスタ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Models\Master\MasterProduct;
use Illuminate\Database\Eloquent\Model;

/**
 * 商品マスタ用リポジトリ
 */
class MasterProductRepository
{
    protected Model $model;

    /**
     * 商品マスタモデルをインスタンス
     *
     * @param MasterProduct $model
     */
    public function __construct(MasterProduct $model)
    {
        $this->model = $model;
    }

    /**
     * 仕入れ単価を取得
     *
     * @param $product_id
     * @return string
     */
    public function getPurchaseUnitPrice($product_id): string
    {
        return MasterProduct::query()->where('id', $product_id)->value('purchase_unit_price');
    }
}
