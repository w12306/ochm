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
                    <h2>统计管理->业务执行金额总表</h2>
                </div>
                <div class="pull-right">
                   <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.statistics.export-excel.business-action') }}"
                    id="export-btn">当前数据导出</a>
                </div>
            </div>

            <form action="" method="get"  id="search-box">
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">客户名称</td>
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
                            <td class="span4">合作方</td>
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
                            <td class="span4">业务类型</td>
                            <td>
                               @foreach ($business_type as $d)
								<?php if(!empty(Request::get('business_type'))){ ?>
								<label><input type="checkbox" name="business_type[]" value="{{$d['key']}}" <?php if(in_array($d['key'],Request::get('business_type'))){echo 'checked="checked"'; }?>   />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php }else{ ?>
								<label><input type="checkbox" name="business_type[]" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php } ?>
								@endforeach
                            </td>
                        </tr>

                        <tr><!-- 联动-->
                            <td class="span4">业务线</td>
                            <td>
                               @foreach ($business_line as $d)
								<?php if(!empty(Request::get('business_line'))){ ?>
								<label><input type="checkbox" name="business_line[]" value="{{$d['key']}}"  <?php if(in_array($d['key'],Request::get('business_line'))){echo 'checked="checked"'; }?> />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php }else{ ?>
								<label><input type="checkbox" name="business_line[]" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php } ?>
							@endforeach
                            </td>
                        </tr>

                        <tr><!-- 联动-->
                            <td class="span4">执行小组</td>
                            <td>
                               @foreach ($team as $d)
								<?php if(!empty(Request::get('team'))){ ?>
								<label><input type="checkbox" name="team[]" value="{{$d['key']}}"  <?php if(in_array($d['key'],Request::get('team'))){echo 'checked="checked"'; }?> />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php }else{ ?>
								<label><input type="checkbox" name="team[]" value="{{$d['key']}}"  />&nbsp;&nbsp;{{$d['value']}}&nbsp;&nbsp;</label>
								<?php } ?>
								
							   @endforeach 
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">执行月份：</td>
                            <td>
                                <input class="input input-small" type="text" id="_month1" name="bmonth" value="" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_month2" name="emonth" value="" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">业务编号</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small" value="" >
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
                        <th>客户名称</th>
                        <th>业务线</th>
                        <th>业务类型</th>
                        <th>产品名称</th>
                        <th>实际金额拆分</th>
                        <th>执行金额</th>
                        <th>支出总和</th>
                        <th>发票总和</th>
                        <th>回款总和</th>
                        <th>坏账金额</th>
                        <th>月利润</th>
                        <th>执行应收</th>
                    </tr>
                </thead>
                <tbody>
					@foreach ($listdata as $d)
                    <tr>
                        <td>{{$d['business_key']}}</td>
                        <td>{{$d['team']}}</td>
                        <td>{{$d['month']}}</td>
                        <td>{{$d['partner']}}</td>
                        <td>{{$d['company']}}</td>
                        <td>{{$d['business_line']}}</td>
                        <td>{{$d['business_type']}}</td>
                        <td>{{$d['product']}}</td>
                        <td>{{$d['team_amount']}}</td>
                        <td>{{$d['month_amount']}}</td>
                        <td>{{$d['expenses_amount']}}</td>
                        <td>{{$d['invoice_amount']}}</td>
                        <td>{{$d['backcash_amount']}}</td>
                        <td>{{$d['badcash_amount']}}</td>
                        <td>{{$d['profit']}}</td>
                        <td>{{$d['income']}}</td>
                    </tr>   
					@endforeach                
                </tbody>
                <tfoot>
                    <td>合计</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{$total['team_amount']}}</td>
                    <td>{{$total['month_amount']}}</td>
                    <td>{{$total['expenses_amount']}}</td>
                    <td>{{$total['invoice_amount']}}</td>
                    <td>{{$total['backcash_amount']}}</td>
                    <td>{{$total['badcash_amount']}}</td>
                    <td>{{$total['profit']}}</td>
                    <td>{{$total['income']}}</td>
                </tfoot>
            </table>
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