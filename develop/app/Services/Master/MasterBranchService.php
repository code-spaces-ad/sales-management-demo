<?php

/**
 * 支所マスター用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Master;

use App\Consts\SessionConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Http\Requests\Master\BranchEditRequest;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Repositories\Master\BranchRepository;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 支所マスター用サービス
 */
class MasterBranchService
{
    use SessionConst;

    protected BranchRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param BranchRepository $repository
     */
    public function __construct(BranchRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * 一覧画面
     *
     * @param array $input_data
     * @return array
     */
    public function index(array $input_data): array
    {
        return [
            /** 検索項目 */
            'search_items' => [
                /** 得意先データ */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'branches' => $this->repository->getSearchResultPagenate($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param MasterBranch $target_data
     * @return array
     */
    public function create(MasterBranch $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 得意先マスター */
                'customers' => MasterCustomer::query()->oldest('sort_code')->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 新規登録処理
     *
     * @param BranchEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(BranchEditRequest $request): array
    {
        $error_flag = false;
        $branch_id = MasterBranch::withTrashed()->max('id') + 1;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'name' => $request->branch_name,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 支所を登録
            $branch = $this->repository->createBranch($request->input());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            $branch = MasterBranch::query()
                ->firstOrNew([
                    'name' => $request->branch_name,
                ]);
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $branch_id, $branch->name);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param MasterBranch $target_data
     * @return array
     */
    public function edit(MasterBranch $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 得意先マスター */
                'customers' => MasterCustomer::query()->get(),
            ],
            /** 対象レコード */
            'target_record_data' => $target_data,
            /** マスター管理使用セッションキー(URL) */
            'session_master_key' => $this->refURLMasterKey(),
        ];
    }

    /**
     * 更新処理
     *
     * @param BranchEditRequest $request
     * @param MasterBranch $branch
     * @return array
     *
     * @throws Exception
     */
    public function update(BranchEditRequest $request, MasterBranch $branch): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'name' => $request->branch_name,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 支所を更新
            $branch = $this->repository->updateBranch($branch, $request->input());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $branch->id, $branch->name);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param MasterBranch $branch
     * @return array
     *
     * @throws Exception
     */
    public function destroy(MasterBranch $branch): array
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($branch->use_master) {
            $error_flag = true;
            $message = MessageHelper::getMasterDestroyMessage($error_flag, $branch->code_zero_fill, $branch->name);

            return [$error_flag, $message];
        }

        DB::beginTransaction();

        try {
            // 納品先マスターを削除
            $branch->mRecipients->each(function ($recipient_record) {
                $recipient_record->delete();
            });

            // 支所マスター削除
            $this->repository->deleteBranch($branch);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $branch->id, $branch->name);

        return [$error_flag, $message];
    }

    /**
     * Excelダウンロード
     *
     * @param $search_condition_input_data
     * @return array
     */
    public function downloadExcel($search_condition_input_data): array
    {
        $filename = config('consts.excel.filename.branches');
        $headings = [
            '得意先名',
            '支所名',
            '支所名略称',
        ];

        $branches = $this->repository->getSearchResult($search_condition_input_data);

        $filters = [
            /** 得意先名 */
            function ($branches) {
                return $branches->customer_name;
            },
            /** 支所名 */
            function ($branches) {
                return $branches->branch_name;
            },
            /** 支所名略称 */
            function ($branches) {
                return $branches->mnemonic_name;
            },
        ];

        return [$branches, $filename, $headings, $filters];
    }
}
