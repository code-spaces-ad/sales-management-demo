<?php

/**
 * 納品先マスタ用リポジトリ
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Repositories\Master;

use App\Helpers\MasterIntegrityHelper;
use App\Models\Master\MasterRecipient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * 納品先マスタ用リポジトリ
 */
class RecipientRepository
{
    protected MasterRecipient $masters;

    protected Model $model;

    protected int $id;

    /**
     * インスタンス化
     *
     * @param MasterRecipient $masters
     * @param MasterRecipient $model
     */
    public function __construct(MasterRecipient $masters,
        MasterRecipient $model)
    {
        $this->masters = $masters;
        if (isset($masters->query()->first()->id)) {
            $this->id = $masters->query()->first()->id;
        }
        $this->model = $model;
    }

    /**
     * 検索結果を取得 (ページネーション)
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return $this->model->query()
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.recipients.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public function getSearchResult(array $search_condition_input_data): Collection
    {
        return $this->model->query()
            ->searchCondition($search_condition_input_data)
            ->get();
    }

    /**
     * マスタの使用状況を返す
     *
     * @return bool
     */
    public function getUseMasterAttribute(): bool
    {
        return MasterIntegrityHelper::existsUseMasterRecipient($this->id);
    }

    /**
     * 条件を元にデータ取得
     *
     * @param string $branchId
     * @param string $recipientName
     * @return Builder|Model|object|null
     */
    public function getRecipientByBrachAndRecipientName(string $branchId, string $recipientName)
    {
        return $this->model->query()
            ->where('branch_id', '=', $branchId)
            ->where('name', '=', $recipientName)
            ->first();
    }

    /**
     * 納品先新規登録
     *
     * @param array $array
     * @return Model
     */
    public function createRecipient(array $array): Model
    {
        return $this->model->query()->create($array);
    }

    /**
     * 得意先更新
     *
     * @param Model $recipient
     * @param array $array
     * @return Model
     */
    public function updateRecipient(Model $recipient, array $array): Model
    {
        return tap($recipient)->update($array);
    }

    /**
     * 得意先削除
     *
     * @param Model $recipient
     * @return bool|null
     */
    public function deleteRecipient(Model $recipient): ?bool
    {
        return $recipient->delete();
    }

    /**
     * 納品先IDセット
     *
     * @param Request $request
     * @return int|null
     */
    public static function setRecipientId(Request $request): ?int
    {
        // 支所IDか納品先名に値がなければ、nullをセット
        if (empty($request->branch_id) || empty($request->recipient_name)) {
            return null;
        }

        $result = MasterRecipient::getRecipientByBrachAndRecipientName($request->branch_id, $request->recipient_name);
        // 納品先マスタにデータが存在していれば、そのIDをセット
        if (!empty($result)) {
            return $result->id;
        }

        // データがなければ、納品先マスタに登録の上、そのIDをセット
        $MasterRecipient = new MasterRecipient();
        $MasterRecipient->name = $request->recipient_name;
        $MasterRecipient->name_kana = $request->recipient_name_kana;
        $MasterRecipient->branch_id = $request->branch_id;

        $MasterRecipient->save();

        return MasterRecipient::query()->max('id');
    }
}
