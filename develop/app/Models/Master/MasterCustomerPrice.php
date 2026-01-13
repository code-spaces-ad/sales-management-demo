<?php

/**
 * 得意先別単価マスターモデル
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Models\Master;

use App\Consts\DB\Master\MasterCustomerPriceConst;
use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * 商品マスターモデル
 */
class MasterCustomerPrice extends Model
{
    /**
     * コード用トレイト使用
     */
    use HasCode;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    //    public mixed $code;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_customer_price';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'customer_id',
        'product_id',
        'sales_date',
        'sales_unit_price',
        'tax_included',
        'reduced_tax_included',
        'unit_price',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // コード値の桁数セット
        $this->code_length = MasterCustomerPriceConst::CODE_MAX_LENGTH;
    }

    // region eloquent-scope

    /**
     * 指定した検索条件だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchCondition(Builder $query, array $search_condition_input_data): Builder
    {

        $code = $search_condition_input_data['code'] ?? null;
        $customer_id = $search_condition_input_data['customer_id'] ?? null;
        $name = $search_condition_input_data['name'] ?? null;

        return $query

            ->when($code !== null, function ($query) use ($code) {
                return $query->code($code['start'] ?? null, $code['end'] ?? null);
            })

            ->when($customer_id, function ($query) use ($customer_id) {
                return $query->where('customer_id', $customer_id);
            })

            ->when($name, function ($query) use ($name) {
                return $query->whereHas('mProduct', function ($product_query) use ($name) {
                    $product_query->where('name', 'like', '%' . $name . '%');
                });
            })
            ->orderBy('code', 'asc');
    }

    /**
     * 指定したIDのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_id_start
     * @param int|null $target_id_end
     * @return Builder
     */
    public function scopeId(Builder $query, ?int $target_id_start, ?int $target_id_end): Builder
    {
        if ($target_id_start === null && $target_id_end === null) {
            return $query;
        }
        if ($target_id_start !== null && $target_id_end === null) {
            return $query->where('id', '>=', $target_id_start);
        }
        if ($target_id_start === null && $target_id_end !== null) {
            return $query->where('id', '<=', $target_id_end);
        }

        return $query->whereBetween('id', [$target_id_start, $target_id_end]);
    }

    /**
     * 指定したcodeのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param int|null $target_code_start
     * @param int|null $target_code_end
     * @return Builder
     */
    public function scopeCode(Builder $query, ?int $target_code_start, ?int $target_code_end): Builder
    {
        if ($target_code_start === null && $target_code_end === null) {
            return $query;
        }
        if ($target_code_start !== null && $target_code_end === null) {
            return $query->where('code', '>=', $target_code_start);
        }
        if ($target_code_start === null && $target_code_end !== null) {
            return $query->where('code', '<=', $target_code_end);
        }

        return $query->whereBetween('code', [$target_code_start, $target_code_end]);
    }

    /**
     * 指定した名前のレコードだけに限定するクエリースコープ(部分一致)
     *
     * @param Builder $query
     * @param string $target_name
     * @return Builder
     */
    public function scopeName(Builder $query, string $target_name): Builder
    {
        return $query->where('name', 'LIKE', '%' . $target_name . '%');
    }

    /**
     * 検索結果を取得（ページネーション）
     *
     * @param array $search_condition_input_data
     * @return LengthAwarePaginator
     */
    public static function getSearchResultPagenate(array $search_condition_input_data): LengthAwarePaginator
    {
        return self::query()
            ->with(['mCustomer', 'mProduct'])
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.customer_price.page_count'));
    }

    /**
     * 検索結果を取得
     *
     * @param array $search_condition_input_data
     * @return Collection
     */
    public static function getSearchResult(array $search_condition_input_data): Collection
    {
        return self::query()
            ->with(['mCustomer', 'mProduct'])
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
        return MasterIntegrityHelper::existsUseMasterCustomerPrice($this->id);
    }

    /**
     * m_customers テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mCustomer(): BelongsTo
    {
        return $this->belongsTo(MasterCustomer::class, 'customer_id');
    }

    /**
     * m_products テーブルとのリレーション
     *
     * @return BelongsTo
     */
    public function mProduct(): BelongsTo
    {
        return $this->belongsTo(MasterProduct::class, 'product_id');
    }

    /**
     * POS連携用の得意先別単価データを取得
     *
     * @param string $target_date
     * @return Collection
     */
    public static function getCustomerPriceDataByPos(string $target_date, string $limit_count): Collection
    {
        return self::query()
            ->select([
                'm_products.code AS product_code',
                'm_customers.code AS customer_code',
                'm_customer_price.tax_included',
                'm_customer_price.unit_price',
                'm_customer_price.reduced_tax_included',
                DB::raw("DATE_FORMAT(m_customer_price.updated_at, '%Y/%m/%d') AS updated_date"),
                DB::raw("DATE_FORMAT(m_customer_price.updated_at, '%H:%i:%s') AS updated_time"),
            ])
            ->leftJoin('m_products', 'm_customer_price.product_id', '=', 'm_products.id')
            ->leftJoin('m_customers', 'm_customer_price.customer_id', '=', 'm_customers.id')
            ->where('m_customer_price.updated_at', '>=', $target_date)
            ->orderBy('m_customer_price.updated_at')
            ->orderBy('m_customer_price.code')
            ->limit($limit_count)
            ->get();
    }
}
