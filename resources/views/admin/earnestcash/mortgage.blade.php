{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>录入保证金 - {{env('SITENAME')}}</title>

    <script>
         $.extend(ST.ACTION,{
            advanceBusList:"{{route('admin.earnestcash.api.addmortgage-api')}}",  //录入发票展示相关业务列表 
           // deleteAmount:"/resource/jsData/success1.json" //删除执行额接口
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')
            <div class="heading clearfix">
                <div class="pull-left">
                    <h2>保证金管理>保证金转回款</h2>
                </div>
            </div>

            <table class="table table-listing" id="search-box">
                <tbody>
                    <tr>
                        <td class="span4">合作方：</td>
                        <td width="200">
							{{$mortgage->partner}}
                           
                        </td>
						
						 <td class="span4">保证金金额：</td>
                        <td>
                            {{$mortgage->amount}}
                        </td>
                    </tr>


                    <tr>
                        <td class="span4">收款时间：</td>
                        <td>
                            {{$mortgage->reception_time}}
                        </td>
						<td class="span4">剩余预收款金额：</td>
                        <td>
                            {{$mortgage->last_amount}}
                        </td>
						
                    </tr>

         

                    <tr>
                        <td class="span4">票据类型：</td>
                        <td>
                           {{$mortgage->bill_type}}
                        </td>
						
						 <td class="span4">票据编号：</td>
                        <td>
                            {{$mortgage->bill_num}}
                        </td>
                    </tr>
                    <tr>
                        <td class="span4">备注：</td>
                        <td >
                            {{$mortgage->remark}}
                        </td>
						<td class="span4">收款银行：</td>
                        <td>
                            {{$mortgage->bank}}
                        </td>
                    </tr>
                </tbody>
            </table>

            

            <form id="js-form-validate" action="{{route('admin.earnestcash.storemortgage')}}" method="post">
			 <input type="hidden" data-id="{{$mortgage->partner_id}}" name="partner_id" value="{{$mortgage->partner_id}}" class="js-partner">
            <h3 class="mt20">新增发票回款信息</h3>
            <table class="table table-listing table-hovered">
                <tbody>
                    <tr>
                        <td class="span4"><em class="text-red">* </em>开票时间：</td>
                        <td width="200">
                            <input class="input input-small js-calender" type="text" name="invoice_time" placeholder="" value="{{$mortgage->reception_time}}" readonly="readonly" data-rules="required">
                        </td>
						
						 <td class="span4"><em class="text-red">* </em>回款时间：</td>
                        <td>
                            <input class="input input-small js-calender" type="text" name="backcash_time" placeholder="" value="{{$mortgage->reception_time}}" readonly="readonly" data-rules="required">
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>发票编号：</td>
                        <td>
                            <input type="text" class="input input-small" type="text" id="invoice_key" name="invoice_key" value="00000000" data-rules="required">
                        </td>
						
						  <td class="span4"><em class="text-red">* </em>回款银行：</td>
                        <td>
                            <select name="backcash_bank" class="js-selectbox js-partner" placeholder="请选择" data-rules="required">
									<option value="">--请选择--</option>
                                   @foreach ($bankData as $d)
                                    <option value="{{$d['key']}}" <?php if(!empty($mortgage->bank) && $mortgage->bank==$d['key']){echo 'selected="selected"';}?>  >{{$d['value']}}</option>
							   		@endforeach	
                         </select> 
                        </td>
                    </tr>
					<tr>
                        <td class="span4"><em class="text-red">* </em>发票类型：</td>
                        <td>
                            <input type="text" class="input input-small" type="text" id="invoice_type" name="invoice_type" value="{{$mortgage->bill_type}}" data-rules="required">
                        </td>
						
						 <td class="span4"><em class="text-red">* </em>回款方式：</td>
                        <td >
                            @foreach ($backcash_typeData as $d)
						 <label>
                                <input type="radio" id="backtype" name="backtype" value="{{$d['key']}}" <?php if(!empty($backcashData->backtype) && $backcashData->backtype==$d['key']){echo 'checked="checked"';}?>
                                data-group="refund_type" data-rules="least" data-holder="#refund_type_help" >
                                <span class="pr10">{{$d['value']}}</span>
                         </label>
						@endforeach	
						<div id="refund_type_help" class="inline-block"> </div>
                        </td>
                    </tr>

                    
                    <tr>
                        <td class="span4">发票/回款备注：</td>
                        <td  colspan="3">
                            <textarea name="remark" id="" cols="73" rows="5">保证金（单号：{{$mortgage->earnestcash_key}}）转回款，开发票_（提交后自动填充）_元，回款金额_（提交后自动填充）_元</textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>发票金额：</td>
                        <td>
                            <input type="text" class="input input-small" type="text" id="total_amount" name="total_amount" value="" data-rules="required match(/^(-?\d+)(\.\d+)?$/)" data-msg="必填 请输入数值" readonly="readonly">
                        </td>
						
						<td class="span4"><em class="text-red">* </em>回款金额：</td>
                        <td>
                            <input type="text" class="input input-small" type="text" id="total_amount_refund" name="total_amount_refund" value="" data-rules="required match(/^(-?\d+)(\.\d+)?$/)" data-msg="必填 请输入数值" readonly="readonly">
                        </td>
                    </tr>

                  
                    <tr>
                        <td class="span4">业务编号：</td>
                        <td>
                            <input type="text" class="input input-small js-business" type="text" id="business_key" name="business_key" value="" >
                            
                            
                        </td>
						
						 <td class="span4"></td>
                        <td>
                           <input type="button" class="btn btn-blue js-search-btn" value="筛选" data-type="auto" style="margin-left:20px;">
                        </td>
                    </tr>


                </tbody>
            </table>
			 <div id="js-container" class="mt20">
                    
              </div>
			<div style="margin-left:200px;" >
							<input type="hidden" value="{{$mortgage->id}}" name="earnestcash_id"  id="earnestcash_id" />
							<input type="hidden" value="{{$mortgage->earnestcash_key}}" name="earnestcash_key"  />
                            <input class="btn btn-blue" type="submit" value="创建">
                            <a class="btn" href="{{route('admin.earnestcash.list')}}">取消</a>
            </div>
			</form>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('view/advance/add', function (app) {
        app({
            el:"#js-container"
        });
    });


    </script>
@endsection