<?php

/**
 * メッセージ関連定数
 *
 * @copyright © 2025 CodeSpaces
 */

return [
    /** 共通 */
    'common' => [
        /** 登録成功メッセージ */
        'store_success' => '登録に成功しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '編集に成功しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '編集に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '削除に成功しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '削除に失敗しました。',
        /** PDF表示失敗メッセージ */
        'show_pdf_failed' => 'PDF表示に失敗しました。',

        /** 確認用（※ダイアログ上で使用） */
        'confirm' => [
            /** 登録時メッセージ */
            'store' => "登録します。\r\nよろしいですか。",
            /** 削除時メッセージ */
            'delete' => "削除します。\r\nよろしいですか。",
            /** 複製時メッセージ */
            'copy' => "複製します。\r\nよろしいですか。\r\n※現在の入力情報で新規登録を行います。",
            /** 更新メッセージ */
            'update' => "更新します。\r\nよろしいですか。",
            /** クリア時メッセージ */
            'clear' => "現在の入力情報をクリアします。\r\nよろしいですか。",
            /** Excel出力時メッセージ */
            'excel' => "Excel出力を実行します。\r\nよろしいですか。",
            /** PDF出力時メッセージ */
            'pdf' => "PDF出力を実行します。\r\nよろしいですか。",
            /** 売上確定時メッセージ */
            'orders_received' => "売上確定します。\r\nよろしいですか。",
        ],

        /** 警告用 */
        'alert' => [
            /** 数字警告メッセージ */
            'number' => '数字を入力して下さい。',
        ],
    ],
    /** 受注伝票 */
    'received' => [
        /** 登録成功メッセージ */
        'store_success' => '受注伝票「**order_number」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '受注伝票「**order_number」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '受注伝票「**order_number」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '受注伝票「**order_number」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '受注伝票「**order_number」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '受注伝票「**order_number」の削除に失敗しました。',
    ],
    /** 売上伝票 */
    'order' => [
        /** 登録成功メッセージ */
        'store_success' => '売上伝票「**order_number」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '売上伝票「**order_number」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '売上伝票「**order_number」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '売上伝票「**order_number」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '売上伝票「**order_number」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '売上伝票「**order_number」の削除に失敗しました。',
    ],
    /** 入金伝票 */
    'deposit' => [
        /** 登録成功メッセージ */
        'store_success' => '入金伝票「**order_number」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '入金伝票「**order_number」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '入金伝票「**order_number」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '入金伝票「**order_number」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '入金伝票「**order_number」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '入金伝票「**order_number」の削除に失敗しました。',
    ],
    /** 仕入伝票 */
    'purchase_order' => [
        /** 登録成功メッセージ */
        'store_success' => '仕入伝票「**order_number」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '仕入伝票「**order_number」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '仕入伝票「**order_number」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '仕入伝票「**order_number」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '仕入伝票「**order_number」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '仕入伝票「**order_number」の削除に失敗しました。',
    ],
    /** 支払伝票 */
    'payment' => [
        /** 登録成功メッセージ */
        'store_success' => '支払伝票「**order_number」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '支払伝票「**order_number」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '支払伝票「**order_number」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '支払伝票「**order_number」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '支払伝票「**order_number」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '支払伝票「**order_number」の削除に失敗しました。',
    ],
    /** 請求締処理 */
    'charge_closing' => [
        /** 処理成功メッセージ */
        'store_success' => '締処理が完了しました。',
        /** 処理失敗メッセージ */
        'store_failed' => '締処理に失敗しました。',
        /** 解除成功メッセージ */
        'cancel_success' => '締処理の解除が完了しました。',
        /** 解除失敗メッセージ */
        'cancel_failed' => '締処理の解除に失敗しました。',

        'confirm' => [
            /** 登録時メッセージ */
            'store' => "請求締処理を実行します。\r\nよろしいですか。", // \r\n※締未処理の請求先が対象となります。
            /** 解除時メッセージ */
            'cancel' => "請求締処理を解除します。\r\nよろしいですか。", // \r\n※締処理済の請求先が対象となります。
        ],
    ],
    /** 仕入締処理 */
    'purchase_closing' => [
        /** 処理成功メッセージ */
        'store_success' => '締処理が完了しました。',
        /** 処理失敗メッセージ */
        'store_failed' => '締処理に失敗しました。',
        /** 解除成功メッセージ */
        'cancel_success' => '締処理の解除が完了しました。',
        /** 解除失敗メッセージ */
        'cancel_failed' => '締処理の解除に失敗しました。',

        'confirm' => [
            /** 登録時メッセージ */
            'store' => "仕入締処理を実行します。\r\nよろしいですか。", // \r\n※締未処理の請求先が対象となります。
            /** 解除時メッセージ */
            'cancel' => "仕入締処理を解除します。\r\nよろしいですか。", // \r\n※締処理済の請求先が対象となります。
        ],
    ],
    /** マスター */
    'master' => [
        /** 登録成功メッセージ */
        'store_success' => '「**code:**name」を登録しました。',
        /** 登録失敗メッセージ */
        'store_failed' => '「**code:**name」の登録に失敗しました。',
        /** 更新成功メッセージ */
        'update_success' => '「**code:**name」を更新しました。',
        /** 更新失敗メッセージ */
        'update_failed' => '「**code:**name」の更新に失敗しました。',
        /** 削除成功メッセージ */
        'destroy_success' => '「**code:**name」を削除しました。',
        /** 削除失敗メッセージ */
        'destroy_failed' => '「**code:**name」の削除に失敗しました。',
    ],
    /** エラー系 */
    'error' => [
        'E0000001' => '印刷対象データは存在しません。',
        'E0000002' => 'PDF表示に失敗しました。',
    ],
];
