{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>
	<script>
        $.extend(ST.ACTION,{
            newUser:"{{ route('admin.config.permission.api.admin-user-info',$adminUserId)}}", //新增，编辑客户form提交接口
			formAction:"{{ route('admin.config.permission.api.save-admin-user')}}"
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
		<?php if(!empty($adminUserId)){ ?>
		<h2>编辑操作人员 </h2>
		<?php }else{ ?>
		<h2>创建操作人员 </h2>
		<?php }?>
            
        </div>
    </div>

     <div id="js-container">

     </div>  

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
       seajs.use('view/tool/newUser', function (app) {
        app({
            el: '#js-container'
        });
    })

    </script>

@endsection