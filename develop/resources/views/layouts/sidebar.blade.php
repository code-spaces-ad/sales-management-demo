{{-- サイドバー用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@section('sidebar')

    @php
        $sidebar_active = [
            'dashboard' => '',
//            'receive' => '',
            'sale' => '',
            'sale-ledger' => '',
            'invoice' => '',
            'trading' => '',
            'trading-ledger' => '',
            'purchase_invoice' => '',
            'inventory' => '',
            'master' => '',
            'data_transfer' => '',
            'system' => '',
            'report_output' => '',
            'sale-report' => '',
            'trading-report' => '',
        ];
        $sidebar_display = [
            'dashboard' => '',
//            'receive' => '',
            'sale' => '',
            'sale-ledger' => '',
            'invoice' => '',
            'trading' => '',
            'trading-ledger' => '',
            'purchase_invoice' => '',
            'inventory' => '',
            'master' => '',
            'data_transfer' => '',
            'system' => '',
            'report_output' => '',
            'sale-report' => '',
            'trading-report' => '',
        ];

        $class_active = 'active';
        $style_display = 'display: block;';

        // ダッシュボードページ
        if (Request::is('dashboard/*')) {
            $sidebar_active['dashboard'] = $class_active;
            $sidebar_display['dashboard'] = $style_display;
        }

//        // 受注管理ページ
//        if (Request::is('receive/*')) {
//            $sidebar_active['receive'] = $class_active;
//            $sidebar_display['receive'] = $style_display;
//        }

        // 売上管理ページ
        if (Request::is('sale/*')) {
            if (Request::is('sale/ledger/*')) {
                $sidebar_active['sale-ledger'] = $class_active;
                $sidebar_display['sale-ledger'] = $style_display;
            }

            $sidebar_active['sale'] = $class_active;
            $sidebar_display['sale'] = $style_display;
        }

        // 請求処理ページ
        if (Request::is('invoice/*')) {
            $sidebar_active['invoice'] = $class_active;
            $sidebar_display['invoice'] = $style_display;
        }

        // 仕入処理ページ
        if (Request::is('trading/*')) {
            if (Request::is('trading/ledger/*')) {
                $sidebar_active['trading-ledger'] = $class_active;
                $sidebar_display['trading-ledger'] = $style_display;
            }

            $sidebar_active['trading'] = $class_active;
            $sidebar_display['trading'] = $style_display;
        }

        // 仕入締処理処理ページ
        if (Request::is('purchase_invoice/*')) {
            $sidebar_active['purchase_invoice'] = $class_active;
            $sidebar_display['purchase_invoice'] = $style_display;
        }

        // 在庫管理ページ
        if (Request::is('inventory/*')) {
            $sidebar_active['inventory'] = $class_active;
            $sidebar_display['inventory'] = $style_display;
        }

        // 帳票管理ページ
        if (Request::is('report_output/*')) {
            if (Request::is('report_output/sale/*')) {
                $sidebar_active['sale-report'] = $class_active;
                $sidebar_display['sale-report'] = $style_display;
            }
            if (Request::is('report_output/trading/*')) {
                $sidebar_active['trading-report'] = $class_active;
                $sidebar_display['trading-report'] = $style_display;
            }
            $sidebar_active['report_output'] = $class_active;
            $sidebar_display['report_output'] = $style_display;
        }

        // マスター管理ページ
        if (Request::is('master/*')) {
            $sidebar_active['master'] = $class_active;
            $sidebar_display['master'] = $style_display;
        }

        // POSデータ連携ページ
        if (Request::is('data_transfer/*')) {
            $sidebar_active['data_transfer'] = $class_active;
            $sidebar_display['data_transfer'] = $style_display;
        }

        // システム設定ページ
        if (Request::is('system/*')) {
            $sidebar_active['system'] = $class_active;
            $sidebar_display['system'] = $style_display;
        }

        /** @see HeadOfficeInfoConst::COMPANY_ID */
        $company_id = HeadOfficeInfoConst::COMPANY_ID;    // 自社ID
    @endphp

    <a id="show-sidebar" class="btn btn-sm btn-dark">
        <i class="fas fa-bars"></i>
    </a>

    <nav id="sidebar" class="sidebar-wrapper">
        <div class="sidebar-content"
             @if (config("app.env") === "staging") style="background-color: brown" @endif>
            <div class="sidebar-brand">
                <a href="{{ url('/') }}">
                    {{-- システム名 --}}
                    <div>{{ config('app.name') }}</div>
                    {{-- 会社名 --}}
                    <div>{{ config('app.company_name') }}</div>
                </a>
                <div id="close-sidebar">
                    {{-- サイドバー閉じるボタン --}}
                    <i class="fas fa-times"></i>
                </div>
            </div>

            {{-- ※クリックイベント処理は、sidebar.js で対応 --}}
            <div class="sidebar-menu">
                <ul>
                    {{-- ダッシュボード画面にアクセス権限があるユーザーのみ表示 --}}
                    <li>
                        <a class="@if (Request::is('dashboard')) active @endif"
                           href="{{ route('dashboard.index') }}">
                            <span>ダッシュボード</span>
                        </a>
                    </li>

{{--                    --}}{{-- 受注管理 --}}
{{--                    <li class="sidebar-dropdown {{ $sidebar_active['receive'] }}">--}}
{{--                        <a class="header-menu">--}}
{{--                            <span>受注管理</span>--}}
{{--                        </a>--}}
{{--                        <div class="sidebar-submenu" style="{{ $sidebar_display['receive'] }}">--}}
{{--                            <ul>--}}
{{--                                <li>--}}
{{--                                    <a class="@if (Request::is('receive/orders_received/create')) active @endif"--}}
{{--                                       href="{{ route('receive.orders_received.create') }}">--}}
{{--                                        --}}{{-- 受注伝票入力 --}}
{{--                                        <span>{{ config('consts.title.receive.menu.create') }}</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                                <li>--}}
{{--                                    <a class="@if (Request::is('receive/orders_received')) active @endif"--}}
{{--                                       href="{{ route('receive.orders_received.index') }}">--}}
{{--                                        --}}{{-- 受注伝票一覧 --}}
{{--                                        <span>{{ config('consts.title.receive.menu.index') }}</span>--}}
{{--                                    </a>--}}
{{--                                </li>--}}
{{--                            </ul>--}}
{{--                        </div>--}}
{{--                    </li>--}}

                    {{-- 売上管理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['sale'] }}">
                        <a class="header-menu">
                            <span>売上管理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['sale'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('sale/orders/create')) active @endif"
                                       href="{{ route('sale.orders.create') }}">
                                        {{-- 売上伝票入力 --}}
                                        <span>{{ config('consts.title.sale.menu.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('sale/orders')) active @endif"
                                       href="{{ route('sale.orders.index') }}">
                                        {{-- 売上伝票一覧 --}}
                                        <span>{{ config('consts.title.sale.menu.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('sale/deposits/create')) active @endif"
                                       href="{{ route('sale.deposits.create') }}">
                                        {{-- 入金伝票入力 --}}
                                        <span>{{ config('consts.title.sale.menu.deposit.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('sale/deposits')) active @endif"
                                       href="{{ route('sale.deposits.index') }}">
                                        {{-- 入金伝票一覧 --}}
                                        <span>{{ config('consts.title.sale.menu.deposit.index') }}</span>
                                    </a>
                                </li>
{{--                                <li class="sidebar-dropdown-2 {{ $sidebar_active['sale-ledger'] }}">--}}
{{--                                    <a>--}}
{{--                                        <span>各種帳票出力</span>--}}
{{--                                    </a>--}}
{{--                                    <div class="sidebar-submenu-2" style="{{ $sidebar_display['sale-ledger'] }}">--}}
{{--                                        <ul>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/customers')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.customers') }}">--}}
{{--                                                    --}}{{-- 得意先元帳 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.customers') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/accounts_receivable_balance')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.accounts_receivable_balance') }}">--}}
{{--                                                    --}}{{-- 売掛台帳 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.accounts_receivable_balance') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/products')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.products') }}">--}}
{{--                                                    --}}{{-- 商品台帳 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.products') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/categories')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.categories') }}">--}}
{{--                                                    --}}{{-- 種別累計売上表 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.categories') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/fiscal_year')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.fiscal_year') }}">--}}
{{--                                                    --}}{{-- 年度別販売実績表 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.fiscal_year') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/sales_products')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.sales_products') }}">--}}
{{--                                                    --}}{{-- 商品別売上表 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.sales_products') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/sales_customers')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.sales_customers') }}">--}}
{{--                                                    --}}{{-- 得意先別売上表 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.sales_customers') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('sale/ledger/deposits')) active @endif"--}}
{{--                                                   href="{{ route('sale.ledger.deposits') }}">--}}
{{--                                                    --}}{{-- 金種別入金一覧表 --}}
{{--                                                    <span>{{ config('consts.title.sale.menu.sale_ledger_submenu.deposits') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </li>--}}
                            </ul>
                        </div>
                    </li>

                    {{-- 請求処理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['invoice'] }}">
                        <a class="header-menu">
                            <span>売上締処理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['invoice'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('invoice/charge/index') || Request::is('invoice/charge_detail/index')) active @endif"
                                       href="{{ route('invoice.charge.index') }}">
                                        {{-- 請求一覧 --}}
                                        <span>{{ config('consts.title.charge.menu.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('invoice/charge_closing')) active @endif"
                                       href="{{ route('invoice.charge_closing.index') }}">
                                        {{-- 請求締処理 --}}
                                        <span>{{ config('consts.title.charge.menu.closing') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('invoice/invoice_print/index')) active @endif"
                                       href="{{ route('invoice.invoice_print.index') }}">
                                        {{-- 請求書発行 --}}
                                        <span>{{ config('consts.title.charge.menu.invoice_print') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- 仕入処理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['trading'] }}">
                        <a class="header-menu">
                            <span>仕入処理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['trading'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('trading/purchase_orders/create')) active @endif"
                                       href="{{ route('trading.purchase_orders.create') }}">
                                        {{-- 仕入伝票入力 --}}
                                        <span>{{ config('consts.title.trading.menu.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('trading/purchase_orders')) active @endif"
                                       href="{{ route('trading.purchase_orders.index') }}">
                                        {{-- 仕入伝票一覧 --}}
                                        <span>{{ config('consts.title.trading.menu.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('trading/payments/create')) active @endif"
                                       href="{{ route('trading.payments.create') }}">
                                        {{-- 支払伝票入力 --}}
                                        <span>{{ config('consts.title.trading.menu.payment.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('trading/payments')) active @endif"
                                       href="{{ route('trading.payments.index') }}">
                                        {{-- 支払伝票一覧 --}}
                                        <span>{{ config('consts.title.trading.menu.payment.index') }}</span>
                                    </a>
                                </li>
{{--                                <li class="sidebar-dropdown-2 {{ $sidebar_active['trading-ledger'] }}">--}}
{{--                                    <a>--}}
{{--                                        <span>各種帳票出力</span>--}}
{{--                                    </a>--}}
{{--                                    <div class="sidebar-submenu-2" style="{{ $sidebar_display['trading-ledger'] }}">--}}
{{--                                        <ul>--}}
{{--                                            <li>--}}
{{--                                                <a class="@if (Request::is('trading/ledger/purchase_orders_sd')) active @endif"--}}
{{--                                                   href="{{ route('trading.ledger.purchase_orders_sd.index') }}">--}}
{{--                                                    --}}{{-- 仕入台帳 --}}
{{--                                                    <span>{{ config('consts.title.trading.menu.orders_sd') }}</span>--}}
{{--                                                </a>--}}
{{--                                            </li>--}}
{{--                                        </ul>--}}
{{--                                    </div>--}}
{{--                                </li>--}}
                            </ul>
                        </div>
                    </li>

                    {{-- 仕入締処理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['purchase_invoice'] }}">
                        <a class="header-menu">
                            <span>仕入締処理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['purchase_invoice'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('purchase_invoice/purchase_closing_list/index') ||
                                        Request::is('purchase_invoice/purchase_closing_detail/index')) active @endif"
                                       href="{{ route('purchase_invoice.purchase_closing_list.index') }}">
                                        {{-- 仕入締一覧 --}}
                                        <span>{{ config('consts.title.trading.menu.closing.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('purchase_invoice/purchase_closing')) active @endif"
                                       href="{{ route('purchase_invoice.purchase_closing.index') }}">
                                        {{-- 仕入締処理 --}}
                                        <span>{{ config('consts.title.trading.menu.closing.closing') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- 在庫処理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['inventory'] }}">
                        <a class="header-menu">
                            <span>在庫処理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['inventory'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('inventory/inventory_datas/create')) active @endif"
                                       href="{{ route('inventory.inventory_datas.create') }}">
                                        {{-- 在庫データ入力 --}}
                                        <span>{{ config('consts.title.inventory.menu.create') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('inventory/inventory_datas')) active @endif"
                                       href="{{ route('inventory.inventory_datas.index') }}">
                                        {{-- 在庫データ一覧 --}}
                                        <span>{{ config('consts.title.inventory.menu.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('inventory/products_moving')) active @endif"
                                       href="{{ route('inventory.products_moving.index') }}">
                                        {{-- 商品残数一覧 --}}
                                        <span>{{ config('consts.title.inventory.menu.products_moving') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('inventory/stocks')) active @endif"
                                       href="{{ route('inventory.stocks.index') }}">
                                        {{-- 在庫確認 --}}
                                        <span>{{ config('consts.title.inventory.menu.stock') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('inventory/inventory_stock_datas')) active @endif"
                                       href="{{ route('inventory.inventory_stock_datas.index') }}">
                                        {{-- 在庫調整 --}}
                                        <span>{{ config('consts.title.inventory.menu.stock_datas.index') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- 帳票管理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['report_output'] }}">
                        <a class="header-menu">
                            <span>帳票管理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['report_output'] }}">
                            <ul>
                                <li class="sidebar-dropdown-2 {{ $sidebar_active['sale-report'] }}">
                                    <a>
                                        <span>売上・請求 帳票出力</span>
                                    </a>
                                    <div class="sidebar-submenu-2" style="{{ $sidebar_display['sale-report'] }}">
                                        <ul>
                                            {{-- CodeSpaces用 帳票 --}}
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/store_shipping_fee_breakdown')) active @endif"
                                                   href="{{ route('report_output.sale.store_shipping_fee_breakdown.index') }}">
                                                    {{-- 各店送料内訳 --}}
                                                    <span>{{ config('consts.title.report_output.menu.store_shipping_fee_breakdown') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/sales_detail_list')) active @endif"
                                                   href="{{ route('report_output.sale.sales_detail_list.index') }}">
                                                    {{-- 売上明細一覧(売上日指定) --}}
                                                    <span>{{ config('consts.title.report_output.menu.sales_detail_list') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/summary_sales_by_customer_product_day')) active @endif"
                                                   href="{{ route('report_output.sale.summary_sales_by_customer_product_day.index') }}">
                                                    {{-- 得意先別商品別日別売上集計表 --}}
                                                    <span>{{ config('consts.title.report_output.menu.summary_sales_by_customer_product_day') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/bank_transfer_fee')) active @endif"
                                                   href="{{ route('report_output.sale.bank_transfer_fee.index') }}">
                                                    {{-- 振込手数料 --}}
                                                    <span>{{ config('consts.title.report_output.menu.bank_transfer_fee') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/accounts_receivable_balance_list')) active @endif"
                                                   href="{{ route('report_output.sale.accounts_receivable_balance_list.index') }}">
                                                    {{-- 売掛残高一覧 --}}
                                                    <span>{{ config('consts.title.report_output.menu.accounts_receivable_balance_list') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/accounts_receivable_balance_list_by_tax_rate')) active @endif"
                                                   href="{{ route('report_output.sale.accounts_receivable_balance_list_by_tax_rate.index') }}">
                                                    {{-- 売掛残高一覧(税率ごと) --}}
                                                    <span>{{ config('consts.title.report_output.menu.accounts_receivable_balance_list_by_tax_rate') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/customer_ledger')) active @endif"
                                                   href="{{ route('report_output.sale.customer_ledger.index') }}">
                                                    {{-- 得意先元帳 --}}
                                                    <span>{{ config('consts.title.report_output.menu.customer_ledger') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/sale/customer_ledger_by_employee')) active @endif"
                                                   href="{{ route('report_output.sale.customer_ledger_by_employee.index') }}">
                                                    {{-- 得意先元帳(担当者別) --}}
                                                    <span>{{ config('consts.title.report_output.menu.customer_ledger_by_employee') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="sidebar-dropdown-2 {{ $sidebar_active['trading-report'] }}">
                                    <a>
                                        <span>仕入・支払 帳票出力</span>
                                    </a>
                                    <div class="sidebar-submenu-2" style="{{ $sidebar_display['trading-report'] }}">
                                        <ul>
                                            <li>
                                                <a class="@if (Request::is('report_output/trading/accounts_payable_list_by_expense_code')) active @endif"
                                                   href="{{ route('report_output.trading.accounts_payable_list_by_expense_code.index') }}">
                                                    {{-- 経費コード別買掛金一覧 --}}
                                                    <span>{{ config('consts.title.report_output.menu.accounts_payable_list_by_expense_code') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/trading/purchase_details_list')) active @endif"
                                                   href="{{ route('report_output.trading.purchase_details_list.index') }}">
                                                    {{-- 仕入明細一覧(入荷日指定) --}}
                                                    <span>{{ config('consts.title.report_output.menu.purchase_details_list') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/trading/accounts_payable_increase_decrease_table')) active @endif"
                                                   href="{{ route('report_output.trading.accounts_payable_increase_decrease_table.index') }}">
                                                    {{-- 買掛金増減表 --}}
                                                    <span>{{ config('consts.title.report_output.menu.accounts_payable_increase_decrease_table') }}</span>
                                                </a>
                                            </li>
                                            <li>
                                                <a class="@if (Request::is('report_output/trading/supplier_ledger')) active @endif"
                                                   href="{{ route('report_output.trading.supplier_ledger.index') }}">
                                                    {{-- 仕入先元帳(締間)軽減 --}}
                                                    <span>{{ config('consts.title.report_output.menu.supplier_ledger') }}</span>
                                                </a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                                <li>
                                    <a class="@if (Request::is('report_output/deposit_slip_inquiry_transfer_fee')) active @endif"
                                       href="{{ route('report_output.deposit_slip_inquiry_transfer_fee.index') }}">
                                        {{-- 入金伝票問い合わせ振込手数料 --}}
                                        <span>{{ config('consts.title.report_output.menu.deposit_slip_inquiry_transfer_fee') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('report_output/journal')) active @endif"
                                       href="{{ route('report_output.journal.index') }}">
                                        {{-- 仕訳帳 --}}
                                        <span>{{ config('consts.title.report_output.menu.journal') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('report_output/accounts_receivable_journal')) active @endif"
                                       href="{{ route('report_output.accounts_receivable_journal.index') }}">
                                        {{-- 売掛金仕訳帳CSV出力軽減 --}}
                                        <span>{{ config('consts.title.report_output.menu.accounts_receivable_journal') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('report_output/monthly_sales_book')) active @endif"
                                       href="{{ route('report_output.monthly_sales_book.index') }}">
                                        {{-- 月間商品売上簿(金額あり商品CDあり) --}}
                                        <span>{{ config('consts.title.report_output.menu.monthly_sales_book') }}</span>
                                    </a>
                                </li>

                            </ul>
                        </div>
                    </li>

                    {{-- マスター管理 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['master'] }}">
                        <a class="header-menu">
                            <span>マスター管理</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['master'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('master/accounting_codes*')) active @endif"
                                       href="{{ route('master.accounting_codes.index') }}">
                                        {{-- 経理コードマスター --}}
                                        <span>{{ config('consts.title.master.menu.accounting_codes.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/customers*')) active @endif"
                                       href="{{ route('master.customers.index') }}">
                                        {{-- 得意先マスター --}}
                                        <span>{{ config('consts.title.master.menu.customers.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/branches*')) active @endif pl-4"
                                       href="{{ route('master.branches.index') }}">
                                        {{-- 支所マスター --}}
                                        <span>┗{{ config('consts.title.master.menu.branches.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/recipients*')) active @endif pl-4"
                                       href="{{route('master.recipients.index')}}">
                                        {{-- 納品先マスター --}}
                                        <span>&nbsp;&nbsp;┗{{ config('consts.title.master.menu.recipients.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/suppliers*')) active @endif"
                                       href="{{ route('master.suppliers.index') }}">
                                        {{-- 仕入先マスター --}}
                                        <span>{{ config('consts.title.master.menu.suppliers.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/products*')) active @endif"
                                       href="{{ route('master.products.index') }}">
                                        {{-- 商品マスター --}}
                                        <span>{{ config('consts.title.master.menu.products.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/categories*')) active @endif"
                                       href="{{ route('master.categories.index') }}">
                                        {{-- カテゴリーマスター --}}
                                        <span class="font-size-12">┗{{ config('consts.title.master.menu.categories.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/sub_categories*')) active @endif"
                                       href="{{ route('master.sub_categories.index') }}">
                                        {{-- サブカテゴリーマスター --}}
                                        <span class="font-size-12">&nbsp;&nbsp;┗{{ config('consts.title.master.menu.sub_categories.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/departments*')) active @endif"
                                       href="{{ route('master.departments.index') }}">
                                        {{-- 部門マスター --}}
                                        <span>{{ config('consts.title.master.menu.departments.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/office_facilities*')) active @endif"
                                       href="{{ route('master.office_facilities.index') }}">
                                        {{-- 事業所マスター --}}
                                        <span class="font-size-12">┗{{ config('consts.title.master.menu.office_facilities.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/employees*')) active @endif"
                                       href="{{ route('master.employees.index') }}">
                                        {{-- 担当マスター --}}
                                        <span>{{ config('consts.title.master.menu.employees.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/warehouses*')) active @endif"
                                       href="{{ route('master.warehouses.index') }}">
                                        {{-- 倉庫マスター --}}
                                        <span>{{ config('consts.title.master.menu.warehouses.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/kinds*')) active @endif"
                                       href="{{ route('master.kinds.index') }}">
                                        {{-- 種別マスター --}}
                                        <span>{{ config('consts.title.master.menu.kinds.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/sections*')) active @endif"
                                       href="{{ route('master.sections.index') }}">
                                        {{-- 管理部署マスター --}}
                                        <span>{{ config('consts.title.master.menu.sections.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/classifications1*')) active @endif"
                                       href="{{ route('master.classifications1.index') }}">
                                        {{-- 分類1マスター --}}
                                        <span>{{ config('consts.title.master.menu.classifications1.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/classifications2*')) active @endif"
                                       href="{{ route('master.classifications2.index') }}">
                                        {{-- 分類2マスター --}}
                                        <span>{{ config('consts.title.master.menu.classifications2.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/summary_group*')) active @endif"
                                       href="{{ route('master.summary_group.index') }}">
                                        {{-- 集計グループマスター --}}
                                        <span>{{ config('consts.title.master.menu.summary_group.index') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('master/customer_price*')) active @endif"
                                       href="{{ route('master.customer_price.index') }}">
                                        {{-- 得意先別単価マスター --}}
                                        <span>{{ config('consts.title.master.menu.customer_price.index') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- システム設定 --}}
                    <li class="sidebar-dropdown {{ $sidebar_active['system'] }}">
                        <a class="header-menu">
                            <span>システム設定</span>
                        </a>
                        <div class="sidebar-submenu" style="{{ $sidebar_display['system'] }}">
                            <ul>
                                <li>
                                    <a class="@if (Request::is('system/head_office_info*')) active @endif"
                                       href="{{ route('system.head_office_info.edit', $company_id) }}">
                                        {{-- 会社情報設定 --}}
                                        <span>{{ config('consts.title.system.menu.head_office_info') }}</span>
                                    </a>
                                </li>
                                <li>
                                    <a class="@if (Request::is('system/log_operations*')) active @endif"
                                       href="{{ route('system.log_operations.index') }}">
                                        {{-- 操作履歴一覧 --}}
                                        <span>{{ config('consts.title.system.menu.log_operations') }}</span>
                                    </a>
                                </li>
                                <li>
                                    @if(!\App\Helpers\UserHelper::isRoleEmployee())
                                        <a class="@if (Request::is('system/users*')) active @endif"
                                           href="{{ route('system.users.index') }}">
                                            {{-- ユーザーマスター --}}
                                            <span>{{ config('consts.title.system.menu.users.index') }}</span>
                                        </a>
                                    @endif
                                    @if(\App\Helpers\UserHelper::isRoleEmployee())
                                        <a class="@if (Request::is('system/users*')) active @endif"
                                           href="{{ route('system.users.edit', Auth::user()->id) }}">
                                            {{-- ユーザーマスター --}}
                                            <span>{{ config('consts.title.system.menu.users.profile') }}</span>
                                        </a>
                                    @endif
                                </li>
                                @if (auth()->user()->role_id === UserRoleType::SYS_ADMIN)
                                <li>
                                    <a class="@if (Request::is('system/settings*')) active @endif"
                                       href="{{ route('system.settings.index') }}">
                                        {{-- 設定 --}}
                                        <span>{{ config('consts.title.system.menu.settings') }}</span>
                                    </a>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endsection
