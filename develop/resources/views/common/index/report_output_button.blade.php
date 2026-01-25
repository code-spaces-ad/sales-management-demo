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
            <button class="btn btn-success mr-2" onclick="downloadReportExcel('{{ $view_settings['download_excel_url'] }}');">
                <i class="fas fa-file-excel"></i> Excel
            </button>

            {{-- PDFダウンロードボタン --}}
            <button class="btn btn-danger" disabled>
                <i class="fas fa-file-pdf"></i> PDF
            </button>
                <b class="col-form-label" style="color: #e3342f;">※「PDF」メンテナンス中※</b>
        </div>
    </div>
@show
