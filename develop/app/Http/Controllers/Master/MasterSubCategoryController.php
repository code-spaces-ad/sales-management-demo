<?php

/**
 * サブカテゴリマスター画面用コントローラー
 *
 * @copyright © 2025 CodeSpaces
 */

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\SubCategoryEditRequest;
use App\Http\Requests\Master\SubCategorySearchRequest;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterSubCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use LogHelper;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * サブカテゴリマスター画面用コントローラー
 */
class MasterSubCategoryController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterSubCategoryController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param SubCategorySearchRequest $request
     * @return View
     */
    public function index(SubCategorySearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目 */
            'search_items' => [
                /** カテゴリーリスト */
                'categories' => MasterCategory::query()->oldest('code')->get(),
            ],
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'sub_categories' => MasterSubCategory::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.sub_categories.index', $data);
    }

    /**
     * Excelダウンロード
     *
     * @param SubCategorySearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(SubCategorySearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.sub_categories');
        $headings = [
            'コード',
            'カテゴリ名',
            'サブカテゴリ名',
            '作成日時',
            '更新日時',
        ];

        $sub_categories = MasterSubCategory::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($sub_categories) {
                return $sub_categories->code_zerofill;
            },
            /** カテゴリ名 */
            function ($sub_categories) {
                return $sub_categories->mCategory->name;
            },
            /** サブカテゴリ名 */
            function ($sub_categories) {
                return $sub_categories->name;
            },
            /** 作成日時 */
            function ($sub_categories) {
                return Carbon::parse($sub_categories->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($sub_categories) {
                return Carbon::parse($sub_categories->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($sub_categories->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.sub_categories.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $sub_categories->exportExcel($filename, $headings, $filters);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterSubCategory();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/sub_categories*', $this->refURLMasterKey());

        return view('master.sub_categories.create_edit', $this->sendDataSubCategory($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param SubCategoryEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(SubCategoryEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $sub_category = new MasterSubCategory();

        try {
            $sub_category->code = $request->code;
            $sub_category->category_id = $request->category_id;
            $sub_category->name = $request->name;

            $sub_category->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $sub_category->code_zero_fill, $sub_category->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.sub_categories.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterSubCategory $sub_category
     * @return View
     */
    public function edit(MasterSubCategory $sub_category): View
    {
        return view('master.sub_categories.create_edit', $this->sendDataSubCategory($sub_category));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param SubCategoryEditRequest $request
     * @param MasterSubCategory $sub_category
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(SubCategoryEditRequest $request, MasterSubCategory $sub_category): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            $sub_category->code = $request->code;
            $sub_category->category_id = $request->category_id;
            $sub_category->name = $request->name;

            $sub_category->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $sub_category->code_zero_fill, $sub_category->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterSubCategory $sub_category
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterSubCategory $sub_category): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($sub_category->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $sub_category->code_zero_fill, $sub_category->name);

            return redirect(route('master.sub_categories.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            $sub_category->delete();   // 論理削除

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $sub_category->code_zero_fill, $sub_category->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.sub_categories.index'))->with(['message' => $message, 'error_flag' => $error_flag]);
    }
}
