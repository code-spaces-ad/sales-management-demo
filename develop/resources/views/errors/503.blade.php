{{-- 503エラー画面用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@extends('errors.base')

@section('title', __('サービスがご利用できません。'))
@section('code', '503')
@section('message', __('サービスがご利用できません。'))
