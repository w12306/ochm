{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>坏账管理 - {{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {
            addDebt   : "{{ route('admin.badcash.save') }}",   //更新坏账
            deleteDebt: "{{ route('admin.badcash.delete') }}", //删除坏账
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>坏账管理>坏账列表</h2>
        </div>
        <div class="pull-right">
            <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.badcash.export-excel') }}"
                    id="export-btn">当前数据导出</a>
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true" id="search-box">
        <table class="table table-listing" id="search-box">
            <tbody>
            <tr>
                <td class="span4">合作方</td>
                <td>
                    <select name="" id="" class="js-selectbox" placeholder="支持多选" multiple="multiple">
                        @foreach ($partners as $partner)
                            <option value="{{ $partner['key'] }}">{{ $partner['value'] }}</option>
                        @endforeach
                    </select>
                    <input name="partner_id_csv" type="hidden" class="hidden js-selectbox-multiple-txt" value="{{ Request::get('partner_id_csv') }}">
                </td>
            </tr>

            <tr>
                <td class="span4">业务编号</td>
                <td>
                    <input type="text" name="business_key" class="input input-small" value="{{ Request::get('business_key') }}">
                </td>
            </tr>

            <tr>
                <td class="span4">录入坏账时间</td>
                <td>
                    <input class="input input-small" type="text" id="_calender1" name="created_at_begin" value="{{ Request::get('created_at_begin') }}" readonly="readonly">
                    &nbsp;-&nbsp;
                    <input class="input input-small" type="text" id="_calender2" name="created_at_end" value="{{ Request::get('created_at_end') }}" readonly="readonly">
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
            <th>业务编号</th>
            <th>执行小组</th>
            <th>执行月份</th>
            <th>合作方</th>
            <th>产品名称</th>
            <th>实际金额拆分</th>
            <th>月执行金额</th>
            <th>已开发票总额</th>
            <th>回款总额</th>
            <th>支出总额</th>
            <th>约定结款时间</th>
            <th>回款状态</th>
            <th>坏账金额</th>
            <th>坏账录入时间</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
		<?php 
		$sum_active_amount=0.00;
		$sum_delivery_amount=0.00;
		$sum_invoice_total=0.00;
		$sum_backcash_total=0.00;
		$sum_expenses_total=0.00;
		$sum_amount=0.00;
		?>
        @foreach ($deliveries as $delivery)
            <tr>
              	<td>{{ $delivery->business->business_key }}</td>
				<td>{{ $teamDept[$delivery->businessTeam->team]}}</td>
				<td>{{ $delivery->delivery->month }}</td>
				<td>{{ $delivery->business->partner->company_name }}</td>
                <td>{{ $delivery->business->product->name }}</td>
                <td>{{ $delivery->businessTeam->active_amount }}</td>
                <td>{{ $delivery->delivery->amount }}</td>
				
				 <td> <?php $invoice_total=0.00;?>
					  @foreach ($delivery->delivery->invoice_deliveys as $de)
						<?php $invoice_total=$invoice_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($invoice_total,2);?></td>
                <td>
				 <?php $backcash_total=0.00;?>
					  @foreach ($delivery->delivery->backcash_invoices as $de)
						<?php $backcash_total=$backcash_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($backcash_total,2);?>
				</td>
                <td>
				 <?php $expenses_total=0.00;?>
					  @foreach ($delivery->delivery->expenses_deliveys as $de)
						<?php $expenses_total=$expenses_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($expenses_total,2);?>
				</td>
				
				<td>{{ $delivery->business->paytime }}</td>
                <td>{{ $delivery->businessTeam->backcashstatus_text }}</td>
				<td>{{ $delivery->amount }}</td>
				<td>{{ $delivery->created_at->toDatetimeString() }}</td>
				<td>
				<?php if($delivery->isshow==0){ ?>
				<font color="#CCCCCC">删除时间<br>{{ $delivery->updated_at}}</font>
				<?php }else{ ?>
				<a href="#" class="pr10 js-edit-debt"
                                data-busid="{{ $delivery->business->business_key }}"
                                data-id="{{ $delivery->id }}"
                                data-amount="{{ $delivery->amount }}"
                                data-month="{{ $delivery->delivery->month }}"
                                data-team="{{ $teamDept[$delivery->businessTeam->team]}}"><i hidid="" class="icon i-edit" title="编辑"></i></a>
                        <a href="#" class="pr10 js-del-debt"
                                data-id="{{ $delivery->id }}"><i hidid="" class="icon i-del" title="删除"></i></a>
				<?php } ?>
				</td>
            </tr>
		<?php
		$sum_active_amount=$sum_active_amount+$delivery->businessTeam->active_amount;
		$sum_delivery_amount=$sum_delivery_amount+$delivery->delivery->amount;
		$sum_invoice_total=$sum_invoice_total+$invoice_total;
		$sum_backcash_total=$sum_backcash_total+$backcash_total;
		$sum_expenses_total=$sum_expenses_total+$expenses_total;
		$sum_amount=$sum_amount+$delivery->amount;
		?>	
        @endforeach
		<tr>
              	<td>当前页合计:</td>
				<td></td>
				<td></td>
				<td></td>
                <td></td>
                <td>{{ number_format($sum_active_amount,2) }}</td>
                <td>{{ number_format($sum_delivery_amount,2)}}</td>
				<td>{{ number_format($sum_invoice_total,2)}}</td>
                <td>{{ number_format($sum_backcash_total,2)}}</td>
                <td>{{ number_format($sum_expenses_total,2)}}</td>
				<td></td>
                <td></td>
				<td>{{$sum_amount}}</td>
				<td></td>
				<td></td>
            </tr>
        </tbody>
    </table>

    <div class="clearfix mt10">
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($deliveries))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('lang/common', function (b) {
            b.com();
            b.debt();
			b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
    </script>
@endsection