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
            invoiceBusList:"/authed/invoice/api/add-seach" , //录入发票展示相关业务列表 
			deleteAmount:"{{route('admin.invoice.delete-detail')}}" //删除执行额接口
        });
    </script>

            <div class="heading clearfix">
                <div class="pull-left">
					 <h2>发票管理>筛选待录入发票的业务：</h2>
                </div>
            </div>
		<form id="js-form-validate" action="{{ route('admin.invoice.store') }}" method="post">	
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">合作方：</td>
                            <td>
							<?php if(empty($invoice_id)){?>
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
                                <input type="text" name="business_key" class="input input-small js-business" value="<?php if(!empty($business_key)){echo $business_key;}?>" >(多个业务编号用,号分隔)
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="button" class="btn btn-blue js-search-btn" value="查询" data-type="auto">(重新查询可编辑条件下其他执行月的发票)</td>
                        </tr>
                    </tbody>
                </table>
            
			
            <div id="js-container" class="mt20">
                
            </div>

            <?php if(empty($invoice_id)){?>
            <h3>新增发票信息</h3>
			 <?php }else{?>
			 <h3>编辑发票信息</h3>
			 <?php }?>
            <table class="table table-listing table-hovered">
                <tbody>
                    <tr>
                        <td class="span4"><em class="text-red">* </em>发票金额：</td>
                        <td>
						<input type="text" class="input input-small"  id="total_amount" name="amount" value="" data-rules="required match(/^(-?\d+)(\.\d+)?$/)" data-msg="注:该项金额由系统自动累加业务表拆分金额，请查询业务并填写。" readonly="readonly">
                           
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>开票时间：</td>
                        <td>
                            <input class="input input-small js-calender" type="text" name="invoice_time" placeholder="" value="<?php if(!empty($invoiceData->invoice_time)){echo $invoiceData->invoice_time;}?>" readonly="readonly" data-rules="required">
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>发票编号：</td>
                        <td>
                            <input type="text" class="input input-small"  value="<?php if(!empty($invoiceData->invoice_key)){echo $invoiceData->invoice_key;}?>"  name="invoice_key"  data-rules="required">
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"><em class="text-red">* </em>发票类型：</td>
                        <td>
						<!--<input type="text" class="input input-small"  value="<?php if(!empty($invoiceData->invoice_type)){echo $invoiceData->invoice_type;}?>"  name="invoice_type"  data-rules="required">-->
						 <div style="display:inline-block" id="js-select-add-list"></div>
                            <a href="javascript:;" class="btn ml10" id="js-select-add-btn">新增</a>
                        </td>
                    </tr>

                    <tr>
                        <td class="span4">发票备注：</td>
                        <td>
                            <textarea name="remark" id="" cols="50" rows="5"><?php if(isset($invoiceData->remark)){echo $invoiceData->remark;}else{echo $remark;}?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="span4"></td>
                        <td>
							
                            <input type="hidden" name="invoice_id" value="{{$invoice_id}}" id="invoice_id">
							<?php if(empty($invoice_id)){?>
							<input class="btn btn-blue" type="submit" value="创建">
							 <?php }else{?>
							 <input class="btn btn-blue" type="submit" value="提交">
							 <?php }?>
                            
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
    seajs.use('view/invoice/add', function (app) {
        app({
            el:"#js-container"
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