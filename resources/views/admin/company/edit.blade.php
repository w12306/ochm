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
            consumerDetail:"/authed/business-api/get-create-company/{{$id}}", //新增，编辑前获取客户信息
            addProductList:"/authed/business-api/get-partner-list",   //展示合作方列表
            partnerDetail:"/authed/business-api/get-create-partner",     //新增，编辑合作方前获取合作方信息
            editPartner:"/authed/toolbox/store-partner", //新增编辑合作方form提交接口
            editConsumer:"/authed/toolbox/store-company", //新增，编辑客户form提交接口
			getUsers:"/authed/config/permission/api/get-users",   //
        });
    
    </script>

            <div class="heading clearfix">
                <div class="pull-left">
					<?php if(!empty($id)){?>
						<h2>工具箱>更新客户</h2>
					<?php }else{?>
						<h2>工具箱>新增客户</h2>
					<?php }?>
                    
                </div>
            </div>

            <div id="js-container"></div>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
   <script>
    seajs.use('view/tool/addconsumer', function (app) {
        app({
            el: '#js-container'
        });
    })
</script>

@endsection