{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION,{
            getPartner:"/authed/business-api/get-partner-select",   //创建业务联动获取合作方
            getProduct:"/authed/business-api/get-product-list",  //创建业务联动获取产品编号
  
            execuntionDelete:"{{route('admin.executive.delete-executive')}}" ,//删除执行单, 
            execuntionOrder:"",//执行单下单, 
        });
    </script>
	
	<!-- 执行单页面单独引用，非公共-->

    <link  rel="stylesheet" media="screen" href="/modules/lib/handsontable/handsontable.css">
    <script  src="/modules/lib/handsontable/pikaday/pikaday.js"></script>
    <script  src="/modules/lib/handsontable/moment/moment.js"></script>
    <script  src="/modules/lib/handsontable/zeroclipboard/ZeroClipboard.js"></script>
    <script  src="/modules/lib/handsontable/handsontable.js"></script>
    <script  src="/modules/lib/WdatePicker/WdatePicker.js"></script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>排期管理>执行单列表</h2>
        </div>
		<div class="pull-right">
            <a id="show-ad-space" href="javascript:;" data-search="{{ route('admin.executive.api.get-surplus-advertis')}}">快速查询广告位余量</a>      
        </div>
    </div>

 <form action="" method="get" >
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">客户名称</td>
                            <td>
                                <select name="company_id" id="company_id"  placeholder="选择客户" class="js-selectbox js-relate-selectbox" data-relate-id="partner_id_select;product_id_select" data-relate-action="/authed/business-api/get-partner-select;/authed/business-api/get-product-list" data-rules="required">
								@foreach ($company_id as $d)
								<?php if(!empty(Request::get('company_id')) && Request::get('company_id')==$d['key']){  ?>
								 <option value="{{$d['key']}}" selected="selected">{{$d['value']}}</option>
								<?php }else{?>
								 <option value="{{$d['key']}}">{{$d['value']}}</option>
								<?php } ?>
                                   
								@endforeach	
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">合作方</td>
                            <td>
							<?php if(isset($partner_list)){ ?>
								<select name="partner_id" id="partner_id_select" class="js-selectbox">
								<option value="">--请选择--</option>
								@foreach ($partner_list as $d)
								<?php if(!empty($partner_id) && $d['key']==$partner_id ){  ?>
										<option value="{{$d['key']}}" selected="selected">{{$d['value']}}</option>
								<?php }else{ ?>
										<option value="{{$d['key']}}">{{$d['value']}}</option>
								<?php } ?>
								@endforeach	
								</select>
							<?php }else{ ?>
								<select name="partner_id" id="partner_id_select" class="js-selectbox"></select>
							<?php }?>
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">产品名称</td>
                            <td>
							<?php if(isset($product_list)){ ?>
								<select name="product_id" id="product_id_select" class="js-selectbox">
								<option value="">--请选择--</option>
								@foreach ($product_list as $key=>$v)
								<?php if(!empty(Request::get('product_id')) && $v==Request::get('product_id')){  ?>
										<option value="{{$v}}" selected="selected">{{$key}}</option>
								<?php }else{?>
										<option value="{{$v}}">{{$key}}</option>
								<?php } ?>
								@endforeach	
								</select>
							<?php }else{ ?>
								<select name="product_id" id="product_id_select" class="js-selectbox"></select>
							<?php }?>
                                
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">执行状态</td>
                            <td>
								<label><input type="checkbox" name="status[]" value="1" <?php if(!empty(Request::get('status')) && in_array(1,Request::get('status'))){ echo 'checked="checked"'; }?> ><span class="pr10">草稿</span></label>
                                <label><input type="checkbox" name="status[]" value="2" <?php if(!empty(Request::get('status')) && in_array(2,Request::get('status'))){ echo 'checked="checked"'; }?><span class="pr10">占坑</span></label>
                                <label><input type="checkbox" name="status[]" value="3" <?php if(!empty(Request::get('status')) && in_array(3,Request::get('status'))){ echo 'checked="checked"'; }?><span class="pr10">下单</span></label>
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
                        <th>执行单排期ID</th>
                        <th>排期名称</th>
                        <th>产品名称</th>
                        <th>产品简称</th>
                        <th>客户名称</th>
                        <th>合作方</th>
                        <th>合作方式</th>
                        <th>区域定向</th>
                        <th>执行状态</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
					@if ($listdata->count() == 0)
					 <tr>
						 <td colspan="10">没有内容</td>
					 </tr>
			   
					 @else
					 	@foreach ($listdata as $d)
						<tr>
							<td>{{$d->key}}</td>
							<td>{{$d->executive_rows[0]->month}}-{{$d->partner->company_name}}-{{$d->product->name}}-({{$d->key}})</td>
							<td>{{$d->product->name}}</td>
							<td>{{$d->pd_sname}}</td>
							<td>{{$d->company->company_name}}</td>
							<td>{{$d->partner->company_name}}</td>
							<td>{{$d->business_cooperation}}
							
							</td>
							<td>{{$d->targettype_text}}</td>
							<td>{{$d->status_text}}</td>
							<td>
							
							@if ($d->status!=1)
								<a href="{{route('admin.executive.executive-detail',$d->id)}}" >查看执行单</a>
							@endif 	
							
							<?php if(!empty($myExecution) && in_array($d->id,$myExecution)){ ?>
							@if ($d->status==2)
								<a href="{{route('admin.business.createBusinessforexecutive',$d->id)}}" >下单</a>
							@endif 
							@if ($d->status==3)
								<a href="{{ route('admin.business.create',$d->business_id) }}" >查看业务</a>
							@endif 	
								
								<a href="{{route('admin.executive.edit',$d->id)}}" >修改</a>
								<a href="javascript:;" class="js-execution-del" data-id="{{$d->id}}">删除</a>
							<?php }?>	
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
        seajs.use('lang/common', function (b) {
        b.com();
        b.execution_operate();
    });
    seajs.use("view/execution/list")
    </script>

@endsection