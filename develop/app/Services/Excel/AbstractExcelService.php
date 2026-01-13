<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Services\Excel;

use App\Helpers\PdfHelper;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class AbstractExcelService
{
    protected string $downloadExcelFileName;

    protected string $downloadPdfFileName;

    protected Spreadsheet $spreadSheet;

    public function __construct(string $downloadExcelFileName, string $downloadPdfFileName)
    {
        $this->downloadExcelFileName = $downloadExcelFileName;
        $this->downloadPdfFileName = $downloadPdfFileName;
    }

    /**
     * Excelダウンロード
     *
     * @return StreamedResponse
     */
    public function downloadExcel(): StreamedResponse
    {
        $spreadSheet = $this->spreadSheet;

        // Output
        $callback = function () use ($spreadSheet) {
            $writer = new Xlsx($spreadSheet);
            $writer->save('php://output');
        };

        $status = 200;
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment;filename="' . $this->downloadExcelFileName . '"',
            'Cache-Control' => 'max-age=0',
        ];

        return new StreamedResponse($callback, $status, $headers);
    }

    /**
     * PDFダウンロード
     *
     * @return string
     *
     * @throws Exception
     */
    public function makePdf(): string
    {
        // 一旦、Excelファイルを保存
        $excelPath = storage_path(config('consts.excel.temp_path')) . $this->downloadExcelFileName;
        $writer = new Xlsx($this->spreadSheet);
        $writer->save($excelPath);

        // Excel -> PDF 変換
        $pdfDir = public_path(config('consts.pdf.temp_path'));
        if (PdfHelper::convertPdf($excelPath, $pdfDir) !== 0) {
            throw new Exception('PDFの生成に失敗');
        }

        return '/' . config('consts.pdf.temp_path') . $this->downloadPdfFileName;
    }

    /**
     * テンプレート準備
     *
     * @param string $excelTemplatePath
     * @param string $orientation
     * @return Worksheet
     */
    protected function initSpreadSheet(
        string $excelTemplatePath,
        string $orientation = PageSetup::ORIENTATION_PORTRAIT
    ): Worksheet {
        // テンプレートファイルの読み込み
        $this->spreadSheet = IOFactory::load($excelTemplatePath);

        // setup page
        $activeSheet = $this->spreadSheet->getActiveSheet();
        $activeSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $activeSheet->getPageSetup()->setOrientation($orientation);
        $activeSheet->getPageSetup()->setHorizontalCentered(true);
        $activeSheet->getPageSetup()->setFitToWidth(1);

        return $activeSheet;
    }

    /**
     * @return Worksheet
     */
    protected function getActiveSheet(): Worksheet
    {
        return $this->spreadSheet->getActiveSheet();
    }

    /**
     * @param string $sheetName
     * @return Worksheet
     *
     * @throws Exception
     */
    protected function copyTemplateSheet(string $sheetName): Worksheet
    {
        $clonedWorksheet = clone $this->spreadSheet->getSheet(0);
        $clonedWorksheet->setTitle($sheetName, false);

        return $this->spreadSheet->addSheet($clonedWorksheet); //
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    protected function removeTemplateSheet(): void
    {
        // 先頭のシートを削する
        $this->spreadSheet->removeSheetByIndex(0);
        // 先頭のシートをアクティブにする
        $this->spreadSheet->setActiveSheetIndex(0);
    }

    /**
     * @param Worksheet $activeSheet
     * @param string $coordinate
     * @return void
     *
     * @throws Exception
     */
    protected function setSelectedDefaultCell(Worksheet $activeSheet, string $coordinate = 'A1'): void
    {
        $activeSheet->setSelectedCells($coordinate);
    }
}
