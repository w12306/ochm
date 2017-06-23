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
            executionDetail:"{{route('admin.executive.api.create-executive',$id)}}", //获取执行单数据
            getPartner:"/authed/business-api/get-partner-select",   //创建业务联动获取合作方
            getProduct:"/authed/business-api/get-product-list",  //创建业务联动获取产品编号
            addConsumerUrl:"/authed/toolbox/create-company",//新增客户url
			area:"{{route('admin.executive.api.area')}}",
            executionAdd:"{{route('admin.executive.store-executive')}}",//编辑新增执行单
			getSoldOut:"{{route('admin.executive.api.get-advertis-status')}}",
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
					<?php if($id>0){
						echo '<h2>排期管理>编辑执行单</h2>';
					}else{
						echo '<h2>排期管理>新建执行单</h2>';
					}?>
						
					</div>
				</div>
	
				<div id="js-container"></div>
	</div>
 

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

 <script>
     seajs.use('view/execution/add', function (app) {
        app({
            el: '#js-container'
        });
    });
</script>

@endsection