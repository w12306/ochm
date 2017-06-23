{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>录入坏账 - {{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION,{
          addDebt   : "{{ route('admin.badcash.save') }}",   //更新坏账
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>坏账管理>筛选未结清的业务</h2>
        </div>
    </div>

    <form action="" method="get">
        <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">合作方：</td>
                            <td>
							 <select name="partner_id" class="js-selectbox js-partner" placeholder="请选择" >
									<option value="">--请选择--</option>
                                   @foreach ($partnerData as $d)
                                    <option value="{{$d->id}}"  <?php if(Request::get('partner_id')==$d->id){echo 'selected="selected"';} ?> >{{$d->company_name}}</option>
							   		@endforeach	
                              </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">业务编号</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small js-business" value="{{ Request::get('business_key') }}" >
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="submit" class="btn btn-blue" value="查询"></td>
                        </tr>
                    </tbody>
                </table>
            
    </form>

    <table class="table table-listing mt20">
        <thead class="thead-gray">
        <tr>
            <th>业务编号</th>
            <th>执行小组</th>
            <th>执行月份</th>
            <th>合作方</th>
			<th>产品名称</th>
            <th>实际金额拆分</th>
            <th>执行金额</th>
            <th>发票总额</th>
            <th>回款总额</th>
            <th>支出总额</th>    
            <th>约定结款时间</th>
            <th>回款状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
		@foreach ($deliveries as $delivery)
			<tr>
				<td>{{ $delivery->business_key }}</td>
				<td>{{$teamDept[$delivery->businessTeam->team]}}</td>
				<td>{{ $delivery->month}}</td>
				<td>{{ $delivery->businessTeam->business->partner->company_name}}</td>
				<td>{{ $delivery->businessTeam->business->product->name }}</td>
                <td>{{ $delivery->businessTeam->active_amount }}</td>
				<td>{{ $delivery->amount }}</td>
				<td> <?php $invoice_total=0.00;?>
					  @foreach ($delivery->invoice_deliveys as $de)
						<?php $invoice_total=$invoice_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($invoice_total,2);?></td>
                <td>
				 <?php $backcash_total=0.00;?>
					  @foreach ($delivery->backcash_invoices as $de)
						<?php $backcash_total=$backcash_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($backcash_total,2);?>
				</td>
                <td>
				 <?php $expenses_total=0.00;?>
					  @foreach ($delivery->expenses_deliveys as $de)
						<?php $expenses_total=$expenses_total+$de->active_amount;?>
					  @endforeach
					  <?php echo number_format($expenses_total,2);?>
				</td>
				<td>{{ $delivery->businessTeam->business->paytime }}</td>
				<td>{{ $delivery->businessTeam->backcashstatus_text }}</td>
				<td>
				<?php if(!empty($badHasArr) && isset($badHasArr[$delivery->business_id.$delivery->businessTeam->id.$delivery->id]) ){ ?>
					已录入
				<?php }else{ ?>
				<a href="javascript:;" class="pr10 js-add-debt"
                                data-busid="{{ $delivery->business_key }}"
                                data-deliveryid="{{ $delivery->id }}"
                                data-month="{{ $delivery->month }}"
                                data-team="{{$teamDept[$delivery->businessTeam->team]}}">添加坏账</a>
				<?php } ?>
				 
				</td>
			</tr>	
		@endforeach	
        </tbody>
    </table>
<div class="clearfix mt10">
 @if (!empty($deliveries))
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($deliveries))->render() !!}
 @endif
    </div>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('lang/common', function (b) {
            b.com();
            b.debt();
        });
    </script>
@endsection