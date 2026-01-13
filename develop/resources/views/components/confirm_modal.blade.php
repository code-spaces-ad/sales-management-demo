{{-- 確認用モーダルBlade --}}
{{-- @copyright © 2025 CodeSpaces --}}

@php
    $label_btn_ok = 'OK';               // OKボタン用ラベル
    $label_btn_cancel = 'キャンセル';    // キャンセルボタン用ラベル
@endphp

{{-- Confirm Modal --}}
<div class="modal fade" id="{{ $modal_id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">確認</h5>
            </div>
            <div class="modal-body">
                {{-- 確認用メッセージ --}}
                {!! nl2br(e($confirm_message)) !!}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btn_ok_{{ $modal_id }}"
                        onclick="{{ $onclick_btn_ok }}">{{ $label_btn_ok }}</button>
                <button type="button" class="btn btn-secondary send_cancel"
                        data-dismiss="modal">{{ $label_btn_cancel }}</button>
            </div>
        </div>
    </div>
</div>

<script>
    /**
     * ロードイベントに追加
     */
    window.addEventListener('load', function () {
        // 確認用モーダル表示時に、OKボタンにフォーカス
        $('#{{ $modal_id }}').on('shown.bs.modal', function () {
            $('#btn_ok_{{ $modal_id }}').focus();
        });
    });
</script>
