<?php

namespace App\Console\Commands;

use App\Helpers\ConvertHelper;
use App\Models\Master\MasterAccountingCode;
use App\Models\Master\MasterCategory;
use App\Models\Master\MasterClassification1;
use App\Models\Master\MasterClassification2;
use App\Models\Master\MasterKind;
use App\Models\Master\MasterProduct;
use App\Models\Master\MasterProductUnit;
use App\Models\Master\MasterSection;
use App\Models\Master\MasterSubCategory;
use App\Models\Master\MasterSupplier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Log;

/**
 * CSVパターン
 * ---------------------------------------------------------------------------------------------------------------------
 * 商品CD,商品名ｶﾅ,商品名,商品名(略),商品名入力,相手先商品番号,JANｺｰﾄﾞ,大分類,中分類,課税区分,在庫管理,入荷残管理,売上残管理,入数,単位,標準在庫数,標準原価,定価,登録日,最終更新日,経理ｺｰﾄﾞ,科目名,仕入先ｺｰﾄﾞ,仕入先名,備考,仕様,種別コード,種別名,管理部署コード,管理部署名,棚番,品　名,軽減税率区分,平均売値,単重,分類１CD,分類１名,分類２CD,分類２名,分類３CD,分類３名,分類４CD,分類４名
 * 150101,チョクバイ,煎茶R03-A,煎茶15-1,入力する,箱13002,, 15 仏事,  1 お茶,1 外税,管理する,管理する,管理する,,,,239.95,900,2000/6/2,2024/12/7,     ,,       ,,直売用,緑(薫)50g、箱13002,1,製品,1,卸課,1-1-3-3,直売用会葬品,1 軽減税率,,,,,,,,,,
 * 150110,ｾﾝﾁｬ C-15,煎茶　Ｃ-15,煎茶 C-15,入力する,,, 15 仏事,  1 お茶,1 外税,管理する,管理する,管理する,,,,305.16,1500,2007/8/22,2025/1/25,     ,,       ,,,,   ,,   ,,,,0 通常,,,,,,,,,,
 * ・・・データ分ある
 */
class ImportMasterProductsFromCsv extends Command
{
    protected $signature = 'import:master-products {file?} {truncate?}';

    protected $description = 'CSVから商品をbulk insertする';

    public function handle(): int
    {
        if (!$this->confirm('商品マスタのCSV取込みを実行しますか？')) {
            return self::INVALID;
        }

        // truncate
        $truncate = $this->argument('truncate') === 'true' ? true : false;
        if ($truncate) {
            // master_products
            $this->info('truncate master_products : start');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MasterProduct::query()->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('truncate master_products : finished');
            // master_products_units
            $this->info('truncate master_products_units : start');
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            MasterProductUnit::query()->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info('truncate master_products_units : finished');
        }

        // CSV取り込み
        $this->info('import master_products : start');
        $fileName = $this->argument('file') ?? 'master-products.csv';
        $filePath = storage_path("app/import/{$fileName}");

        if (!file_exists($filePath)) {
            $this->error("import master_products : file not found: {$filePath}");

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

            [$tax_type_id, $consumption_tax_rate, $reduced_tax_flag] = ConvertHelper::convertTaxData($record['課税区分'], $record['軽減税率区分']);

            //            $record['商品名ｶﾅ'] = mb_convert_kana($record['商品名ｶﾅ'], 'K');
            //            $record['商品名ｶﾅ'] = mb_convert_kana($record['商品名ｶﾅ'], 'c');

            $data[] = [
                'code' => $record['商品CD'],
                'name_kana' => $record['商品名ｶﾅ'],
                'name' => $record['商品名'],
                'customer_product_code' => $record['相手先商品番号'],
                'jan_code' => ConvertHelper::convertExponentialToString($record['JANｺｰﾄﾞ']),
                'category_id' => MasterCategory::query()->where('code', $record['大分類'])->value('id'),
                'sub_category_id' => MasterSubCategory::query()
                    ->where('code', $record['中分類'])
                    ->where('category_id', MasterCategory::query()
                        ->where('code', $record['大分類'])
                        ->value('id')
                    )
                    ->value('id'),
                'unit_price' => ConvertHelper::convertPriceValue($record['定価']),
                'tax_type_id' => $tax_type_id,
                'purchase_unit_price' => ConvertHelper::convertPriceValue($record['標準原価']),
                'reduced_tax_flag' => $reduced_tax_flag,
                'unit_price_decimal_digit' => ConvertHelper::convertUnitPriceDigit($record['定価']),
                'quantity_decimal_digit' => 0,      // 固定
                'quantity_rounding_method_id' => 2, // 固定 切上げ
                'amount_rounding_method_id' => 2,   // 固定 切上げ
                'accounting_code_id' => MasterAccountingCode::query()->where('code', $record['経理ｺｰﾄﾞ'])->value('id'),
                'supplier_id' => MasterSupplier::query()->where('code', $record['仕入先ｺｰﾄﾞ'])->value('id'),
                'note' => $record['備考'],
                'specification' => $record['仕様'],
                'kind_id' => MasterKind::query()->where('code', $record['種別コード'])->value('id'),
                'section_id' => MasterSection::query()->where('code', $record['管理部署コード'])->value('id'),
                'rack_address' => $record['棚番'],
                'item_name' => $record['品　名'],
                'purchase_unit_weight' => floatval($record['単重']) ?? 0,
                'classification1_id' => MasterClassification1::query()->where('code', $record['分類１CD'])->value('id'),
                'classification2_id' => MasterClassification2::query()->where('code', $record['分類２CD'])->value('id'),
                'product_status' => null,
                'created_at' => $now->format('Y/m/d H:i:s'),
                'updated_at' => $now->format('Y/m/d H:i:s'),
            ];
            ++$count;
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            // 商品マスタ
            DB::transaction(function () use ($data) {
                foreach (array_chunk($data, 1000) as $chunk) {
                    echo '.';
                    MasterProduct::query()->insert($chunk);
                }
            });

            $products = MasterProduct::all();
            foreach ($products as $product) {
                //　単位
                MasterProductUnit::query()->insert([
                    'product_id' => $product->id,
                    'unit_id' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                echo '.';
            }
            echo PHP_EOL;

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
            echo $exception->getMessage();
            $this->error('import master_products : error');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('import master_products : finished');

        return self::SUCCESS;
    }
}
