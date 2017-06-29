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
                    data-url="{{ route('admin.statistics.export-excel.finance-info') }}"
                    id="export-btn">当前数据导出</a>   
                </div>
            </div>

           <form action="" method="get"  id="search-box">
                <table class="table table-listing" id="search-box">
                    <tbody>
                        {{-- 通用搜索类别 --}}
    					@include('admin.statistic.header-comm')
                        
                        <tr>
                            <td class="span4">业务发票状态</td>
                            <td>
                                <label><input type="checkbox" name="invoice_status[]" value="1" <?php if(!empty(Request::get('invoice_status')) && in_array('1',Request::get('invoice_status'))){echo 'checked="checked"'; }?> ><span class="pr10">已开全</span></label>
                                <label><input type="checkbox" name="invoice_status[]" value="0" <?php if(!empty(Request::get('invoice_status')) && in_array('0',Request::get('invoice_status'))){echo 'checked="checked"'; }?>><span class="pr10">未开全</span></label>
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
                            <td class="span4">业务编号</td>
                            <td>
                                <input type="text" name="business_key" class="input input-small" value="<?php echo Request::get('business_key'); ?>" >
                            </td>
                        </tr>
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="submit" class="btn btn-blue" value="查询"></td>
                        </tr>
                    </tbody>
                </table>
            </form>
            
            <table class="table table-listing mt20 border-table">
                <thead class="thead-gray">
                    <tr>
                        <th>业务编号</th>
                        <th>执行小组</th>
                        <th>业务线</th>
                        <th>合作方</th>
                        <th>客户名称</th>
                        <th>产品名称</th>
						<th>产品类型</th>
                        <th>实际金额拆分</th>
                        <th>执行总额</th>
                        <th>已开发票总额</th>
                        <th>回款总额</th>
                        <th>支出总额</th>
                        <th>坏账总额</th>
                        <th>回款状态</th>
                        <th>未开发票金额</th>
                        <th>执行应收</th>
                    </tr>
                </thead>
                <tbody>
                  @foreach ($listdata as $d)
                    <tr>
                        <td>{{$d['business_key']}}</td>
                        <td>{{$d['team']}}</td>
                        <td>{{$d['business_line']}}</td>
                        <td>{{$d['partner']}}</td>
                        <td>{{$d['company']}}</td>
                        <td>{{$d['product']}}</td>
						<td>{{$d['product_type']}}</td>
                        <td>{{$d['team_amount']}}</td>
                        <td>{{$d['delivery_amount']}}</td>
						<td>{{$d['invoice_amount']}}</td>
                        <td>{{$d['backcash_amount']}}</td>
						<td>{{$d['expenses_amount']}}</td>
                        <td>{{$d['badcash_amount']}}</td>
						<td>{{$d['backcash_status']}}</td>
                        <td>{{$d['losinvoice_amount']}}</td>
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
                    <td>{{$total['team_amount']}}</td>
                    <td>{{$total['delivery_amount']}}</td>
                    <td>{{$total['invoice_amount']}}</td>
					<td>{{$total['backcash_amount']}}</td>
                    <td>{{$total['expenses_amount']}}</td>
                    <td>{{$total['badcash_amount']}}</td>
                    <td></td>
                    <td>{{$total['losinvoice_amount']}}</td>
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