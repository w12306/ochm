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
					 <h2>支出管理>筛选录入付款业务</h2>
                </div>
            </div>
			<ul class="nav nav-tabs mt10 mb10">
                <li ><a href="{{ route('admin.payment.add')}}">业务付款</a></li>    
                <li class="active"><a href="#">独立付款</a></li>     
            </ul>
		 <form id="js-form-validate" action="{{ route('admin.stand-payment.store') }}" method="post" onSubmit="return false"  id="search-box">
                 <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4"><em class="text-red">* </em>支出单号：</td>
                            <td>
								<select name="expenses_id" class="js-selectbox js-partner" placeholder="请选择"  data-rules="required"  data-url="{{ route('admin.stand-payment.api.get-expenses-data') }}" >
								    @foreach ($expensesData as $d)
                                    <option value="{{$d->id}}" <?php if(isset($paymentData->expenses_id) && $paymentData->expenses_id==$d->id){echo 'selected="selected"';}?>  >{{$d->expenses_key}}</option>
							   		@endforeach	
                                  
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>支出金额：</td>
                            <td class="js-amount">
                                0.00
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>下游渠道名称：</td>
                            <td class="js-name">
                                
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>付款金额：</td>
                            <td>
                                <input type="text" name="payment_amount" class="input input-small" value="<?php if (isset($paymentData->payment_amount)){echo $paymentData->payment_amount;}?>" data-rules="required match(/^(-?\d+)(\.\d+)?$/)"  data-msg="必填 请输入数值">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>付款时间：</td>
                            <td>
                                <input class="input input-small js-calender" type="text" name="payment_time" placeholder="" value="<?php if (isset($paymentData->payment_time)){echo $paymentData->payment_time;}?>" readonly="readonly" data-rules="required" >
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>发票编号：</td>
                            <td>
                                <input type="text" name="invoice_num" class="input input-small" value="<?php if (isset($paymentData->invoice_num)){echo $paymentData->invoice_num;}?>" data-rules="required" >
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>发票金额：</td>
                            <td>
                                <input type="text" name="invoice_amount" class="input input-small" value="<?php if (isset($paymentData->invoice_amount)){echo $paymentData->invoice_amount;}?>" data-rules="required match(/^(-?\d+)(\.\d+)?$/)" maxlength="9"  data-msg="必填 请输入数值">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4"><em class="text-red">* </em>发票类型：</td>
                            <td>
                              <!--  <input type="text" name="invoice_type" class="input input-small" value="<?php if (isset($paymentData->invoice_type)){echo $paymentData->invoice_type;}?>" data-rules="required" >-->
							  <div style="display:inline-block" id="js-select-add-list"></div>
                            <a href="javascript:;" class="btn ml10" id="js-select-add-btn">新增</a>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">付款备注：</td>
                            <td>
                                <textarea name="remark" id="" cols="50" rows="3"><?php if (isset($paymentData->remark)){echo $paymentData->remark;}?></textarea>
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td>
                               <input type="hidden" name="id" value="<?php if (isset($paymentData->id)){echo $paymentData->id;}?>">
                                <input class="btn btn-blue" type="submit" value="确定">
                                <a class="btn" href="{{route('admin.stand-payment.list')}}">取消</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
<script>
   seajs.use('lang/common', function (app) {
        app.com();
        app.formValidate();
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

    $(function(){
        $(".js-partner").on("change",function(){
            getRelate();
        });

        /*if($(".js-partner").val()){
            getRelate();
        }*/

        function getRelate(){
            var val = $(".js-partner").val(),
                url = $(".js-partner").data("url");
            $.ajax({
                url:url,
                type:"post",
                data:{expenses_id:val},
                dataType:"json",
                success:function(d){
                    if(d.data){
                        $(".js-amount").html(d.data.amount);
                        $(".js-name").html(d.data.under_partner);
                    }
                    
                }
            })
        }
    })
</script>

@endsection