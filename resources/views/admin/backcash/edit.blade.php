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
            refundBusList:"/authed/backcash/api/add-seach" , //录入发票展示相关业务列表 
			deleteAmount:"{{route('admin.backcash.delete-detail')}}" //删除执行额接口
        });
    </script>

            <div class="heading clearfix">
                <div class="pull-left">
					 <h2>回款管理>筛选待录入回款的业务</h2>
                </div>
            </div>
		<form id="js-form-validate" action="{{ route('admin.backcash.store') }}" method="post">	
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">合作方：</td>
                            <td>
							<?php if(empty($backcash_id)){?>
							 <select name="partner_id" class="js-selectbox js-partner" placeholder="请选择" >
									<option value="">--请选择--</option>
                                   @foreach ($partnerData as $d)
                                    <option value="{{$d->id}}" <?php if($d->id==$partner_id){echo 'selected="selected"';}?> >{{$d->company_name}}</option>
							   		@endforeach	
                                </select>
							 <?php }else{?>
                                   @foreach ($partnerData as $d)
                                     <?php if($d->id==$partner_id){
									 echo $d->company_name;
									 echo '<input type="hidden" value="'.$partner_id.'" class="js-partner" name="partner_id" id="partner_id" />';
									 }?> 
							   		@endforeach	
									<input type="hidden" name="times" class="input input-small js-times" value="" >
							 <?php }?>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">业务编号</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small js-business" value="<?php if(!empty($business_key)){echo $business_key;}?>" >
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

            <?php if(empty($backcash_id)){?>
            <h3>新增回款信息</h3>
			 <?php }else{?>
			 <h3>编辑回款信息</h3>
			 <?php }?>
            <table class="table table-listing table-hovered">
                <tbody>
                    <tr>
                        <td class="span4"><em class="text-red">* </em>回款金额：</td>
                        <td>
						<input type="text" class="input input-small"  id="total_amount" name="amount" value="" data-rules="required match(/^(-?\d+)(\.\d+)?$/)" data-msg="注:该项金额由系统自动累加业务表拆分金额，请查询业务并填写。" readonly="readonly">
                           
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>回款时间：</td>
                        <td>
                            <input class="input input-small js-calender" type="text" name="backtime" placeholder="" value="<?php if(!empty($backcashData->backtime)){echo $backcashData->backtime;}?>" readonly="readonly" data-rules="required">
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>回款银行：</td>
                        <td>
						<select name="bank" class="js-selectbox js-partner" placeholder="请选择" data-rules="required">
									<option value="">--请选择--</option>
                                   @foreach ($bankData as $d)
                                    <option value="{{$d['key']}}" <?php if(!empty($backcashData->bank) && $backcashData->bank==$d['key']){echo 'selected="selected"';}?>  >{{$d['value']}}</option>
							   		@endforeach	
                         </select> 
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>回款方式：</td>
                        <td>
						@foreach ($backcash_typeData as $d)
						 <label>
                                <input type="radio" id="" name="backtype" value="{{$d['key']}}" <?php if(!empty($backcashData->backtype) && $backcashData->backtype==$d['key']){echo 'checked="checked"';}?>
                                data-group="refund_type" data-rules="least" data-holder="#refund_type_help" >
                                <span class="pr10">{{$d['value']}}</span>
                         </label>
						@endforeach	
						<div id="refund_type_help" class="inline-block"> </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="span4">回款备注：</td>
                        <td>
                            <textarea name="remark" id="" cols="50" rows="5"><?php if(!empty($backcashData->remark)){echo $backcashData->remark;}?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"></td>
                        <td>
							
                            <input type="hidden" name="backcash_id" value="{{$backcash_id}}" id="refund_id">
                            <input class="btn btn-blue" type="submit" value="创建">
                            <a class="btn" href="javascript:history.back(-1);">取消</a>
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
    

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
<script>
    seajs.use('view/refund/add', function (app) {
        app({
            el:"#js-container"
        });
    });
</script>

@endsection