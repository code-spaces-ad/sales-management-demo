<?php

namespace App\Http\Controllers\Master;

use App\Consts\DB\Master\CommonConst;
use App\Helpers\LogHelper;
use App\Helpers\MessageHelper;
use App\Helpers\SessionHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Define\SendDataTrait;
use App\Http\Requests\Master\CategoryEditRequest;
use App\Http\Requests\Master\CategorySearchRequest;
use App\Models\Master\MasterCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterCategoryController extends Controller
{
    /** 入力項目トレイト */
    use SendDataTrait;

    /**
     * MasterProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Show the application dashboard.
     *
     * @param CategorySearchRequest $request
     * @return View
     */
    public function index(CategorySearchRequest $request): View
    {
        $search_condition_input_data = $request->validated();
        Session::put($this->refURLMasterKey(), URL::full());

        $data = [
            /** 検索項目入力データ */
            'search_condition_input_data' => $search_condition_input_data,
            /** 検索結果 */
            'search_result' => [
                'categories' => MasterCategory::getSearchResultPagenate($search_condition_input_data),
            ],
        ];

        return view('master.categories.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $target_record_data = new MasterCategory();
        $target_record_data->code = CommonConst::CODE_INITIAL_VALUE;

        SessionHelper::forgetSessionForMismatchURL('*master/categories*', $this->refURLMasterKey());

        return view('master.categories.create_edit', $this->sendDataCategory($target_record_data));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CategoryEditRequest $request
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function store(CategoryEditRequest $request): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        $category = new MasterCategory();

        try {
            // カテゴリーマスター
            $category->code = $request->code;
            $category->name = $request->name;

            $category->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(), config('consts.message.common.store_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterStoreMessage($error_flag, $category->code_zero_fill, $category->name);

        // 一覧画面へリダイレクト
        return redirect(route('master.categories.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param MasterCategory $category
     * @return View
     */
    public function edit(MasterCategory $category): View
    {
        return view('master.categories.create_edit', $this->sendDataCategory($category));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CategoryEditRequest $request
     * @param MasterCategory $category
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function update(CategoryEditRequest $request, MasterCategory $category): RedirectResponse
    {
        $error_flag = false;

        DB::beginTransaction();

        try {
            // カテゴリーマスター
            $category->code = $request->code;
            $category->name = $request->name;

            $category->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.update_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterUpdateMessage($error_flag, $category->code_zero_fill, $category->name);

        // 一覧画面へリダイレクト
        return redirect(Session::get('reference_url.master_url'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param MasterCategory $category
     * @return RedirectResponse
     *
     * @throws Exception
     */
    public function destroy(MasterCategory $category): RedirectResponse
    {
        $error_flag = false;

        // マスタが使用されていた場合、削除せずリダイレクト
        if ($category->use_master) {
            $message = MessageHelper::getMasterDestroyMessage(true, $category->code_zero_fill, $category->name);

            return redirect(route('master.categories.index'))->with(['message' => $message, 'error_flag' => true]);
        }

        DB::beginTransaction();

        try {
            // サブカテゴリーマスターを削除
            $category->mSubCategories->each(function ($sub_category_record) {
                $sub_category_record->delete();
            });

            // カテゴリーマスター削除
            $category->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            $error_flag = true;

            LogHelper::error(__CLASS__, $e->getMessage(),
                config('consts.message.common.destroy_failed'));
        }

        // フラッシュメッセージ取得
        $message = MessageHelper::getMasterDestroyMessage($error_flag, $category->code_zero_fill, $category->name);

        return redirect(route('master.categories.index'))
            ->with(['message' => $message, 'error_flag' => $error_flag]);
    }

    /**
     * Excelダウンロード
     *
     * @param CategorySearchRequest $request
     * @return RedirectResponse
     * @return StreamedResponse
     */
    public function downloadExcel(CategorySearchRequest $request): RedirectResponse|StreamedResponse
    {
        $search_condition_input_data = $request->validated();

        $filename = config('consts.excel.filename.categories');
        $headings = [
            'コード',
            'カテゴリー名',
            '作成日時',
            '更新日時',
        ];

        $categories = MasterCategory::getSearchResult($search_condition_input_data);

        $filters = [
            /** コード（０埋め） */
            function ($categories) {
                return $categories->code_zerofill;
            },
            /** 名前 */
            function ($categories) {
                return $categories->name;
            },
            /** 作成日時 */
            function ($categories) {
                return Carbon::parse($categories->created_at)->format('Y/m/d H:i:s');
            },
            /** 更新日時 */
            function ($categories) {
                return Carbon::parse($categories->updated_at)->format('Y/m/d H:i:s');
            },
        ];

        if ($categories->isEmpty()) {
            // マスターデータ 0件の場合、一覧画面へリダイレクト
            return redirect(route('master.categories.index'))
                ->with(['message' => config('consts.message.error.E0000001'), 'error_flag' => true]);
        }

        return $categories->exportExcel($filename, $headings, $filters);
    }
}
