<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Master\MasterCustomer;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidDateException;
use Log;

/**
 * 請求締日用ヘルパークラス
 */
class ClosingDateHelper
{
    /**
     * 請求締日のリストを取得
     *
     * @return array
     */
    public static function getClosingDateList(): array
    {
        return config('consts.default.common.closing_date_list');
    }

    /**
     * 回収日のリストを取得
     *
     * @return array
     */
    public static function getCollectionDayList(): array
    {
        $list = [];
        for ($index = 1; $index <= 31; ++$index) {
            $list[$index] = $index;
        }

        return $list;
    }

    /**
     * 指定年月と締日区分から締範囲日付を返す
     *
     * @param string $charge_year_month
     * @param int $closing_date
     * @return array
     */
    public static function getChargeCloseTermDate(string $charge_year_month, int $closing_date): array
    {
        $str_year = explode('-', $charge_year_month)[0];
        $str_month = explode('-', $charge_year_month)[1];

        try {
            // 締日(終了日付）を取得
            if ($closing_date === 0) {
                // 末寺指定の場合
                $start_date = new Carbon($str_year . '-' . $str_month . '-01');
                $end_date = $start_date->copy()->endOfMonth();
            } else {
                // 日付存在チェック
                Carbon::createSafe(intval($str_year), intval($str_month), $closing_date, 0, 0, 0);
                $end_date = new Carbon($str_year . '-' . $str_month . '-' . strval($closing_date));
                $start_date = $end_date->copy()->subMonth()->addDay();
            }
        } catch (InvalidDateException $e) {
            // 例外処理(存在しない日付は末日で返す）※例：2/30
            $start_date = new Carbon($str_year . '-' . $str_month . '-01');
            $end_date = $start_date->copy()->endOfMonth();
        }

        return [$start_date, $end_date->endOfDay()];
    }

    /**
     * 締日年月表示文言
     *
     * @param string $charge_date
     * @param int $closing_date
     * @return string
     */
    public static function getChargeClosingDateDisplay(string $charge_date, int $closing_date): string
    {
        return DateHelper::changeDateFormat($charge_date, 'Y年m月') .
            config('consts.default.common.closing_date_list')[$closing_date] . '日締め';
    }

    /**
     * 残高締日年月表示文言
     *
     * @param string $charge_date
     * @param int $closing_date
     * @return string
     */
    public static function getChargeClosingDateBalanceDisplay(string $charge_date, int $closing_date): string
    {
        return DateHelper::changeDateFormat($charge_date, 'Y年m月') .
            config('consts.default.common.closing_date_list')[$closing_date] . '日残高';
    }

    /**
     * 入金予定日を返す
     *
     * @param Carbon $target_date
     * @param int $customer_id
     * @return Carbon
     */
    public static function getPlannedDate(Carbon $target_date, int $customer_id): Carbon
    {
        $customer = MasterCustomer::find($customer_id);
        $check_date = $target_date->addMonth($customer->collection_month - 1)->copy();
        try {
            Carbon::createSafe(intval($check_date->year), intval($check_date->month), $customer->collection_day,
                0, 0, 0);
            $planned_deposit_at = $check_date->day($customer->collection_day);
        } catch (InvalidDateException $e) {
            // 存在しない場合はその当月の末日を返す
            $planned_deposit_at = $check_date->lastOfMonth();
            Log::warning('InvalidDateException:指定年月の月末をセットして返す:' . $planned_deposit_at->format('Y-m-d'));
        }
        // 時分秒は「00:00:00」としておく
        $planned_deposit_at->setHour(0)->setMinute(0)->setSecond(0);

        return $planned_deposit_at;
    }
}
