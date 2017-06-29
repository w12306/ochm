{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>配置 - ADPS</title>


@endsection

{{-- 内容 --}}
@section('content')

    <h1 class="">
        所有系统配置
    </h1>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        $(document).ready(function () {
            $('.ui.accordion').accordion();
            $('.menu .item').tab();
            $('.ui.dropdown').dropdown();
            $('.ui.checkbox').checkbox();
            $('.ui.radio.checkbox').checkbox();
        });
    </script>

@endsection