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
			businessDelete:"/authed/business/delete-business",//删除业务接口,
            amountList:"/authed/business-api/get-delivery-list", //添加执行额时获取执行额列表
            addAmount:"/authed/business/store-amount-business",  //编辑、添加执行额form提交接口
            amountDelete:"/authed/business/delete-delivery" //删除执行额
        });
    </script>
    <div class="heading clearfix">
		<div class="pull-left">
            <h2>录入执行金额</h2>
        </div>
        <div class="pull-right">
            <a title="新建业务" href="{{ route('admin.business.create') }}" class="btn mr5">
                <i class="icon i-add"></i> 新建业务
            </a>
        </div>
    </div>

    <form action="" method="get">
        <table class="table table-listing">
            <tbody>
            <tr>
                <td class="span4">客户名称:</td>
                <td>
					<select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
					@foreach ($companyData as $d)
                                    <option value="{{$d['key']}}">{{$d['value']}}</option>
					@endforeach				
                     </select>
                     <input name="company_id" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{Request::get('company_id')}}">

                </td>
            </tr>

            <tr>
                <td class="span4">合作方:</td>
                <td>
				  <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
					@foreach ($partnersData as $d)
                                    <option value="{{$d['key']}}">{{$d['value']}}</option>
					@endforeach				
                     </select>
                     <input name="partner_id" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{Request::get('partner_id')}}">
				
                </td>
            </tr>
			
			<tr>
                <td class="span4">业务类型:<input type="checkbox" onclick="selectAllBox('business_type[]',this)" name="business_type_parent" /></td>
                <td>
				
				@foreach ($business_type as $d)
					<?php if(!empty(Request::get('business_type'))){ ?>
					<label><input type="checkbox" name="business_type[]" onclick="childSelectAllBox('business_type_parent',this)" value="{{$d['key']}}" <?php if(in_array($d['key'],Request::get('business_type'))){echo 'checked="checked"'; }?>   />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php }else{ ?>
					<label><input type="checkbox" name="business_type[]" onclick="childSelectAllBox('business_type_parent',this)" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php } ?>
					
				@endforeach
                </td>
            </tr>
			
			<tr>
                <td class="span4">业务线:<input type="checkbox" onclick="selectAllBox('business_line[]',this)" name="business_line_parent" /></td>
                <td>
				@foreach ($business_line as $d)
					<?php if(!empty(Request::get('business_line'))){ ?>
					<label><input type="checkbox" name="business_line[]" onclick="childSelectAllBox('business_line_parent',this)" value="{{$d['key']}}"  <?php if(in_array($d['key'],Request::get('business_line'))){echo 'checked="checked"'; }?> />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php }else{ ?>
					<label><input type="checkbox" name="business_line[]" onclick="childSelectAllBox('business_line_parent',this)" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
					<?php } ?>
				@endforeach
                </td>
            </tr>
			<tr>
                <td class="span4">执行小组:</td>
                <td>
				<table class="table table-listing">
                        <tbody>
						<?php foreach($team as $k=>$t){  ?>
						<tr>
                            <td style="width: 120px; text-align: right;">
                        	<span style="text-align:left; width: auto;" class="inline-block span3"><?php echo $k; ?><input type="checkbox" onclick="selectAllBoxId('team_<?php echo $k; ?>',this)" name="team_<?php echo $k; ?>_parent" /></span>
                            </td>
                            <td>
                            <?php foreach($t as $d){  ?>
								<?php if(!empty(Request::get('team'))){ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" <?php if(in_array($d['value'],Request::get('team'))){echo 'checked="checked"'; }?> data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]" id="team_<?php echo $k; ?>" onclick="childSelectAllBox('team_<?php echo $k; ?>_parent',this)">{{$d['key']}}
                            	</label>
								<?php }else{ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]" id="team_<?php echo $k; ?>" onclick="childSelectAllBox('team_<?php echo $k; ?>_parent',this)">
                                {{$d['key']}}
                            	</label>
								<?php } ?>
							<?php } ?>                                
                            </td>
                        </tr>
						<?php }	?>    
					</tbody>
				</table>
                </td>
            </tr>
			<tr>
                <td class="span4">回款状态:</td>
                <td>
				<label><input type="checkbox" name="verify_type" value="1" ><span class="pr10">未结清</span></label>
                 <label><input type="checkbox" name="verify_type" value="2" ><span class="pr10">已结清</span></label>
                </td>
            </tr>
			
			
			<tr>
                <td class="span4">业务编号:</td>
                <td>
				<input type="text" name="key" class="input input-small" value="{{Request::get('key')}}" >
                </td>
            </tr>

            <tr class="tr_bg">
                <th></th>
                <td><input type="submit" class="btn btn-blue" value="查询"></td>
            </tr>
            </tbody>
        </table>
    </form>
    <table class="table table-listing mt20 bus-list border-table">
        <thead class="thead-gray">
        <tr>
            <th>业务编号</th>
            <th>执行小组</th>
			<th>实际金额</th>
			<th>执行总额</th>
			<th>发票总额</th>
			<th>回款总额</th>
			<th>支出总额</th>
			<th>合作方</th>
			<th>客户名称</th>
			<th>业务类型</th>
			<th>合同编号(红色为框架)</th>
			<th>约定执行时间</th>
			<th>约定结款时间</th>
			<th>业务状态</th>
			<th>回款状态</th>
			<th>操作</th>
        </tr>
        </thead>
      @if ($listdata->count() == 0)
             <tr>
                 <td colspan="15">没有内容</td>
             </tr>
       @else
            @foreach ($listdata as $d)
		   <tr >
				  <td><a href="{{ route('admin.business.business-detail' , ['id' => $d->business->id]) }}">{{ $d->business->business_key }}</a></td>
				  <td>{{ $teamDept[$d->team] }}</td>
				  <td>
				  {{ $d->active_amount }}
				  </td>
				  <td>
					  <?php $total=0.00;?>
					  @foreach ($d->deliverys as $de)
						<?php if($de->isshow==1){$total=$total+$de->amount;}?>
					  @endforeach
					  <?php echo number_format($total,2);?>
					</td>
					<td>
					   <?php $invoice_total=0.00;?>
					  @foreach ($d->invoice_deliveys as $de)
						<?php if($de->isshow==1){$invoice_total=$invoice_total+$de->active_amount;}?>
					  @endforeach
					  <?php echo number_format($invoice_total,2);?>
					 </td>
					 <td>
					   <?php $backcash_total=0.00;?>
					  @foreach ($d->backcash_invoices as $de)
						<?php if($de->isshow==1){$backcash_total=$backcash_total+$de->active_amount;}?>
					  @endforeach
					  <?php echo number_format($backcash_total,2);?>
					  </td>
					  <td>
					  <?php $expenses_total=0.00;?>
					  @foreach ($d->expenses_deliveys as $de)
						<?php if($de->isshow==1){$expenses_total=$expenses_total+$de->active_amount;}?>
					  @endforeach
					  <?php echo number_format($expenses_total,2);?>
					  </td>
				  <td>{{ $d->business->partner->company_name }}</td>
				  <td>{{ $d->business->company->company_name }}</td>
				  <td>{{ $d->business->business_type }}</td>
				  <td>
				   <?php $ctkey=[];?>
					  @foreach ($d->business->contracts as $ct)
						<?php $ctkey[$ct->ckey]=$ct->ckey; ?>
					  @endforeach
				    <?php echo implode(',',$ctkey);?>
				  </td>
				  <td>{{ $d->business->btime }}-{{ $d->business->etime }}</td>
			  	  <td>{{ $d->business->paytime }}</td>
				  <td>{{ $d->business->auditstatus_text }}</td>
				  <td>{{$d->backcashstatus_text}}</td>
				  <td>
				  
				  <?php if($d->business->audit_status!=-1){ ?>
					  <?php if($d->business->audit_status!=1 || $audit_edit){ ?>
					  <a href="{{ route('admin.business.create') }}/{{ $d->business->id }}" class="js-product-edit"><i hidid="" class="icon i-edit" title="编辑业务"></i></a>
					  <?php } ?>
				  <a href="javascript:;" class="js-bus-del" data-id="{{ $d->business->id }}"><i hidid="" class="icon i-del" title="删除业务"></i></a>
				  <?php $sum_delivery_amount=0.00;?>
					  @foreach ($d->deliverys as $de)
						<?php if($de->isshow==1){ $sum_delivery_amount=$sum_delivery_amount+$de->amount;} ?>
					  @endforeach
				  <a href="javascript:;" class="js-add-amount" data-id="{{ $d->id }}" data-active_amount='{{$d->active_amount}}' data-delivery_amount="{{$sum_delivery_amount}}" data-title="{{ $d->business_key }}:{{ $teamDept[$d->team] }}" class="pr10"><i hidid="" class="icon i-addMount" title="增加执行额"></i></a>
				  
				  <?php }else{ ?>
				   <font color="#CCCCCC">删除时间<br>{{ $d->business->del_time }}</font>
				  <?php } ?>
				  
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
		b.business_del_pop();
		b.addamount_pop();
    });
	function selectAllBox(name,obj){
		$("input[name='"+name+"']").each(function(i){
			if(obj.checked){
				this.checked=true;
			}else{
				this.checked=false;
			}
		});	
	}
	function selectAllBoxId(name,obj){
		$("input[id='"+name+"']").each(function(i){
			if(obj.checked){
				this.checked=true;
			}else{
				this.checked=false;
			}
		});	
	}
	function childSelectAllBox(name,obj){
		$("input[name='"+name+"']").each(function(i){
			if(!obj.checked){
				this.checked=false;
			}
		});	
	}
</script>
  

@endsection