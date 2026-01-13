{{-- 行追加・削除ボタン Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}

<div class="d-block w-100 d-md-flex">
    <button type="button"
            class="btn-outline-info btn-group-xs ml-1 createRowBtn"
            style="width: 25px;"
            value="clear"
            title="行を追加します"
            onclick="appendRow(this);">+
    </button>
    <button type="button"
            class="btn-outline-info btn-group-xs ml-1 removeRowBtn"
            style="width: 25px;"
            value="clear"
            title="行を削除します"
            onclick="removeRow(this);">-
    </button>
</div>

<script>
    function appendRow(target) {
        // 最終行のNoを取得
        let table_row_cnt = order_products_table.rows.length;
        let last_row_no = Number(order_products_table.rows[table_row_cnt - 1].cells[0].innerHTML);

        // 追加行のNoを取得
        let new_row_no = last_row_no + 1;
        // 追加行のdetailのidx
        let new_row_detail_idx = new_row_no - 1;

        // 行の複製
        let new_row = $(target).closest('tr').clone().appendTo('#order_products_table tbody');
        order_products_table.rows[table_row_cnt].cells[0].innerHTML = String(new_row_no);

        // 値クリア
        new_row.find('[type=text]').val('');
        new_row.find('[type=number]').val('');
        new_row.find('[type=date]').val('');
        new_row.find('[type=checkbox]').removeAttr('checked').prop('checked', false);

        // 印刷チェックボックス
        new_row.find('.input-delivery-print').attr('name', 'detail[' + new_row_detail_idx + '][delivery_print]');
        new_row.find('.input-delivery-print').attr('id', 'delivery-print-' + new_row_detail_idx);
        new_row.find('.label-delivery-print').attr('for', 'delivery-print-' + new_row_detail_idx);

        // 商品セレクト
        new_row.find('.input-product-select').attr('name', 'detail[' + new_row_detail_idx + '][product_id]');
        new_row.find('.input-product-name').attr('name', 'detail[' + new_row_detail_idx + '][product_name]');
        new_row.find('span').remove();
        new_row.find('.input-product-select').select2({
            width: '100%',
            matcher: function (params, data) {
                return select2Matcher(params, data);
            },
            templateSelection: function (data) {
                return $(data.element).data('code');
            },
        });
        new_row.find('.input-product-select').prop('selectedIndex', 0).change();

        // 数量
        new_row.find('.input-quantity').attr('name', 'detail[' + new_row_detail_idx + '][quantity]');
        new_row.find('.input-quantity-minus').attr('id', 'input-quantity-minus-' + new_row_detail_idx);

        // 単位
        new_row.find('.input-product-unit-name-select').attr('name', 'detail[' + new_row_detail_idx + '][unit_name]');

        // 仕入単価
        new_row.find('.input-unit-price').attr('name', 'detail[' + new_row_detail_idx + '][unit_price]');
        new_row.find('.input-unit-price-purchase').attr('name', 'detail[' + new_row_detail_idx + '][purchase_unit_price]');

        // 粗利
        new_row.find('.input-product-sub-gross').attr('name', 'detail[' + new_row_detail_idx + '][gross_profit]');

        // 税率
        new_row.find('.input-consumption-tax-rate-select').attr('name', 'detail[' + new_row_detail_idx + '][consumption_tax_rate]');

        // 値引き
        new_row.find('.input-discount').attr('name', 'detail[' + new_row_detail_idx + '][discount]');

        // 金額
        new_row.find('.hidden-consumption-tax-rate').attr('name', 'detail[' + new_row_detail_idx + '][consumption_tax_rate]');
        // 単価小数桁数
        new_row.find('.hidden-unit-price-decimal-digit').attr('name', 'detail[' + new_row_detail_idx + '][unit_price_decimal_digit]');
        // 数量小数桁数
        new_row.find('.hidden-quantity-decimal-digit').attr('name', 'detail[' + new_row_detail_idx + '][quantity_decimal_digit]');
        // 税区分
        new_row.find('.hidden-tax-type-id').attr('name', 'detail[' + new_row_detail_idx + '][tax_type_id]');
        // 軽減税率対象フラグ
        new_row.find('.hidden-reduced-tax-flag').attr('name', 'detail[' + new_row_detail_idx + '][reduced_tax_flag]');
        // 税額端数処理
        new_row.find('.hidden-tax-rounding-method-id').attr('name', 'detail[' + new_row_detail_idx + '][tax_rounding_method_id]');
        // 税額
        new_row.find('.hidden-tax').attr('name', 'detail[' + new_row_detail_idx + '][tax]');
        // 金額端数処理
        new_row.find('.hidden-amount-rounding-method-id').attr('name', 'detail[' + new_row_detail_idx + '][amount_rounding_method_id]');

        // 納品日カレンダ
        new_row.find('.input-delivery-date').attr('name', 'detail[' + new_row_detail_idx + '][delivery_date]');
        new_row.find('.input-delivery-date').attr('disabled', false);

        // 倉庫セレクト
        new_row.find('.input-warehouse-name-select').attr('name', 'detail[' + new_row_detail_idx + '][warehouse_id]');
        new_row.find('.input-warehouse-name-select').prop('selectedIndex', 0).change();
        new_row.find('.input-warehouse-name-select').attr('disabled', true);

        // 備考
        new_row.find('.input-note').attr('name', 'detail[' + new_row_detail_idx + '][note]');
        new_row.find('.input-detail-note').attr('name', 'detail[' + new_row_detail_idx + '][note]');

        //　売上チェックボックス
        new_row.find('.input-sales-confirm').attr('name', 'detail[' + new_row_detail_idx + '][sales_confirm]');
        new_row.find('.input-sales-confirm').attr('id', 'sales-confirm-' + new_row_detail_idx);
        new_row.find('.label-sales-confirm').attr('for', 'sales-confirm-' + new_row_detail_idx);

        // ソート
        new_row.find('.input-sort').attr('name', 'detail[' + new_row_detail_idx + '][sort]');
        new_row.find('.input-sort').attr('value', new_row_detail_idx);
    }

    /**
     * 行削除
     * @param target
     */
    function removeRow(target) {
        if (!confirm("指定行を削除します。\r\nよろしいですか。")) {
            return;
        }

        // 削除
        $(target).closest('tr').remove();

        // 対象画面を取得
        let screen_name_id = Number($('#screen_name').val());

        // 受注伝票入力画面
        if (screen_name_id === 1) {
            return;
        }
        // 売上伝票入力画面
        if (screen_name_id === 2) {
            // 商品金額再セット
            setProductPrice(target);
            // 粗利再計算
            setProductGross(target);
            // 各合計値セット
            setTotalAmounts();
            return;
        }
        // 仕入伝票入力画面
        if (screen_name_id === 3) {
            // 商品金額再セット
            setProductPrice(target);
            // 各合計値セット
            setTotalAmounts();
            return;
        }
    }
</script>

