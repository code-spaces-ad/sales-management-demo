{{-- 帳票出力ボタンBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@section('report_output_button')
    <div class="col-md-12 px-0">
        <div class="text-center">
            {{-- クリアボタン --}}
            <button type="button" class="btn btn-secondary mr-2" onclick="clearInput();">
                <i class="fas fa-times"></i>
                クリア
            </button>

            {{-- Excelダウンロードボタン --}}
            <a class="btn btn-success mr-2" onclick="downloadReportExcel('{{ $view_settings['download_excel_url'] }}');">
                <i class="fas fa-file-excel"></i> Excel
            </a>

            {{-- PDFダウンロードボタン --}}
            <a class="btn btn-danger" onclick="downloadReportPdf('{{ $view_settings['download_pdf_url'] }}');">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
        </div>
    </div>
@show
