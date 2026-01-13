<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Helpers;

use App\Enums\CollectionMonth;
use App\Enums\DepositMethodType;
use App\Enums\RoundingMethodType;
use App\Enums\TransactionType;
use App\Models\Master\MasterConsumptionTax;
use TaxType;

/**
 * 変換用ヘルパークラス
 */
class ConvertHelper
{
    /**
     * @param string $data
     * @return int
     */
    public static function convertTaxCalcTypeId(string $data): int
    {
        $explode_id = explode(' ', $data)[0];

        // 該当しない場合はその間登録するが基本NG
        return intval($explode_id);
    }

    /**
     * @param string $data
     * @return string
     */
    public static function convertTelNumber(string $data): string
    {
        // 半角変換+半角スペース除去
        $convert = mb_strtolower($data);
        $convert = str_replace('－', '-', $convert);

        return str_replace(' ', '', $convert);
    }

    /**
     * @param string $data
     * @return int
     */
    public static function convertTaxRoundingMethodId(string $data): int
    {
        $explode_id = explode(' ', $data)[0];
        if ($explode_id === strval(RoundingMethodType::ROUND_OFF)) {
            return RoundingMethodType::ROUND_OFF;
        }
        if ($explode_id === strval(RoundingMethodType::ROUND_DOWN)) {
            return RoundingMethodType::ROUND_DOWN;
        }
        if ($explode_id === strval(RoundingMethodType::ROUND_UP)) {
            return RoundingMethodType::ROUND_UP;
        }

        // 該当しない場合はその間登録するが基本NG
        return intval($explode_id);
    }

    /**
     * @param string $data
     * @return int
     */
    public static function convertTransactionId(string $data): int
    {
        $explode_id = explode(' ', $data)[0];
        if ($explode_id === strval(TransactionType::ON_ACCOUNT)) {
            return TransactionType::ON_ACCOUNT;
        }
        if ($explode_id === strval(TransactionType::WITH_CASH)) {
            return TransactionType::WITH_CASH;
        }

        // 該当しない場合はその間登録するが基本NG
        return $explode_id;
    }

    /**
     * @param string $data
     * @return int
     */
    public static function convertClosingDate(string $data): int
    {
        if ($data === '31' || $data === '') {
            return 0;
        }

        // 該当しない場合はその間登録するが基本NG
        return intval($data);
    }

    /**
     * @param string $data
     * @return array
     */
    public static function convertCollectionDate(string $data): array
    {
        $month = null;
        $day = null;
        if (preg_match('/(\d+)ヶ月後\s*(\d+)日払/', $data, $matches)) {
            // 回収月
            $month = (int) $matches[1];
            if ((int) $matches[1] === 0) {
                $month = CollectionMonth::THIS_MONTH;
            }
            if ((int) $matches[1] === 1) {
                $month = CollectionMonth::NEXT_MONTH;
            }
            if ((int) $matches[1] === 2) {
                $month = CollectionMonth::TWO_MONTHS_LATER;
            }
            if ((int) $matches[1] === 3) {
                $month = CollectionMonth::THREE_MONTHS_LATER;
            }
            if ((int) $matches[1] === 4) {
                $month = CollectionMonth::FOUR_MONTHS_LATER;
            }
            if ((int) $matches[1] === 5) {
                $month = CollectionMonth::FIVE_MONTHS_LATER;
            }
            if ((int) $matches[1] === 6) {
                $month = CollectionMonth::SIX_MONTHS_LATER;
            }

            // 回収日
            $day = (int) $matches[2];
            if ((int) $matches[1] <= 0 || (int) $matches[1] > 31) {
                $day = 1;
            }
        }

        return [$month, $day];
    }

    /**
     * @param string $data
     * @return int
     */
    public static function convertCollectionMethod(string $data): int
    {
        $explode_id = explode(' ', $data)[0];
        if ($explode_id === strval(DepositMethodType::CASH)) {
            return DepositMethodType::CASH;
        }
        if ($explode_id === strval(DepositMethodType::CHECK)) {
            return DepositMethodType::CHECK;
        }
        if ($explode_id === strval(DepositMethodType::TRANSFER)) {
            return DepositMethodType::TRANSFER;
        }
        if ($explode_id === strval(DepositMethodType::BILL)) {
            return DepositMethodType::BILL;
        }

        // 5〜8 がない？
        if ($explode_id >= strval(DepositMethodType::OFFSET) && $explode_id <= strval(DepositMethodType::OTHER)) {
            echo 'not convert CollectionMethod:' . $explode_id;
        }

        if ($explode_id === '9') {
            return DepositMethodType::OTHER;
        }

        // 該当しない場合はその間登録するが基本NG
        return intval($explode_id);
    }

    /**
     * @param string $data
     * @return int
     */
    public static function convertUnitPriceDigit(string $data): int
    {
        $float = (float) $data;

        if (floor($float) == $float) {
            return 0;
        }

        $decimalPart = explode('.', (string) $float)[1] ?? '';

        return strlen($decimalPart);
    }

    /**
     * @param string|null $data
     * @return float
     */
    public static function convertPriceValue(?string $data): float
    {
        if (is_null($data)) {
            return 0;
        }

        $data = trim($data);

        if ($data === '' || $data == 0) {
            return 0;
        }

        return $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public static function convertExponentialToString(string $data): string
    {
        if (stripos($data, 'e') === false) {
            return $data;
        }

        return rtrim(number_format((float) $data, 99, '.', ''), '0');
    }

    /**
     * @param $tax_type
     * @param $reduced
     * @return array
     */
    public static function convertTaxData($tax_type, $reduced): array
    {
        $tax_type_id = 1;
        $consumption_tax_rate = 0;
        $reduced_tax_flag = 0;

        // 課税区分
        $explode_tax_type_id = explode(' ', $tax_type)[0];
        if ($explode_tax_type_id === strval(TaxType::OUT_TAX)) {
            $tax_type_id = TaxType::OUT_TAX;
        }
        if ($explode_tax_type_id === strval(TaxType::IN_TAX)) {
            $tax_type_id = TaxType::IN_TAX;
        }
        if ($explode_tax_type_id === strval(TaxType::TAX_EXEMPT)) {
            $tax_type_id = TaxType::TAX_EXEMPT;
        }

        // 軽減税率区分
        $tax_rate_list = MasterConsumptionTax::getTaxValueList();
        $explode_reduced_id = explode(' ', $reduced)[0];
        if ($explode_reduced_id === '0') {
            $consumption_tax_rate = $tax_rate_list['normal_tax_rate'];
        }
        if ($explode_reduced_id === '1') {
            $consumption_tax_rate = $tax_rate_list['reduced_tax_rate'];
            $reduced_tax_flag = 1;
        }

        return [$tax_type_id, $consumption_tax_rate, $reduced_tax_flag];
    }
}
