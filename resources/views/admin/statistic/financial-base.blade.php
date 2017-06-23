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
        });
    </script>
   			<div class="heading clearfix">
                <div class="pull-left">
                    <h2>统计管理->业务财务信息总表</h2>
                </div>
                <div class="pull-right">
                   <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.statistics.export-excel.financial-base') }}"
                    id="export-btn">当前数据导出</a>   
                </div>
            </div>

           <form action="" method="get"  id="search-box">
                 <table class="table table-listing">
                    <tbody>
                      {{-- 通用搜索类别 --}}
    					@include('admin.statistic.header-comm')
						
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
                            <td class="span4">回款状态</td>
                            <td>
                                <label><input type="checkbox" name="backcash_status[]" value="1" <?php if(!empty(Request::get('backcash_status')) && in_array('1',Request::get('backcash_status'))){echo 'checked="checked"'; }?>><span class="pr10">已结清</span></label>
                                <label><input type="checkbox" name="backcash_status[]" value="0" <?php if(!empty(Request::get('backcash_status')) && in_array('0',Request::get('backcash_status'))){echo 'checked="checked"';} ?>><span class="pr10">未结清</span></label>
                            </td>
                        </tr>

                       <tr>
                            <td class="span4">合同状态</td>
                            <td>
							@foreach ($contract_status as $k=>$d)
                                <label><input type="checkbox" name="contract_status[]" value="{{$k}}" <?php if(!empty(Request::get('contract_status')) && in_array($k,Request::get('contract_status'))){echo 'checked="checked"'; }?> ><span class="pr10">{{$d}}</span></label>
							@endforeach  	
                               
                            </td>
                        </tr>

                         <tr>
                            <td class="span4">确认函状态</td>
                            <td>
							@foreach ($confirm_status as $k=>$d)
                                <label><input type="checkbox" name="confirm_status[]" value="{{$k}}" <?php if(!empty(Request::get('confirm_status')) && in_array($k,Request::get('confirm_status'))){echo 'checked="checked"'; }?>><span class="pr10">{{$d}}</span></label>
							@endforeach  	
                              
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">业务编号</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small" value="<?php echo Request::get('business_key'); ?>" >
                            </td>
                        </tr>


                       <tr>
                            <td class="span4">执行月份：</td>
                            <td>
                               <input class="input input-small" type="text" id="_month1" name="bmonth" value="<?php echo Request::get('bmonth'); ?>" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_month2" name="emonth" value="<?php echo Request::get('emonth'); ?>" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">发票开票时间：</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender1" name="btime" value="<?php echo Request::get('btime'); ?>" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender2" name="etime" value="<?php echo Request::get('etime'); ?>" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">回款时间</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender3" name="refund_btime" value="<?php echo Request::get('refund_btime'); ?>" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender4" name="refund_etime" value="<?php echo Request::get('refund_etime'); ?>" readonly="readonly">
                            </td>
                        </tr>
                        

                        <tr>
                            <td class="span4">定制显示字段：</td>
                            <td class="js-tb-name">
							@foreach ($formfields as $k=>$d)
                                <label><input type="checkbox" name="fields[]" value="{{$k}}" <?php if(!empty($tablefields) && in_array($d,$tablefields)){echo 'checked="checked"';}?>  ><span class="pr10">{{$d}}</span></label>
							@endforeach  	
                                <p class="mt10">(业务编号和执行小组是基础字段，其他选项最多允许选择10项，若超过十项，可选择当前数据导出xls，xls是全字段表)</p>
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="submit" name="submit" class="btn btn-blue" value="查询"></td>
                        </tr>
                    </tbody>
                </table>
            </form>
            <?php if(!empty($list)){ ?>
            <table class="table table-listing mt20 border-table">
                <thead class="thead-gray">
                    <tr>
                        <th>业务编号</th>
                        <th>执行小组</th>
						@foreach ($tablefields as $k=>$d)
                        <th>{{$d}}</th>
						@endforeach 
                    </tr>
                </thead>
				 @foreach ($list as $k=>$d)
				 <tr>
                        <td>{{$d['business_key']}}</td>
                        <td>{{$d['team']}}</td>
						@foreach ($tablefields as $lk=>$ld)
						<?php 
						if($lk=="team_month_invoice"){
							if(isset($total['invoiceTotal'][$d['delivery_id']])){
								echo '<td>'.number_format($total['invoiceTotal'][$d['delivery_id']],2).'</td>';
							}else{
								echo '<td>0.00</td>';	
							}	
						}else if($lk=="team_month_backcash"){
							if(isset($total['backcashTotal'][$d['delivery_id']])){
								echo '<td>'.number_format($total['backcashTotal'][$d['delivery_id']],2).'</td>';
							}else{
								echo '<td>0.00</td>';	
							}
						}else if($lk=="team_month_expenses"){
							if(isset($total['expensesTotal'][$d['delivery_id']])){
								echo '<td>'.number_format($total['expensesTotal'][$d['delivery_id']],2).'</td>';
							}else{
								echo '<td>0.00</td>';	
							}
						}else if($lk=="team_month_badcash"){
							if(isset($total['badcashTotal'][$d['delivery_id']])){
								echo '<td>'.number_format($total['badcashTotal'][$d['delivery_id']],2).'</td>';
							}else{
								echo '<td>0.00</td>';	
							}
						}else if($lk=="yszk_amount"){
							$team_month_expenses=0.00;
							$team_month_backcash=0.00;
							$team_month_badcash=0.00;
							if(isset($total['expensesTotal'][$d['delivery_id']])){
								$team_month_expenses=$total['expensesTotal'][$d['delivery_id']];
							}
							if(isset($total['backcashTotal'][$d['delivery_id']])){
								$team_month_backcash=$total['backcashTotal'][$d['delivery_id']];
							}
							if(isset($total['badcashTotal'][$d['delivery_id']])){
								$team_month_badcash=$total['badcashTotal'][$d['delivery_id']];
							}
							$sum=$d['team_month_amount']-$team_month_expenses-$team_month_backcash-$team_month_badcash;
							echo '<td>'.$sum.'</td>';
						}else{
							echo '<td>'.$d[$lk].'</td>';
						}?>
						@endforeach 
                 </tr>
				 @endforeach 
                <tbody>         
                </tbody>
            </table>
			<?php } ?>
	<div class="clearfix mt10">
			
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
 seajs.use('lang/common', function (b) {
        b.com();
		b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
       });
    })
</script>

@endsection