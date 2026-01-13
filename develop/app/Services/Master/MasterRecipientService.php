<?php

/**
 * 納品先マスター用サービス
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Master;

use App\Consts\SessionConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Http\Requests\Master\RecipientEditRequest;
use App\Models\Master\MasterBranch;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterRecipient;
use App\Repositories\Master\RecipientRepository;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 納品先マスター用サービス
 */
class MasterRecipientService
{
    use SessionConst;

    protected RecipientRepository $repository;

    /**
     * リポジトリをインスタンス
     *
     * @param RecipientRepository $repository
     */
    public function __construct(RecipientRepository $repository)
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
                'branches' => MasterBranch::query()->oldest('id')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $input_data,
            /** 検索結果 */
            'search_result' => [
                'recipients' => $this->repository->getSearchResultPagenate($input_data),
            ],
        ];
    }

    /**
     * 新規登録画面
     *
     * @param MasterRecipient $target_data
     * @return array
     */
    public function create(MasterRecipient $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 得意先マスター */
                'branches' => MasterBranch::query()->oldest('id')->get(),
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
     * @param RecipientEditRequest $request
     * @return array
     *
     * @throws Exception
     */
    public function store(RecipientEditRequest $request): array
    {
        $error_flag = false;
        $recipient_id = MasterRecipient::withTrashed()->max('id') + 1;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'name' => $request->recipient_name,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 納品先を登録
            $recipient = $this->repository->createRecipient($request->input());

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            $recipient = MasterRecipient::query()
                ->firstOrNew([
                    'name' => $request->recipient_name,
                ]);
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $recipient_id, $recipient->name);

        return [$error_flag, $message];
    }

    /**
     * 編集画面
     *
     * @param MasterRecipient $target_data
     * @return array
     */
    public function edit(MasterRecipient $target_data): array
    {
        return [
            /** 入力項目 */
            'input_items' => [
                /** 得意先マスター */
                'branches' => MasterBranch::query()->get(),
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
     * @param RecipientEditRequest $request
     * @param MasterRecipient $recipient
     * @return array
     *
     * @throws Exception
     */
    public function update(RecipientEditRequest $request, MasterRecipient $recipient): array
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // リクエストキーのデフォルトセット
            $default_values = [
                'name' => $request->recipient_name,
            ];
            foreach ($default_values as $key => $value) {
                $request->filled($key) ?: $request->merge([$key => $value]);
            }

            // 納品先を登録
            $recipient = $this->repository->updateRecipient($recipient, $request->input());

            $recipient->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $recipient->id, $recipient->name);

        return [$error_flag, $message];
    }

    /**
     * 削除処理
     *
     * @param MasterRecipient $recipient
     * @return array
     *
     * @throws Exception
     */
    public function destroy(MasterRecipient $recipient): array
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($recipient->use_master) {
            $error_flag = true;
            $message = MessageHelper::getMasterDestroyMessage(true, $recipient->code_zero_fill, $recipient->name);

            return [$error_flag, $message];
        }

        DB::beginTransaction();

        try {
            $this->repository->deleteRecipient($recipient);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;
            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $recipient->id, $recipient->name);

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
        $filename = config('consts.excel.filename.recipients');
        $headings = [
            '得意先名',
            '支所名',
            '納品先名',
        ];

        $recipients = $this->repository->getSearchResult($search_condition_input_data);
        $filters = [
            /** 得意先名 */
            function ($recipients) {
                return $recipients->customer_name;
            },
            /** 支所名 */
            function ($recipients) {
                return $recipients->branch_name;
            },
            /** 納品先名 */
            function ($recipients) {
                return $recipients->recipient_name;
            },
        ];

        return [$recipients, $filename, $headings, $filters];
    }
}
