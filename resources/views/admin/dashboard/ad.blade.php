{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>配置 - ABMP</title>


@endsection

{{-- 内容 --}}
@section('content')

    <h1 class="ui header">
        广告管理
        <span class="sub header"></span>
    </h1>

    <div class="ui divider"></div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>

    </script>
@endsection