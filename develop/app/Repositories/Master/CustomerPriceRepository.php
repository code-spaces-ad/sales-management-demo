<?php

/**
 * 得意先別単価マスター用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Models\Master\MasterCustomerPrice;
use Illuminate\Database\Eloquent\Model;

/**
 * 得意先別単価マスタ用リポジトリ
 */
class CustomerPriceRepository
{
    protected MasterCustomerPrice $masters;

    protected Model $model;

    protected int $id;

    /**
     * インスタンス化
     *
     * @param MasterCustomerPrice $masters
     * @param MasterCustomerPrice $model
     */
    public function __construct(MasterCustomerPrice $masters,
        MasterCustomerPrice $model)
    {
        $this->masters = $masters;
        if (isset($masters->query()->first()->id)) {
            $this->id = $masters->query()->first()->id;
        }
        $this->model = $model;
    }
}
