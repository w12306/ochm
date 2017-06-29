{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection
	
{{-- 内容 --}}
@section('content')
	<script>
        $.extend(ST.ACTION,{
            executionDetail:"{{route('admin.executive.api.executive-detail',[$id,$showother])}}" //获取执行单详情数据
        });
    </script>
	<link  rel="stylesheet" media="screen" href="/modules/lib/handsontable/handsontable.css">
    <script  src="/modules/lib/handsontable/pikaday/pikaday.js"></script>
    <script  src="/modules/lib/handsontable/moment/moment.js"></script>
    <script  src="/modules/lib/handsontable/zeroclipboard/ZeroClipboard.js"></script>
    <script  src="/modules/lib/handsontable/handsontable.js"></script>
    <script  src="/modules/lib/WdatePicker/WdatePicker.js"></script>	
	<div class="container">
			<div class="heading clearfix">
                <div class="pull-left">
                    <h2>执行单排期{{$executive->key}}</h2>
                </div>

                <div class="pull-right">
                   <!-- <a title="导出xls" href="javascript:;" class="btn mr5 ">导出xls</a>-->
                    <a title="显示空白广告位" href="{{route('admin.executive.executive-detail',[$id,1])}}" class="btn mr5 ">显示空白广告位</a>           
                </div>
            </div>
	
			<div id="js-container" class="mt20"></div>
	</div>
 

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

 <script>
     seajs.use('view/execution/detail', function (app) {
        app({
            el: '#js-container',
            tableOnly:false
        });
    })
</script>

@endsection