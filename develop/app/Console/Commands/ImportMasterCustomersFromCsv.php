<?php

namespace App\Console\Commands;

use App\Enums\SalesInvoiceFormatType;
use App\Enums\SalesInvoicePrintingMethod;
use App\Helpers\ConvertHelper;
use App\Models\Master\MasterCustomer;
use App\Models\Master\MasterCustomerHonorificTitle;
use App\Models\Master\MasterEmployee;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * CSVパターン
 * ---------------------------------------------------------------------------------------------------------------------
 * 得意先[    100]-[ 200000] ＊＊ 得意先ﾌﾟﾙｰﾌﾘｽﾄ ＊＊ DATE : 2024/11/16(13:44),,,,,,,,,,,,,,,,,,,,,,,,,,,,,
 * 得意先CD,得意先名ｶﾅ,得意先名,得意先(略),郵便番号,住所1,住所2,E-mail,備考,電話番号,FAX番号,担当者CD,担当者名,請求先CD,売上区分,税区分(売掛),税端数(売掛),税区分(現金),税端数(現金),締ｸﾞﾙｰﾌﾟ,合計請求書,請求明細書,入金予定日,入金方法,単価管理,単価ｸﾞﾙｰﾌﾟ,掛率,金額端数,登録日,最終更新日
 * 101,アケボノソウサイ,有限会社　あけぼの葬祭,有限会社 あけぼの葬,893-0015,鹿屋市新川町891-2,,,,,,10,岩川,101,1 売掛,1 締時一括,3 切り上げ,2 伝票単位,3 切り上げ,31,印刷する,印刷する,0ヶ月後 0日払,1 現金,1 個別単価,,,1,2017/1/31,2024/1/24
 * 102,ソウゴウソウサイアリゾノ,有限会社　総合葬祭有園,有限会社　総合葬祭有,899-8102,曽於市大隅町下窪町152,,,,,,10,岩川,102,1 売掛,1 締時一括,3 切り上げ,2 伝票単位,3 切り上げ,31,印刷する,印刷する,1ヶ月後31日払,3 振込,1 個別単価,,,2,2000/5/23,2024/1/24
 * ・・・データ分ある
 */
class ImportMasterCustomersFromCsv extends Command
{
    protected $signature = 'import:master-customers {file?} {truncate?}';

    protected $description = 'CSVから得意先をbulk insertする';

    public function handle(): int
    {
        if (!$this->confirm('得意先マスタのCSV取込みを実行しますか？')) {
            return self::INVALID;
        }

        // truncate
        $truncate = $this->argument('truncate') === 'true' ? true : false;
        if ($truncate) {
            // master_customers
            $this->info('truncate master_customers : start');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MasterCustomer::query()->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('truncate master_customers : finished');
            // master_customers_honorific_titles
            $this->info('truncate master_customers_honorific_titles : start');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MasterCustomerHonorificTitle::query()->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('truncate master_customers_honorific_titles : finished');
        }

        // CSV取り込み
        $this->info('import master_customers : start');
        $fileName = $this->argument('file') ?? 'master-customers.csv';
        $filePath = storage_path("app/import/{$fileName}");

        if (!file_exists($filePath)) {
            $this->error("import master_customers : file not found: {$filePath}");

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

            [$collection_month, $collection_day] = ConvertHelper::convertCollectionDate($record['入金予定日']);

            $record['郵便番号'] = str_replace('‐', '-', $record['郵便番号']);

            $tax_calc_type_id = $record['税区分(売掛)'];
            $tax_rounding_method_id = $record['税端数(売掛)'];
            if ($record['売上区分'] === '2') {
                $tax_calc_type_id = $record['税区分(現金)'];
                $tax_rounding_method_id = $record['税端数(現金)'];
            }

            //            $record['得意先名ｶﾅ'] = mb_convert_kana($record['得意先名ｶﾅ'], 'K');
            //            $record['得意先名ｶﾅ'] = mb_convert_kana($record['得意先名ｶﾅ'], 'c');

            $data[] = [
                'code' => $record['得意先CD'],
                'summary_group_id' => null,
                'employee_id' => MasterEmployee::query()->where('code', $record['担当者CD'])->value('id') ?? null,
                'name_kana' => $record['得意先名ｶﾅ'],
                'name' => $record['得意先名'],
                'postal_code1' => explode('-', $record['郵便番号'])[0] ?? null,
                'postal_code2' => explode('-', $record['郵便番号'])[1] ?? null,
                'address1' => preg_replace('/^[ 　]+|[ 　]+$/u', '', $record['住所1']),
                'address2' => preg_replace('/^[ 　]+|[ 　]+$/u', '', $record['住所2']),
                'tel_number' => ConvertHelper::convertTelNumber($record['電話番号']),
                'fax_number' => ConvertHelper::convertTelNumber($record['FAX番号']),
                'email' => $record['E-mail'],
                'billing_customer_id' => $record['請求先CD'],
                'department_id' => null,
                'office_facilities_id' => null,
                'tax_calc_type_id' => ConvertHelper::convertTaxCalcTypeId($tax_calc_type_id),
                'tax_rounding_method_id' => ConvertHelper::convertTaxRoundingMethodId($tax_rounding_method_id),
                'transaction_type_id' => ConvertHelper::convertTransactionId($record['売上区分']),
                'closing_date' => ConvertHelper::convertClosingDate($record['締ｸﾞﾙｰﾌﾟ']),
                'start_account_receivable_balance' => 0,
                'sort_code' => $record['得意先CD'],
                'billing_balance' => 0,
                'collection_month' => $collection_month,
                'collection_day' => $collection_day,
                'collection_method' => ConvertHelper::convertCollectionMethod($record['入金方法']),
                'sales_invoice_format_type' => SalesInvoiceFormatType::NO_MIRROR,
                'sales_invoice_printing_method' => SalesInvoicePrintingMethod::HORIZONTAL,
                'note' => $record['備考'],

                'created_at' => $now->format('Y/m/d H:i:s'),
                'updated_at' => $now->format('Y/m/d H:i:s'),
            ];
            ++$count;
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // 得意先マスタ
            DB::transaction(function () use ($data) {
                foreach (array_chunk($data, 1000) as $chunk) {
                    echo '.';
                    MasterCustomer::query()->insert($chunk);
                }
            });

            $now = Carbon::now();
            $count = 0;
            $customers = MasterCustomer::all();
            foreach ($customers as $customer) {
                if ($count === 10) {
                    $now = $now->addSecond();
                    $count = 0;
                }

                //　請求先コードから得意先ID 逆引き更新
                if (!empty($customer->billing_customer_id)) {
                    echo '.';
                    $customer->billing_customer_id = MasterCustomer::query()
                        ->where('code', $customer->billing_customer_id)
                        ->value('id');
                    $customer->save();
                    $customer->updated_at = $now->format('Y/m/d H:i:s');
                    $customer->save();
                }

                //　敬称
                MasterCustomerHonorificTitle::query()->insert([
                    'customer_id' => $customer->id,
                    'honorific_title_id' => 1,
                    'created_at' => $now->format('Y/m/d H:i:s'),
                    'updated_at' => $now->format('Y/m/d H:i:s'),
                ]);
                echo '.';
                ++$count;
            }
            echo PHP_EOL;

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            echo $exception->getMessage();
            $this->error('import master_customers : error');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('import master_customers : finished');

        return self::SUCCESS;
    }
}
