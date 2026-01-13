{{-- 429エラー画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('errors.base')

@section('title', __('リクエストが制限値を超えました。'))
@section('code', '429')
@section('message', __('リクエストが制限値を超えました。'))
