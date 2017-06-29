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
            iPaymentDelete:"{{route('admin.stand-payment.delete')}}" //删除独立付款接口
        });
    </script>
			<div class="heading clearfix">
        <div class="pull-left">
            <h2>付款管理>独立付款列表</h2>
        </div>
        <div class="pull-right">
            <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.stand-payment.export-excel') }}"
                    id="export-btn">当前数据导出</a>
        </div>
    	</div>
			<ul class="nav nav-tabs mt10 mb10">
                <li ><a href="{{ route('admin.payment.list') }}">业务付款</a></li>    
                <li class="active"><a href="#">独立付款</a></li>     
            </ul>
	 <form action="" method=""   id="search-box">
                <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4">付款时间：</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender1" name="btime" value="{{ Request::get('btime') }}" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender2" name="etime" value="{{ Request::get('etime') }}" readonly="readonly">
                            </td>
                        </tr>
						 <tr>
                            <td class="span4">发票类型：</td>
                            <td>
                                <input type="text" name="invoice_type" class="input input-small" value="{{ Request::get('invoice_type') }}" >
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">下游合作方：</td>
                            <td>
                               <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
								@foreach ($under_partnerData as $d)
												<option value="{{$d['id']}}">{{$d['company_name']}}</option>
								@endforeach				
								 </select>
								 <input name="under_partner" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{ Request::get('under_partner') }}">
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
                    <th>付款单号</th>
                    <th>付款金额</th>
                    <th>付款时间</th>
                    <th>发票金额</th>
					<th>发票编号</th>
					<th>发票类型</th>
					<th>支出金额（元）</th>
					<th>支出类型</th>
					<th>下游客户</th>
					<th>下游合作方</th>
					<th>下游合作形式</th>
					<th>备注</th>
					<th>操作</th>
                </tr>
            </thead>
            <tbody>
			<?php
			$sum_amount=0.00;
			$invoice_amount=0.00;
			$stand_expenses_amount=0.00;
			?>
			@foreach ($listdata as $d)
                <tr>
                    <td>{{$d->payment_key}}</td>
                    <td>{{$d->payment_amount}}</td>
					<td>{{$d->payment_time}}</td>
					<td>{{$d->invoice_amount}}</td>
					<td>{{$d->invoice_num}}</td>
					<td>{{$d->invoice_type}}</td>
					<td>{{$d->stand_expenses->amount}}</td>
					<td>{{ $expenses_type[$d->stand_expenses->expenses_type]}}</td>
					<td>
					@foreach ($d->stand_expenses->under_partner->undercompany as $uc)
						{{$uc->company_name}},
					@endforeach	
					</td>
					<td>{{$d->stand_expenses->under_partner->company_name}}</td>
					<td>{{$d->stand_expenses->cooperation_type}}</td>
					<td>{{$d->remark}}</td>
                    <td>
					<?php if($d->isshow==1){?>
                        <a href="{{ route('admin.stand-payment.add-payment',$d->id) }}" title="修改"  class="js-edit-data-type"><i hidid="" class="icon i-edit" title="编辑"></i></a>						
						<a href="javascript:;" title="删除" data-id="{{$d->id}}" class="js-data-delete"><i hidid="" class="icon i-del" title="删除"></i></a>
						<?php
						$sum_amount=$sum_amount+$d->payment_amount;
						$invoice_amount=$invoice_amount+$d->invoice_amount;
						$stand_expenses_amount=$stand_expenses_amount+$d->stand_expenses->amount;
						?>
					<?php }else{ ?>	
						<font color="#CCCCCC">已删除{{$d->deleted_at}}</font>
					<?php }?>	
                        
                    </td>
                </tr> 
			@endforeach	
                  <tr>
                    <td>本页合计：</td>
                    <td>{{number_format($sum_amount,2)}}</td>
					<td></td>
					<td>{{number_format($invoice_amount,2)}}</td>
					<td></td>
					<td></td>
					<td>{{number_format($stand_expenses_amount,2)}}</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
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
            el:"#js-container",
            deleteAction:ST.ACTION.iPaymentDelete
        });
    });
	seajs.use('lang/common', function (b) {
            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
</script>
@endsection