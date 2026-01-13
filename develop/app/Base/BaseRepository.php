<?php

/**
 * ベース用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Base;

use Illuminate\Database\Eloquent\Model;

/**
 * ベース用リポジトリ
 */
class BaseRepository
{
    protected Model $model;

    /**
     * モデルをインスタンス
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * idのMax+1を取得
     *
     * @return int
     */
    public function getMaxPlusOneOfId(): int
    {
        return $this->model->withTrashed()->max('id') + 1;
    }

    /**
     * order_numberを元に対象のモデルを取得又は新規でモデルを作成
     *
     * @param int $order_number
     * @return Model
     */
    public function firstOrNewForOrderNumber(int $order_number): Model
    {
        return $this->model->query()->firstOrNew(['order_number' => $order_number]);
    }
}
