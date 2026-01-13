{{-- 単価履歴Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="col-md-2">
    <input type="hidden" id="hidden_price_history_product_id">
    <div class="price_history_wrapper" style="position: relative;">
        <div id="price_history_loading" class="loading-overlay" style="display: none;">
            <div class="spinner"></div>
        </div>
        <table id="price_history" class="table table-sm" style="border: transparent">
            <thead>
            <tr>
    {{--            <th class="text-center" style="background:#9ae693">&nbsp;</th>--}}
                <th class="text-center" style="background:#9ae693">日付</th>
                <th class="text-center" style="background:#9ae693">単価</th>
            </tr>
            </thead>
            <tbody>
            <tr>
    {{--            <td class="text-center" style="background:#9ae693">単価1</td>--}}
                <td class="order_date text-center" style="background:white">&nbsp;</td>
                <td class="unit_price text-right" style="background:white">&nbsp;</td>
            </tr>
            <tr>
    {{--            <td class="text-center" style="background:#9ae693">単価2</td>--}}
                <td class="order_date text-center" style="background:white">&nbsp;</td>
                <td class="unit_price text-right" style="background:white">&nbsp;</td>
            </tr>
            <tr>
    {{--            <td class="text-center" style="background:#9ae693">単価3</td>--}}
                <td class="order_date text-center" style="background:white">&nbsp;</td>
                <td class="unit_price text-right" style="background:white">&nbsp;</td>
            </tr>
            <tr>
    {{--            <td class="text-center" style="background:#9ae693">単価4</td>--}}
                <td class="order_date text-center" style="background:white">&nbsp;</td>
                <td class="unit_price text-right" style="background:white">&nbsp;</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
