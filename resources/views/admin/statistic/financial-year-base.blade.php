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
		 annualExecutionList:"{{route('admin.statistics.api.financial-year-base')}}"
        });
    </script>
   			<div class="heading clearfix">
                <div class="pull-left">
                    <h2>统计管理>年度执行统计</h2>
                </div>
                <div class="pull-right">
                    <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.statistics.export-excel.financial-year-base') }}"
                    id="export-btn">当前数据导出</a>     
                </div>
            </div>
			 <ul class="nav nav-tabs mt10 mb10">
				 <?php $k=0;$nowyear=$year;?>
			 	@foreach ($years as $y)
					<?php if(($year=="" && $k==0) || ($year==$y)){
					$nowyear=$y;
					?>
					<li class="active"><a href="{{ route('admin.statistics.tj.financial-year-base',$y) }}">{{$y}}年</a></li> 
					<?php }else{ ?>
					<li ><a href="{{ route('admin.statistics.tj.financial-year-base',$y) }}">{{$y}}年</a></li> 
					<?php } $k++; ?>
				@endforeach	     
            </ul>
           <form action="" method="get"  id="search-box">
		   		  <input type="hidden" value="<?php echo $nowyear;?>" name="year" />	
                  <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4">执行月份：</td>
                            <td>
                                <input class="input input-small" type="text" id="_month1" name="bmonth" value="{{Request::get('bmonth')}}" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_month2" name="emonth" value="{{Request::get('emonth')}}" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">发票开票时间：</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender1" name="btime" value="{{Request::get('btime')}}" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender2" name="etime" value="{{Request::get('etime')}}" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">回款时间</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender3" name="refund_btime" value="{{Request::get('refund_btime')}}" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender4" name="refund_etime" value="{{Request::get('refund_etime')}}" readonly="readonly">
                            </td>
                        </tr>

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
                      
                        <tr class="tr_bg">
                            <th></th>
                            <td><input type="submit" class="btn btn-blue" value="查询"></td>
                        </tr>
                    </tbody>
                </table>
            </form>
           <div id="js-container" class="mt20"></div>
	<div class="clearfix mt10">
			
    </div>
@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
    seajs.use('view/statistic/list', function (app) {
        app({
            el:"#js-container",
            listAction:ST.ACTION.annualExecutionList
        });
    });
	seajs.use('lang/common', function (b) {
		b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
       });
    })
</script>

@endsection