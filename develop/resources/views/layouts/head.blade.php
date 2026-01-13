{{-- head用Blade --}}
{{-- @copyright © 2025 CodeSpaces --}}
@section('head')
    <meta charset="utf-8">
    <meta name="viewport" content="
        width=device-width,
        user-scalable=no,
        initial-scale=1.0,
        maximum-scale=1.0,
        minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>@section('title') @show</title>
    {{-- X-CSRF-TOKEN --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- laravelmix style --}}
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <link rel="stylesheet" href="{{ mix('css/common.css') }}">
    <link rel="stylesheet" href="{{ mix('css/custom.css') }}">

    {{-- Fonts --}}
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito:200,600">
@endsection
