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
            addBusPaymentList:"{{ route('admin.payment.api.add-payment-data') }}" , //录入展示相关业务列表
			deleteAmount:"{{route('admin.payment.delete-detail')}}" //删除执行额接口
        });
    </script>

            <div class="heading clearfix">
                <div class="pull-left">
					 <h2>支出管理>筛选录入付款业务</h2>
                </div>
            </div>
			<ul class="nav nav-tabs mt10 mb10">
                <li class="active"><a href="{{ route('admin.payment.add')}}">业务付款</a></li>    
                <li><a href="{{ route('admin.stand-payment.add-payment')}}">独立付款</a></li>     
            </ul>
		 <form id="js-form-validate" action="{{ route('admin.payment.store') }}" method="post" onSubmit="return false"  id="search-box">
                 <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4"><em class="text-red">* </em>付款时间：</td>
                            <td><input class="input input-small js-calender" type="text" name="payment_time" placeholder="" value="<?php if(isset($paymentData->payment_time)){echo $paymentData->payment_time;}?>" readonly="readonly"  data-rules="required" >
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>发票编号：</td>
                            <td>
                                <input type="text" name="invoice_num" class="input input-small" value="<?php if(isset($paymentData->invoice_num)){echo $paymentData->invoice_num;}?>" data-rules="required" >
                            </td>
                        </tr>

                         <tr>
                            <td class="span4"><em class="text-red">* </em>发票金额：</td>
                            <td>
                                <input type="text" name="invoice_amount" class="input input-small" value="<?php if(isset($paymentData->invoice_amount)){echo $paymentData->invoice_amount;}?>" data-rules="required match(/^(-?\d+)(\.\d+)?$/)"  data-msg="必填 请输入数值">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>发票类型：</td>
                            <td>
                                <!--<input type="text" name="invoice_type" class="input input-small" value="<?php if(isset($paymentData->invoice_type)){echo $paymentData->invoice_type;}?>" data-rules="required" >-->
								<div style="display:inline-block" id="js-select-add-list"></div>
                            <a href="javascript:;" class="btn ml10" id="js-select-add-btn">新增</a>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">付款备注：</td>
                            <td>
                                <textarea name="remark" id="" cols="50" rows="3"><?php if(isset($paymentData->remark)){echo $paymentData->remark;}?></textarea>
                            </td>
                        </tr>
						 <tr>
                            <td class="span4"><em class="text-red">* </em>付款金额：</td>
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
                                <select name="partner_id" class="js-selectbox js-partner" placeholder="请选择">
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

				<?php if(isset($paymentData->id)){
				echo "<br>注意:付款金额修改成 0  保存后则会被系统自动删除该条明细！";
				}
				?>
                <div id="js-container" class="mt20">
                    
                </div>

                <div class="center">
                    <input type="hidden" name="payment_id" value="<?php if(isset($paymentData->id)){echo $paymentData->id;}?>" id="id">
                    <input class="btn btn-blue" type="submit" value="确定">
                    <a class="btn" href="{{route('admin.payment.list')}}">取消</a>
                </div>
            </form>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
<script>
  seajs.use('view/expend/add', function (app) {
        app({
            el:"#js-container",
            listAction:ST.ACTION.addBusPaymentList
        });
    });
	 seajs.use("lang/common",function(com){
        com.selectAdd({
            id:"js-select-add-list", /*选择框父元素id*/
            btn:"js-select-add-btn", /*添加按钮id*/
            listUrl:"{{route('admin.config.dictionary.api.invoice_type')}}", /*显示列表*/
            addUrl:"{{route('admin.config.dictionary.api.add-data')}}", /*添加接口*/
            name:"invoice_type", //select的name值
            rules:"required", //是否为必填项目
            type:"invoice_type" //添加时传的type
        });
    })
</script>

@endsection