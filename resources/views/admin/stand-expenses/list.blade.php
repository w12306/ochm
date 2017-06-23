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
            iExpnedDelete:"{{ route('admin.stand-expenses.delete') }}" //删除独立支出接口
        });
    </script>
	<div class="heading clearfix">
        <div class="pull-left">
            <h2>支出管理>独立支出列表</h2>
        </div>
        <div class="pull-right">
            <a title="当前数据导出" href="javascript:;" class="btn mr5" data-url="{{ route('admin.stand-expenses.export-excel') }}" id="export-btn">当前数据导出</a>
        </div>
    </div>
     <ul class="nav nav-tabs mt10 mb10">
                <li ><a href="{{ route('admin.expenses.list-expenses') }}">业务支出</a></li>    
                <li class="active"><a href="#">独立支出</a></li>     
            </ul>

            <form action="" method=""  id="search-box">
                <table class="table table-listing">
                    <tbody>

                        <tr>
                            <td class="span4">下游合作方：</td>
                            <td>
                                <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
								@foreach ($under_partnerData as $d)
												<option value="{{$d['id']}}"  >{{$d['company_name']}}</option>
								@endforeach				
								 </select>
								 <input name="under_partner" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{ Request::get('under_partner') }}">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">支出类型：</td>
                            <td>
							@foreach ($expenses_type as $key=>$d)
								<label class="ml10">
                                <input type="checkbox" name="expend_type[]" value="{{$key}}"  <?php if(!empty(Request::get('expend_type')) && in_array($key,Request::get('expend_type'))){echo 'checked="checked"';}?>>{{$d}}
                                </label>
								
							@endforeach	
                               
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
                    <th>支出单号</th>
                    <th>支出类型</th>
                    <th>下游客户</th>
                    <th>下游合作方</th>
					<th>下游合作形式</th>
					<th>合同编号</th>
					<th>付款状态</th>
					<th>支出金额</th>
					<th>备注</th>
					<th>操作</th>
                </tr>
            </thead>
            <tbody>
			<?php
			$sum_amount=0.00;
			?>
			@foreach ($listdata as $d)
                <tr>
                    <td>{{$d->expenses_key}}</td>
                    <td>{{$expenses_type[$d->expenses_type]}}</td>
                    <td>
					@foreach ($d->under_partner->undercompany as $uc)
						{{$uc->company_name}},
					@endforeach	
					</td>
					<td>{{$d->under_partner->company_name}}</td>
					<td>{{$d->cooperation_type}}</td>
					<td>{{$d->contract_key}}</td>
					<td>
					<?php
					$payment_status='未付清';
					if(!empty($d->stand_payments)){
						$payment=0.00;
						foreach($d->stand_payments as $pv){
							$payment=$payment+$pv->payment_amount;
						}
						if($payment>=$d->amount){
							$payment_status='已付清';
						}
					}
					echo $payment_status;
					?>
					</td>
					<td>{{$d->amount}}</td>
					<td>{{$d->remark}}</td>
                    <td>
					<?php if($d->isshow==1){?>
                        <a href="{{ route('admin.stand-expenses.add-expenses',$d->id) }}" title="修改"  class="js-edit-data-type"><i hidid="" class="icon i-edit" title="编辑"></i></a>						
						<a href="javascript:;" title="删除" data-id="{{$d->id}}" class="js-data-delete"><i hidid="" class="icon i-del" title="删除"></i></a>
					<?php 
					$sum_amount=$sum_amount+$d->amount;
					}else{ ?>	
						<font color="#CCCCCC">已删除{{$d->deleted_at}}</font>
					<?php }?>	
                    </td>
                </tr> 
			@endforeach	
               <tr>
                    <td>本页合计:</td>
                    <td></td>
                    <td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>{{ number_format($sum_amount,2)}}</td>
					<td></td>
                    <td></td>
                </tr>    
            </tbody>
        </table>

	<div class="clearfix mt10">
	{!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($listdata))->render() !!}
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
   seajs.use('view/expend/list', function (app) {
        app({
            deleteAction:ST.ACTION.iExpnedDelete
        })
    });
	seajs.use('lang/common', function (b) {
            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
</script>
@endsection