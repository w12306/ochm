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
            productDetail:"/authed/business-api/get-product-att", //新增/编辑 表单数据
            addProductList:"{{ route('admin.toolbox.store-product') }}"   //表单提交
        });
    </script>
    <div class="heading clearfix">
        <div class="pull-left">
             <h2>工具箱>产品管理</h2>
        </div>
        <div class="pull-right">
               <a title="新增产品信息" href="javascript:;" class="btn mr5" id="js-add-product">新增产品信息</a>   
            </a>
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true">
		 <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">产品类型</td>
                            <td>
							@foreach ($product_type as $producttype)
								<?php 
								 $checked="";
								 if(!empty(Request::get('type'))){
								 	if(in_array($producttype['key'],Request::get('type'))){ 
										$checked='checked="checked"';
									}
								 }
								?> 
								<label><input type="checkbox" name="type[]" value="{{$producttype['key']}}" <?php echo $checked;?> /><span class="pr10">{{$producttype['value']}}</span></label>
							@endforeach	
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">产品名称</td>
                            <td>
                                <input type="text" name="name" value="{{ Request::get('name', '') }}" class="input" placeholder="请输入产品名称">
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
			<th>产品ID</th>
            <th>产品名称</th>
            <th>产品类型</th>
			<th>客户名称</th>
			<th>游戏画面</th>
            <th>模式类型</th>
            <th>游戏题材</th>
			<th>画面风格</th>
			<th>运营模式</th>
			<th>收费模式</th>
			<th>游戏类型</th>
			<th>操作</th>
        </tr>
        </thead>
		
        <tbody>
	   @if ($listdata->count() == 0)
             <tr>
                 <td colspan="12">没有内容</td>
             </tr>
       @endif
       @foreach ($listdata as $product)
            <tr>
				<td>
                    {{ $product->id }}
                </td>
				<td>
                    {{ $product->name }}
                </td>
				<td>
                    {{ $product->type }}
                </td>
				<td>
				<?php if(isset($company_list[$product->company_id])){echo $company_list[$product->company_id];}?>
                </td>
				<td>
                    {{ $product->game_screen }}
                </td>
				<td>
                    {{ $product->mode_type }}
                </td>
				<td>
                    {{ $product->game_theme }}
                </td>
				<td>
                    {{ $product->screen_style }}
                </td>
				<td>
                    {{ $product->business_model }}
                </td>
				<td>
                    {{ $product->charging_mode }}
                </td>
				<td>
                    {{ $product->game_type }}
                </td>
               
                <td>
					<a href="javascript:;" data-id="{{ $product->id }}" class="js-product-edit"><i hidid="{{ $product->id }}" class="icon i-edit"></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
	<div class="clearfix mt10">
			{!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($listdata))->render() !!}
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
    seajs.use('view/tool/product', function (app) {
        app();
    })
</script>

@endsection