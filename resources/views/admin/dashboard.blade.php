{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection

{{-- 内容 --}}
@section('content')

    <span>ABMP广告业务管理后台欢迎您！</span>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        $(document).ready(function () {

        });
    </script>

@endsection