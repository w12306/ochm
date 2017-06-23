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
            businessCheck:"{{ route('admin.business.store-audit-business') }}", //业务审核接口
            bussinessAmountEdit:"{{ route('admin.business.update-business-data') }}",//预算金额编辑
			bussinessDetail1:"{{ route('admin.business.api.business-backcash',$businessData->business_key ) }}",//多行的业务详情表1接口
            bussinessDetail2:"{{ route('admin.business.api.business-expenses',$businessData->business_key) }}",//多行的业务详情表2接口
			executionDetail:"{{route('admin.executive.api.executive-detail',[$executive_id,0])}}", //获取执行单详情数据
        });
</script>
	<link  rel="stylesheet" media="screen" href="/modules/lib/handsontable/handsontable.css">
    <script  src="/modules/lib/handsontable/pikaday/pikaday.js"></script>
    <script  src="/modules/lib/handsontable/moment/moment.js"></script>
    <script  src="/modules/lib/handsontable/zeroclipboard/ZeroClipboard.js"></script>
    <script  src="/modules/lib/handsontable/handsontable.js"></script>
    <script  src="/modules/lib/WdatePicker/WdatePicker.js"></script>	
            <div class="heading clearfix">
                <div class="pull-left">
                    <h2>业务管理>业务详情</h2>
                </div>
                <div class="pull-right">
				<?php if($businessData->audit_status!=-1){ ?>
					<?php if($businessData->audit_status!=1 || $audit_edit){ ?>
                    <a title="编辑业务" href="{{ route('admin.business.create') }}/{{ $businessData->id }}" class="btn mr5">编辑业务</a>   
					<?php }?> 
                <?php }?>
				</div>
            </div>
			<input type="hidden" id="busid" value="{{ $businessData->id }}"> 
			<?php 
			$class_str=' js-edit-amount ';
			$text='(点击修改)';
			if($businessData->audit_status==-1){
				$class_str='';
				$text='';
			}?>
            <div class="datatable datatable-default datatable-striped datatable-condensed" style="width:100%;">
				<div class="clearfix datatable-div" >
			 		<ol class="datatable-bd-new">
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell" >业务编号</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->business_key }}</div>
							</div>
						</li>
						<li class="datatable-row">
							 <div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">客户名称</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->company->company_name }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">合作方</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->partner->company_name }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">产品名称</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->product->name }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">产品类型</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->product->type }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">业务类型</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->business_type }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">业务线</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->business_line }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">约定执行时间</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->btime }}-{{ $businessData->etime }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">约定结款时间</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->paytime }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">业务状态</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->auditstatus_text }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">业务回款状态</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{$businessData->backcash_status}}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">确认函状态</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">{{ $businessData->confirmstatus_text }}</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            <div class="datatable-cell">合同编号</div>
							</div>
							<div class="datatable-col" style="width:80%">
                            <div class="datatable-cell" title="{{ $contractInfo['key'] }}">{{ $contractInfo['key'] }}</div>
                        </div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            	<div class="datatable-cell">合同状态</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								@if (isset($contractInfo['status']) && !empty($contractInfo['status']))
									@foreach ($contractInfo['status'] as $k=>$v)
									{{$k.":".$v}}&nbsp;&nbsp;
									@endforeach
								@endif	
								</div>
							</div>
						</li>
					</ol>
                <ol class="datatable-bd-new">
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            	<div class="datatable-cell">执行小组</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								@if ($businessData->businessteams->count() != 0)
									@foreach ($businessData->businessteams as $d)	
										{{$teamDept[$d->team]}}&nbsp;&nbsp;&nbsp;&nbsp;
									@endforeach
								@endif	
								</div>
							</div>
						</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">预算金额</div>
							</div>
							<div class="datatable-col" style="width:80%">
							   <div class="datatable-cell {{$class_str}}" data-name="amount">
							   <?php if($businessData->audit_status!=1 || $audit_edit){ ?>
									<span class="js-txt">{{ $businessData->amount }}</span>
									<span class="red">{{$text}}</span>  
								<?php }else{ ?>
									<span >{{ $businessData->amount }}</span>
								<?php }?> 
								</div>
							</div>
						</li>	
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">实际金额</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell {{$class_str}}" data-name="active_amount">
									<?php if($businessData->audit_status!=1 || $audit_edit){ ?>
										<span class="js-txt">{{ $businessData->active_amount }}</span>
										<span class="red">{{$text}}</span>
									<?php }else{ ?>
										<span >{{ $businessData->active_amount }}</span>
									<?php }?> 
									
								</div>
							</div>
                    	 </li>
					@if ($businessData->businessteams->count() >1)	 
						@if ($team_amount->count() != 0)
            				@foreach ($team_amount as $d)	
							<li class="datatable-row">
								<div class="datatable-col" style="width:20%">
									<div class="datatable-cell" title="预算金额拆分-{{$teamDept[$d->team]}}">预算金额拆分-{{$teamDept[$d->team]}}</div>
								</div>
								<div class="datatable-col" style="width:80%">
									<div class="datatable-cell {{$class_str}}" data-name="amount#{{$d->team}}">
										<?php if($businessData->audit_status!=1 || $audit_edit){ ?>
											<span class="js-txt">{{$d->amount}}</span>
											<span class="red">{{$text}}</span>
										<?php }else{ ?>
											<span >{{$d->amount}}</span>
										<?php }?> 					
									</div>
								</div>
							</li>
							@endforeach
						@endif	
						@if ($team_amount->count() != 0)
            				@foreach ($team_amount as $d)	
							<li class="datatable-row">
								<div class="datatable-col" style="width:20%">
									<div class="datatable-cell" title="实际金额拆分-{{$teamDept[$d->team]}}">实际金额拆分-{{$teamDept[$d->team]}}</div>
								</div>
								<div class="datatable-col" style="width:80%">
									<div class="datatable-cell {{$class_str}}" data-name="active_amount#{{$d->team}}">
										<?php if($businessData->audit_status!=1 || $audit_edit){ ?>
											<span class="js-txt">{{$d->active_amount}}</span>
											<span class="red">{{$text}}</span>
										<?php }else{ ?>
											<span class="js-txt">{{$d->active_amount}}</span>
										<?php }?> 
									</div>
								</div>
							</li>
							@endforeach
						@endif	
				@endif	
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">执行总额</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell js-edit-amount" >
								{{$businessData->all_team_amount}}
								</div>
							</div>
						</li>	
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">发票总额</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell js-edit-amount">{{$businessData->invoince_amount}}</div>
							</div>
                    	</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
                            	<div class="datatable-cell">回款总额</div>
                        	</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								 <?php $backcash_total=0.00;?>
								  @foreach ($businessData->backcash_invoices as $de)
									<?php $backcash_total=$backcash_total+$de->active_amount;?>
								  @endforeach
								  <?php echo number_format($backcash_total,2);?>
								</div>
							</div>
                    	</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">支出总额</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								<?php $expenses_total=0.00;?>
								  @foreach ($businessData->expenses_deliveys as $de)
									<?php $expenses_total=$expenses_total+$de->active_amount;?>
								  @endforeach
								  <?php echo number_format($expenses_total,2);?>
								</div>
							</div>
                    	</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">付款总额</div>
							</div>
                        	<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								<?php $payment_total=0.00;?>
								  @foreach ($businessData->payment_expensess as $de)
									<?php $payment_total=$payment_total+$de->active_amount;?>
								  @endforeach
								  <?php echo number_format($payment_total,2);?>
								</div>
                        	</div>
                    	</li>
						<li class="datatable-row">
							<div class="datatable-col" style="width:20%">
								<div class="datatable-cell">坏账总额</div>
							</div>
							<div class="datatable-col" style="width:80%">
								<div class="datatable-cell">
								<?php $badcash_total=0.00;?>
								  @foreach ($businessData->badcashs as $de)
									<?php $badcash_total=$badcash_total+$de->amount;?>
								  @endforeach
								  <?php echo number_format($badcash_total,2);?>
								</div>
							</div>
						</li>	
                </ol>
				</div>
			<div class="datatable-ft" style="margin-top:20px; text-align:center;">
				
				<?php if($businessData->audit_status==0){?>
                <div class="datatable-ft" style="margin-top:20px; text-align:center;">
				 <a class="btn btn-blue js-bus-check" href="javascript:;" data-type="1">通过审核</a>
                 <a class="btn ml20 js-bus-check" href="javascript:;" data-type="2">不通过审核</a>
				</div>
				<?php } ?>
            </div>

            <div id="js-container1" class="mt10"></div>
			<div id="js-container2" class="mt10" <?php if(empty($executive_id)){?> style="display:none;" <?php } ?>></div>
            
        </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
    seajs.use('view/business/detail', function (app) {
        app({
            el: '#js-container1'
        });
    });
	seajs.use('view/execution/detail', function (app) {
        app({
            el: '#js-container2',
            tableOnly:false
        });
    });
</script>  

@endsection