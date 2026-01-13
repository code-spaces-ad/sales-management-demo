<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Models\Master\MasterHeadOfficeInfo;
use Carbon\Carbon;
use Exception;

/**
 * 日付用ヘルパークラス
 */
class DateHelper
{
    /**
     * 和歴の日付を取得（例：令和3年11月30日）
     *
     * @param $value
     * @return array|string
     */
    public static function getFullJpDate($value)
    {
        $result = DateHelper::chgAdToJpDate($value);
        if (!empty($result)) {
            $result = $result[0] . $result[2] . '年' . $result[3] . '月' . $result[4] . '日';
        }

        return $result;
    }

    /**
     * 和歴の日付を取得（例：R3/11/30）
     *
     * @param $value
     * @return array|string
     */
    public static function getFullShortJpDate($value)
    {
        $result = DateHelper::chgAdToJpDate($value);
        if (!empty($result)) {
            $result = $result[1] . $result[2] . '/' . $result[3] . '/' . $result[4];
        }

        return $result;
    }

    /**
     * 和暦変換後に年を取得
     *   (グレゴリオ暦が採用された「明治6年1月1日」以降に対応)
     *
     * 引数：西暦(9999/99/99 or 9999-99-99)
     * 戻値：和暦
     */
    public static function chgAdToJpDate($value)
    {
        // 和暦変換用データ
        $arr = [
            ['date' => '2019-05-01', 'year' => '2019', 'name' => '令和', 'short_name' => 'R'], // 新元号追加
            ['date' => '1989-01-08', 'year' => '1989', 'name' => '平成', 'short_name' => 'H'],
            ['date' => '1926-12-25', 'year' => '1926', 'name' => '昭和', 'short_name' => 'S'],
            ['date' => '1912-07-30', 'year' => '1912', 'name' => '大正', 'short_name' => 'T'],
            ['date' => '1873-01-01', 'year' => '1868', 'name' => '明治', 'short_name' => 'M'], // 明治6年1月1日以降
        ];
        // 日付チェック
        if (DateHelper::chkDate($value) === false) {
            return '';
        }
        $arrad = explode('-', str_replace('/', '-', $value));
        $addate = (int) sprintf('%d%02d%02d', (int) $arrad[0], (int) $arrad[1], (int) $arrad[2]);
        $result = '';
        foreach ($arr as $key => $row) {
            // 日付チェック
            if (DateHelper::chkDate($row['date']) === false) {
                return '';
            }
            $arrjp = explode('-', str_replace('/', '-', $row['date']));
            $jpdate = (int) sprintf('%d%02d%02d', (int) $arrjp[0], (int) $arrjp[1], (int) $arrjp[2]);
            // 元号の開始日と比較
            if ($addate >= $jpdate) {
                // 和暦年の計算
                $year = sprintf('%d', ((int) $arrad[0] - (int) $row['year']) + 1);
                if ((int) $year === 1) {
                    $year = '元';
                }

                $result = [$row['name'], $row['short_name'], $year, (int) $arrad[1], (int) $arrad[2]];
                break;
            }
        }

        return $result;
    }

    /**
     * 日付チェック
     *
     * 引数：西暦(9999/99/99 or 9999-99-99)
     * 戻値：結果
     */
    public static function chkDate($value)
    {
        if ((strpos($value, '/') !== false) && (strpos($value, '-') !== false)) {
            return false;
        }
        $value = str_replace('/', '-', $value);
        $pattern = '#^([0-9]{1,4})-(0[1-9]|1[0-2]|[1-9])-([0-2][0-9]|3[0-1]|[1-9])$#';
        preg_match($pattern, $value, $arrmatch);
        if ((isset($arrmatch[1]) === false) || (isset($arrmatch[2]) === false) || (isset($arrmatch[3]) === false)) {
            return false;
        }
        if (checkdate((int) $arrmatch[2], (int) $arrmatch[3], (int) $arrmatch[1]) === false) {
            return false;
        }

        return true;
    }

    /**
     * 会計年度の範囲取得
     *
     * @param string $value
     * @return array
     */
    public static function getFiscalYearRange(string $value): array
    {
        $fiscal_year = MasterHeadOfficeInfo::getFiscalYear();

        $start_date = Carbon::parse($value . '-' . $fiscal_year)->toDateString();

        $end_date = Carbon::parse($start_date)
            ->settings(['yearOverflow' => false])
            ->subDay()
            ->addYear()
            ->toDateString();

        return [$start_date, $end_date];
    }

    /**
     * 古い日付と新しい日付を元に末尾に'年度'を付与した「年」リストを取得
     * 例: $oldest = '2023-02-21', $latest = '2024-04-01'
     *    出力 [2022 => '2022年度', 2023 => '2023年度', 2024 => '2024年度'] ※会計年度が4月の場合
     *
     * @param string $oldest
     * @param string $latest
     * @return array
     */
    public static function getFiscalYearsListByOldestAndLatestDate(string $oldest, string $latest): array
    {
        // 売上日付を元に「年」のリストを取得
        $fiscal_years = range(self::getFiscalYear($oldest), self::getFiscalYear($latest));

        // 配列のkeyとvalueが同じリストに変換し、$valueは末尾に'年度'を付与したリストに変換
        return array_map(function ($year) {
            return $year . '年度';
        }, array_combine($fiscal_years, $fiscal_years));
    }

    /**
     * 対象日付から会計年度を取得
     *
     * @param string $date
     * @return string
     */
    public static function getFiscalYear(string $date): string
    {
        // 期首月
        $fiscal_year = MasterHeadOfficeInfo::getFiscalYear();

        $date = new Carbon($date);

        // 期首月の1日を取得
        $start_date = new Carbon($date->format('Y') . '-' . $fiscal_year . '-01');

        // 対象日付が期首月より前の場合、前年を会計年度としてreturn
        if ($date < $start_date) {
            return $date->format('Y') - 1;
        }

        return $date->format('Y');
    }

    /**
     * 日付形式を変更
     *
     * @param string|null $date
     * @param string $format
     * @return string
     */
    public static function changeDateFormat(?string $date, string $format = 'Y-m-d'): string
    {
        // Carbonの変換が出来ない場合、今日の日付をセット
        try {
            $date = new Carbon($date);
        } catch (Exception $e) {
            $date = '';
        }

        return $date ? $date->format($format) : '';
    }

    /**
     * 対象の日付が過去月か判定
     *
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function isLessThanThisMonth(string $date, string $format = 'Ym'): bool
    {
        $closing_ym = self::changeDateFormat($date, $format);

        // 対象日付(月)と今月を比較し、対象日付が過去月だったら、true
        if (Carbon::parse($closing_ym)->lt(Carbon::parse(now()->format('Ym')))) {
            return true;
        }

        return false;
    }

    /**
     * 対象月(YYYY-mm)から月初を取得する
     *
     * @param $yearMonth
     * @return string
     */
    public static function getMonthStart($yearMonth): string
    {
        try {
            $date = new Carbon($yearMonth);

            return $date->format('Y-m-01');
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 対象月(YYYY-mm)から月末を取得する
     *
     * @param $yearMonth
     * @return string
     */
    public static function getMonthEnd($yearMonth): string
    {
        try {
            $date = new Carbon($yearMonth);

            return $date->format('Y-m-t');
        } catch (Exception $e) {
            return '';
        }
    }
}
