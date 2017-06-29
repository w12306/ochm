{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}--出错了</title>

@endsection

{{-- 内容 --}}
@section('content')
    <div class="error">
        <h2 class="tac">出错了</h2>
        <div class="tac p20">
            <i class="error-icon"></i> {{ $message }}
        </div>
    </div>


@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script type="text/javascript">
        $(function () {

        });
    </script>
@endsection