<?php

namespace App\Console\Commands;

use App\Enums\SalesInvoiceFormatType;
use App\Enums\SalesInvoicePrintingMethod;
use App\Helpers\ConvertHelper;
use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * CSVパターン
 * ---------------------------------------------------------------------------------------------------------------------
 * 仕入先[   1010]-[ 100000] ＊＊ 仕入先ﾌﾟﾙｰﾌﾘｽﾄ ＊＊ DATE : 2024/11/16
 * 仕入先CD,仕入先名ｶﾅ,仕入先名,仕入先(略),郵便番号,住所1,住所2,E-mail,URL,電話番号,FAX番号,担当者CD,担当者名,支払先CD,税区分(買掛),税端数(買掛),仕入区分,税区分(現金),税端数(現金),締ｸﾞﾙｰﾌﾟ,支払明細書,支払予定日,支払区分,単価管理,単価ｸﾞﾙｰﾌﾟ,掛率,金額端数区分,登録日,最終更新日
 * 1010,セイワ,㈱清和,㈱清和,815-0082,福岡市南区大楠1-22-22,,,,0120-296110,0120-296119,1,堀口(崇),1010,2 伝票単位,2 切り捨て,1 買,2 伝票単位,2 切り捨て,31,印刷する,1ヶ月後25日払,3 振込,1 個別単価,,,1,2000/5/27,2024/11/12
 * 1011,イシカワシコウ,石川紙工㈱,石川紙工㈱,799-0704,愛媛県四国中央市土居町津根3630,,,,0896-74-8080,0896-74-8060,1,堀口(崇),1011,1 締時一括,2 切り捨て,1 買,2 伝票単位,2 切り捨て,20,印刷する,1ヶ月後15日払,3 振込,1 個別単価,,,2,2000/8/21,2024/11/12
 * ・・・データ分ある
 */
class ImportMasterSuppliersFromCsv extends Command
{
    protected $signature = 'import:master-suppliers {file?} {truncate?}';

    protected $description = 'CSVから仕入先をbulk insertする';

    public function handle(): int
    {
        if (!$this->confirm('仕入先マスタのCSV取込みを実行しますか？')) {
            return self::INVALID;
        }

        // truncate
        $truncate = $this->argument('truncate') === 'true' ? true : false;
        if ($truncate) {
            // master_suppliers
            $this->info('truncate master_suppliers : start');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MasterSupplier::query()->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('truncate master_suppliers : finished');
        }

        // CSV取り込み
        $this->info('import master_suppliers : start');
        $fileName = $this->argument('file') ?? 'master-suppliers.csv';
        $filePath = storage_path("app/import/{$fileName}");

        if (!file_exists($filePath)) {
            $this->error("import master_suppliers : file not found: {$filePath}");

            return self::FAILURE;
        }

        $stream = fopen($filePath, 'r');

        // 最初の1行
        $line = fgets($stream);
        // エンコーディング判定
        $encoding = mb_detect_encoding($line, ['UTF-8', 'SJIS-win', 'EUC-JP']);
        // SJIS-winだったらフィルターを付ける
        if ($encoding === 'SJIS-win') {
            stream_filter_append($stream, 'convert.iconv.SJIS-win/UTF-8');
        }
        // 読み戻し
        rewind($stream);

        // 1行目（ヘッダー）を読み飛ばす
        fgetcsv($stream);

        // ヘッダー行を取得（1行目）
        $headers = fgetcsv($stream);

        $data = [];
        $now = Carbon::now();
        $count = 0;
        while (($row = fgetcsv($stream)) !== false) {
            if ($count === 10) {
                $now = $now->addSecond();
                $count = 0;
            }

            $record = array_combine($headers, $row);

            [$collection_month, $collection_day] = ConvertHelper::convertCollectionDate($record['支払予定日']);

            if (strlen($record['郵便番号']) === 7) {
                $record['郵便番号'] = mb_substr($record['郵便番号'], 0, 3) . '-' . mb_substr($record['郵便番号'], 3, 4);
            }
            $record['郵便番号'] = str_replace('‐', '-', $record['郵便番号']);

            $tax_calc_type_id = $record['税区分(買掛)'];
            $tax_rounding_method_id = $record['税端数(買掛)'];
            if ($record['仕入区分'] === '2') {
                $tax_calc_type_id = $record['税区分(現金)'];
                $tax_rounding_method_id = $record['税端数(現金)'];
            }

            //            $record['仕入先名ｶﾅ'] = mb_convert_kana($record['仕入先名ｶﾅ'], 'K');
            //            $record['仕入先名ｶﾅ'] = mb_convert_kana($record['仕入先名ｶﾅ'], 'c');

            $data[] = [
                'code' => $record['仕入先CD'],
                'name_kana' => $record['仕入先名ｶﾅ'],
                'name' => $record['仕入先名'],
                'postal_code1' => explode('-', $record['郵便番号'])[0] ?? null,
                'postal_code2' => explode('-', $record['郵便番号'])[1] ?? null,
                'address1' => preg_replace('/^[ 　]+|[ 　]+$/u', '', $record['住所1']),
                'address2' => preg_replace('/^[ 　]+|[ 　]+$/u', '', $record['住所2']),
                'tel_number' => ConvertHelper::convertTelNumber($record['電話番号']),
                'fax_number' => ConvertHelper::convertTelNumber($record['FAX番号']),
                'email' => $record['E-mail'],
                'tax_calc_type_id' => ConvertHelper::convertTaxCalcTypeId($tax_calc_type_id),
                'tax_rounding_method_id' => ConvertHelper::convertTaxRoundingMethodId($tax_rounding_method_id),
                'transaction_type_id' => ConvertHelper::convertTransactionId($record['仕入区分']),
                'closing_date' => ConvertHelper::convertClosingDate($record['締ｸﾞﾙｰﾌﾟ']),
                'start_account_receivable_balance' => 0,
                'billing_balance' => 0,
                'collection_month' => $collection_month,
                'collection_day' => $collection_day,
                'collection_method' => ConvertHelper::convertCollectionMethod($record['支払区分']),
                'sales_invoice_format_type' => SalesInvoiceFormatType::NO_MIRROR,
                'sales_invoice_printing_method' => SalesInvoicePrintingMethod::HORIZONTAL,
                'note' => null,
                'created_at' => $now->format('Y/m/d H:i:s'),
                'updated_at' => $now->format('Y/m/d H:i:s'),
            ];
            ++$count;
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // 仕入先マスタ
            DB::transaction(function () use ($data) {
                foreach (array_chunk($data, 1000) as $chunk) {
                    echo '.';
                    MasterSupplier::query()->insert($chunk);
                }
            });
            echo PHP_EOL;

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            echo $exception->getMessage();
            $this->error('import master_suppliers : error');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('import master_suppliers : finished');

        return self::SUCCESS;
    }
}
