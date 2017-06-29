{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
         <div class="pull-left">
                    <h2>工具箱>客户管理</h2>
         </div>
         <div class="pull-right">
              <a title="新增产品信息" href="{{ route("admin.toolbox.create-company") }}" class="btn mr5" >新增客户信息</a>    
         </div>
    </div>

	<form  method="get"  action="">
                <table class="table table-listing" id="search-box">
                    <tbody>
                       <!-- <tr>
                            <td class="span4">客户级别</td>
                            <td>
                                <select name="" id="" class="js-selectbox" placeholder="请选择">
                                    <option value="1">客户级别1</option>
                                    <option value="2">客户级别2</option>
                                    <option value="3">客户级别3</option>
                                    <option value="4">客户级别4</option>
                                </select>
                            </td>
                        </tr>
						-->
                        <tr>
                            <td class="span4">客户名称</td>
                            <td>
                                <input type="text" name="company_name" class="input input-small" value="{{Request::get('company_name')}}" >
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="submit" class="btn btn-blue" value="查询"></td>
                        </tr>
                    </tbody>
                </table>
     </form>
    

    		<table class="table table-listing mt20 border-table">
                <thead class="thead-gray">
                    <tr>
                        <th>上游客户ID</th>
                        <th>客户名称</th>
                       <!-- <th>客户级别</th>-->
                        <th>所属执行小组</th>
                        <th>提交时间</th>
                        <th>提交人</th>
                        <!--<th>最后操作人</th>
                        <th>最后操作时间</th>-->
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
				@if ($listdata->count() == 0)
				 <tr>
					 <td colspan="6">没有内容</td>
				 </tr>
		   
				@else
            		@foreach ($listdata as $d)
                    <tr>
                        <td>{{$d->id}}</td>
                        <td>{{$d->company_name}}</td>
                        <!--<td>{{$d->id}}</td>-->
                        <td>{{$d->team}}</td>
                        <td>{{$d->created_at}}</td>
                        <td>{{$d->real_name}}</td>
                        <!--<td>{{$d->id}}</td>
                        <td>{{$d->id}}</td>-->
                        <td>
                            <a href="{{ route("admin.toolbox.create-company",['id' => $d->id]) }}" class="js-product-edit"><i hidid="" class="icon i-edit" title="编辑"></i></a>
                        </td>
                    </tr>  
						@endforeach
				@endif	                 
                </tbody>
            </table>
	<div class="clearfix mt10">
			{!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($listdata))->render() !!}
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        $.extend(ST, {
            //页面初始化方法写在此处
            pageInit: function () {
            }
        });
       
    </script>

@endsection