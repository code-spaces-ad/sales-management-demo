<?php

namespace App\Models\Master;

use App\Consts\DB\Master\MasterOfficeFacilitiesConst;
use App\Helpers\MasterIntegrityHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MasterOfficeFacility extends Model
{
    /**
     * コード用トレイト使用
     */
    use HasCode;

    /**
     * ソフトデリート有効（論理削除）
     */
    use SoftDeletes;

    /**
     * テーブル名
     *
     * @var string
     */
    protected $table = 'm_office_facilities';

    /**
     * 複数代入する属性
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'department_id',
        'name',
        'manager_id',
        'note',
        'created_at',
        'updated_at',
        'deleted_at',
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
        $this->code_length = MasterOfficeFacilitiesConst::CODE_MAX_LENGTH;
    }

    /**
     * 指定した検索条件だけに限定するクエリースコープ
     *
     * @param Builder $query
     * @param array $search_condition_input_data
     * @return Builder
     */
    public function scopeSearchCondition(Builder $query, array $search_condition_input_data): Builder
    {
        $target_id = $search_condition_input_data['id'] ?? null;
        $target_code = $search_condition_input_data['code'] ?? null;
        $target_department_id = $search_condition_input_data['department_id'] ?? null;
        $target_name = $search_condition_input_data['name'] ?? null;

        $select_recipient_column = [
            DB::raw('m_departments.name AS department_name'),
            DB::raw('m_departments.id AS department_id'),
            DB::raw('m_departments.code AS department_code'),
            DB::raw('m_office_facilities.id AS id'),
            DB::raw('m_office_facilities.name AS name'),
            DB::raw('m_office_facilities.code AS code'),
            DB::raw('m_office_facilities.note AS note'),
            DB::raw('m_employees.id AS employee_id'),
            DB::raw('m_employees.name AS employee_name'),
        ];

        return $query->select($select_recipient_column)
            ->leftJoin('m_departments', 'm_office_facilities.department_id', '=', 'm_departments.id')
            ->leftJoin('m_employees', 'm_office_facilities.manager_id', '=', 'm_employees.id')
            ->when(isset($target_id), function ($query) use ($target_id) {
                return $query->id($target_id['start'] ?? null, $target_id['end'] ?? null);
            })
            ->when(isset($target_code), function ($query) use ($target_code) {
                return $query->code($target_code['start'] ?? null, $target_code['end'] ?? null);
            })
            ->when(isset($target_department_id), function ($query) use ($target_department_id) {
                return $query->where('m_office_facilities.department_id', $target_department_id);
            })
            ->when(isset($target_name), function ($query) use ($target_name) {
                return $query->name($target_name);
            })
            ->oldest('m_departments.code')
            ->oldest('m_office_facilities.code');
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
        if (is_null($target_id_start) && is_null($target_id_end)) {
            return $query;
        }
        if (isset($target_id_start) && is_null($target_id_end)) {
            return $query->where('m_office_facilities.id', '>=', $target_id_start);
        }
        if (is_null($target_id_start) && isset($target_id_end)) {
            return $query->where('m_office_facilities.id', '<=', $target_id_end);
        }

        return $query->whereBetween('m_office_facilities.id', [$target_id_start, $target_id_end]);
    }

    /**
     * 指定したコードのレコードだけに限定するクエリースコープ(範囲)
     *
     * @param Builder $query
     * @param string|null $target_code_start
     * @param string|null $target_code_end
     * @return Builder
     */
    public function scopeCode(Builder $query, ?string $target_code_start, ?string $target_code_end): Builder
    {
        if (is_null($target_code_start) && is_null($target_code_end)) {
            return $query;
        }
        if (isset($target_code_start) && is_null($target_code_end)) {
            return $query->where('m_office_facilities.code', '>=', $target_code_start);
        }
        if (is_null($target_code_start) && isset($target_code_end)) {
            return $query->where('m_office_facilities.code', '<=', $target_code_end);
        }

        return $query->whereBetween('m_office_facilities.code', [$target_code_start, $target_code_end]);
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
        return $query->where('m_office_facilities.name', 'LIKE', '%' . $target_name . '%');
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
            ->searchCondition($search_condition_input_data)
            ->paginate(config('consts.default.master.accounting_codes.page_count'));
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
        return MasterIntegrityHelper::existsUseMasterOfficeFacility($this->id);
    }

    // region eloquent-relationships

    /**
     * 倉庫マスター テーブルとのリレーション
     *
     * @return HasOne
     */
    public function mWarehouse(): HasOne
    {
        return $this->hasOne(MasterWarehouse::class, 'code', 'code');
    }

    /**
     * 部門マスター テーブルとのリレーション
     *
     * @return HasOne
     */
    public function mDepartment(): HasOne
    {
        return $this->hasOne(MasterDepartment::class, 'id', 'department_id');
    }

    /**
     * 社員マスター テーブルとのリレーション
     *
     * @return HasOne
     */
    public function mEmployee(): HasOne
    {
        return $this->hasOne(MasterEmployee::class, 'id', 'manager_id');
    }

    /**
     * 事業所コードを指定して、部門IDを返す
     *
     * @param int $code
     * @return int
     */
    public static function getDepartmentId(int $code): int
    {
        return self::query()
            ->where('code', $code)
            ->first('department_id');
    }
}
