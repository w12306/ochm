{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection
	
{{-- 内容 --}}
@section('content')
	<!-- 执行单页面单独引用，非公共-->
    <link  rel="stylesheet" media="screen" href="/modules/lib/handsontable/handsontable.css">
    <script  src="/modules/lib/handsontable/pikaday/pikaday.js"></script>
    <script  src="/modules/lib/handsontable/moment/moment.js"></script>
    <script  src="/modules/lib/handsontable/zeroclipboard/ZeroClipboard.js"></script>
    <script  src="/modules/lib/handsontable/handsontable.js"></script>
    <script  src="/modules/lib/WdatePicker/WdatePicker.js"></script>
	<script>
			$.extend(ST.ACTION,{
				businessDetail:"/authed/business-api/create-business/{{$bid}}/{{$executive_id}}",
            	editBusiness:"{{ route('admin.business.store-business') }}",//编辑、添加业务form表单提交接口
				getPartner:"/authed/business-api/get-partner-select",   //联动获取合作方
            	getContract:"/authed/business-api/get-contract-select",  //联动获取合同编号
				getProduct:"/authed/business-api/get-product-list",  //创建业务联动获取产品编//联动获得产品下拉数据 
				addConsumerUrl:"/authed/toolbox/create-company",
				
				productDetail:"/authed/business-api/get-product-att", //新增、编辑产品前加载数据传接口
				addProductList:"{{ route('admin.toolbox.store-product') }}", //新增，编辑产品form表单提交
				executionDetail:"{{ route('admin.executive.api.business-getexecutive')}}", //获取执行单数据
			});
	</script>
	<div class="container">
				<div class="heading clearfix">
					<div class="pull-left">
					<?php if($bid>0){ ?>
					<h2>业务管理>编辑业务</h2>
					<?php }else{ ?> 
					<h2>业务管理>创建业务</h2>
					<?php } ?>
						
					</div>
				</div>
				<div id="js-container"></div>
	</div>
 

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

 <script>
    seajs.use('view/business/create', function (app) {
        app({
            el: '#js-container'
        });
    })
</script>

@endsection