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
                    <h2>统计管理->业务法务信息总表</h2>
                </div>
                <div class="pull-right">
                    <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.statistics.export-excel.business-legal') }}"
                    id="export-btn">当前数据导出</a>     
                </div>
            </div>

           <form action="" method="get"  id="search-box">
                <table class="table table-listing" id="search-box">
                    <tbody>
                        {{-- 通用搜索类别 --}}
    					@include('admin.statistic.header-comm')
                        
                        <tr>
                            <td class="span4">合同状态</td>
                            <td>
							@foreach ($contract_status as $k=>$d)
                                <label><input type="checkbox" name="contract_type[]" value="{{$k}}" <?php if(!empty(Request::get('contract_type')) && in_array($k,Request::get('contract_type'))){echo 'checked="checked"'; }?> ><span class="pr10">{{$d}}</span></label>
							@endforeach  	
                               
                                <label><input type="checkbox" name="business_contract_type" value="4" <?php if(!empty(Request::get('business_contract_type')) ){echo 'checked="checked"'; }?>><span class="pr10">特殊无合同</span></label>
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
                        <th>合同编号</th>
                        <th>实际金额拆分</th>
						<th>回款总额</th>
                        <th>合同状态</th>
                        <th>确认函状态</th>
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
						<td>{{$d['contract_key']}}</td>
                        <td>{{$d['team_amount']}}</td>
						<td>{{$d['backcash_amount']}}</td>
                        <td>{{$d['contract_status']}}</td>
                        <td>{{$d['confirm_status']}}</td>
                    </tr>   
					@endforeach                    
                </tbody>
               

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