{{-- 403エラー画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('errors.base')

@section('title', __('アクセスが拒否されました。'))
@section('code', '403')
@section('message', __('アクセスが拒否されました。'))
