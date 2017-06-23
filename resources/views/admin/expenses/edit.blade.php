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
            addBusExpnedList:"{{ route('admin.expenses.api.add-expenses-data') }} " , //录入支出 查询月执行额 
			deleteAmount:"{{route('admin.expenses.delete-detail')}}" //删除执行额接口
        });
    </script>

            <div class="heading clearfix">
                <div class="pull-left">
					 <h2>支出管理>筛选录入支出业务</h2>
                </div>
            </div>
			<ul class="nav nav-tabs mt10 mb10">
                <li class="active"><a href="{{ route('admin.expenses.add-expenses')}}">业务支出</a></li>    
                <li><a href="{{ route('admin.stand-expenses.add-expenses')}}">独立支出</a></li>     
            </ul>
		 <form id="js-form-validate" action="{{ route('admin.expenses.store') }}" method="post" onSubmit="return false"  id="search-box">
                <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4"><em class="text-red">* </em>下游合作方名称：</td>
                            <td>
                                <select name="under_partner_id" class="js-selectbox " placeholder="请选择" data-rules="required" >
									<option value="">--请选择--</option>
                                   @foreach ($under_partnerData as $d)
                                    <option value="{{$d->id}}" <?php if(isset($expensesData->under_partner_id) && $expensesData->under_partner_id==$d->id){echo 'selected="selected"';}?>  >{{$d->company_name}}</option>
							   		@endforeach	
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>支出类型：</td>
                            <td>
							
							 @foreach ($expenses_type as $d)
							 	<label class="ml10">
                                <input type="radio" name="expenses_type" value="{{$d['key']}}" data-group="expend_type" data-rules="least" data-holder="#expend_type_help" 
								<?php if(isset($expensesData->expenses_type) && $expensesData->expenses_type==$d['key']){echo 'checked="checked"';}?>>{{$d['value']}}
                                </label>
							 @endforeach	
                              <font color="#FF0000">&nbsp;&nbsp;回款结清：回款总额+坏账总额+费用总额=实际总金额，不涉及回款的请不要选择“费用”类型</font>
                                <div id="expend_type_help" class="inline-block"></div>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>下游合作形式：</td>
                            <td>
							@foreach ($cooperation as $k=>$d)
                                   <label class="ml10">
                                <input type="radio" name="cooperation_type" value="{{$d}}" data-group="cooperation_type" data-rules="least" data-holder="#cooperation_type_help" 
								 <?php if(isset($expensesData->cooperation_type) && $expensesData->cooperation_type==$d){echo 'checked="checked"';}?>>{{$d}}
                                </label>
							@endforeach	
                                <div id="cooperation_type_help" class="inline-block"></div>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">合同编号：</td>
                            <td>
                                <input type="text" name="contract_key" class="input input-small" value="<?php if(isset($expensesData->contract_key)){echo $expensesData->contract_key;}?>">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>预约付款时间：</td>
                            <td>
                                <input class="input input-small js-calender" type="text" name="payment_time" placeholder="" value="<?php if(isset($expensesData->payment_time)){echo $expensesData->payment_time;}?>" readonly="readonly"  data-rules="required" >
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">合同有效期：</td>
                            <td>
                                <span>
                                    <input class="input input-small" type="text" id="_calender1" name="btime" placeholder="" value="<?php if(isset($expensesData->contract_btime)){echo $expensesData->contract_btime;}?>" readonly="readonly">
                                </span>&nbsp;-&nbsp;
                                <span>
                                    <input class="input input-small" type="text" id="_calender2" name="etime" placeholder="" value="<?php if(isset($expensesData->contract_etime)){echo $expensesData->contract_etime;}?>" readonly="readonly">
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">支出备注：</td>
                            <td>
                                <textarea name="remark" id="" cols="50" rows="3"><?php if(isset($expensesData->remark)){echo $expensesData->remark;}?></textarea>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>支出金额：</td>
                            <td>
                                <input type="text" name="amount" class="input input-small" id="total_amount" value="" data-rules="required match(/^(-?\d+)(\.\d+)?$/)"  data-msg="注:该项金额由系统自动累加业务表拆分金额，请查询业务并填写。" readonly="readonly">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="table table-listing mt20" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">合作方：</td>
                            <td>
                                <select name="partner_id" class="js-selectbox js-partner" placeholder="请选择" >
									<option value="">--请选择--</option>
                                   @foreach ($partnerData as $d)
                                    <option value="{{$d->id}}" <?php if(isset($partner_id) && $d->id==$partner_id){echo 'selected="selected"';}?> >{{$d->company_name}}</option>
							   		@endforeach	
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">业务编号：</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small js-business" value="<?php if(isset($business_key)){echo $business_key;}?>" >
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="button" class="btn btn-blue js-search-btn" value="查询" data-type="auto"></td>
                        </tr>
                    </tbody>
                </table>


                <div id="js-container" class="mt20">
                    
                </div>

                <div class="center">
                    <input type="hidden" name="expenses_id" value="<?php if(isset($expensesData->id)){echo $expensesData->id;}?>" id="id">
                    <input class="btn btn-blue" type="submit" value="确定">
                    <a class="btn" href="{{route('admin.expenses.list-expenses')}}">取消</a>
                </div>
            </form>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
<script>
    seajs.use('view/expend/add', function (app) {
        app({
            el:"#js-container",
            listAction:ST.ACTION.addBusExpnedList
        });
    });
</script>

@endsection