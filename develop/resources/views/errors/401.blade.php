{{-- 401エラー画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('errors.base')

@section('title', __('認証に失敗しました。'))
@section('code', '401')
@section('message', __('認証に失敗しました。'))
