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
            partnerDetail:"/authed/business-api/get-create-partner",     //新增，编辑合作方前获取合作方信息
            editPartner:"/authed/toolbox/store-partner", //新增编辑合作方form提交接口
        });
    
    </script>
    <div class="heading clearfix">
         		<div class="pull-left">
                    <h2>工具箱>合作方管理</h2>
                </div>
                <div class="pull-right">
                    <a title="新增合作方" href="javascript:;" class="btn mr5" id="js-add-partner">新增合作方</a>
                    <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.toolbox.partner-excel') }}"
                    id="export-btn">当前数据导出</a>  
                </div>
    </div>

   <form action="" method="get" id="search-box">
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">公司类型</td>
                            <td>
							@foreach ($company_type as $k=>$d)
									<?php if(!empty(Request::get('company_type'))){ ?>
									<label><input type="checkbox" name="company_type[]" value="{{$k}}"  <?php if(in_array($k,Request::get('company_type'))){echo 'checked="checked"'; }?> />&nbsp;&nbsp;{{$d}}&nbsp;&nbsp;</label>
									<?php }else{ ?>
									<label><input type="checkbox" name="company_type[]" value="{{$k}}" />&nbsp;&nbsp;{{$d}}&nbsp;&nbsp;</label>
									<?php } ?>
							@endforeach 	
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">公司全称</td>
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
                        <th>合作方ID</th>
                        <th>公司全称</th>
                        <th>公司类型</th>
                        <th>初始余额</th>
                        <th>纳税识别码</th>
                        <th>公司简称</th>
                        <th>公司地址</th>
                        <th>公司电话</th>
                        <th>联系人</th>
                        <th>所属上游客户</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
				@if ($listdata->count() == 0)
					 <tr>
						 <td colspan="11">没有内容</td>
					 </tr>
			    @endif
				 @foreach ($listdata as $d)
                    <tr>
                        <td>{{$d->id}}</td>
                        <td>{{$d->company_name}}</td>
                        <td>{{$d->companytype_text}}</td>
                        <td>{{$d->balance}}</td>
                        <td>{{$d->taxkey}}</td>
                        <td>{{$d->nickname}}</td>
                        <td>{{$d->address}}</td>
                        <td>{{$d->tel}}</td>
                        <td>{{$d->boss}}</td>
                        <td>
						@foreach ($d->company as $upcompany)
							{{$upcompany->company_name}}<br>
						@endforeach	
						</td>
                        <td>
							<a href="javascript:;" class="js-partner-edit" data-id="{{ $d->id }}"><i hidid="{{ $d->id }}" class="icon i-edit"></i></a>
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
    seajs.use('view/tool/partner', function (app) {
        app();
    })
	
	seajs.use('lang/common', function (b) {
            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
</script>

@endsection