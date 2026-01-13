{{-- 500エラー画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('errors.base')

@section('title', __('サーバエラーが発生しました。'))
@section('code', '500')
@section('message', __('サーバエラーが発生しました。'))
