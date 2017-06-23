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
		profitList:"{{route('admin.statistics.api.business-profit')}}",  //获取月利润统计
        });
    </script>

			 <div class="heading clearfix">
                <div class="pull-left">
                    <h2>统计管理>执行小组月利润统计</h2>
                </div>
                <div class="pull-right">
                    <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.statistics.export-excel.business-profit') }}"
                    id="export-btn">当前数据导出</a>     
                </div>
            </div>

			<form action="" method="" onSubmit="return false"  id="search-box">
                        <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">执行小组</td>
                            <td>
                              <table class="table table-listing">
                        <tbody>
						<?php foreach($team as $k=>$t){  ?>
						<tr>
                            <td style="width: 100px; text-align: right;">
                        	<span style="text-align:left; width: auto;" class="inline-block span3"><?php echo $k; ?></span>
                            </td>
                            <td>
                            <?php foreach($t as $d){  ?>
								<?php if(!empty(Request::get('team'))){ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" <?php if(in_array($d['value'],Request::get('team'))){echo 'checked="checked"'; }?> data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]">{{$d['key']}}
                            	</label>
								<?php }else{ ?>
								<label style="text-align:left;width: auto;margin-left: 10px;" class="inline-block span3" for="">
                               <input type="checkbox" value="{{$d['value']}}" data-holder="#deparment_help" data-group="deparment" data-rules="least" data-department-name="{{$d['key']}}" name="team[]">
                                {{$d['key']}}
                            	</label>
								<?php } ?>
							<?php } ?>                                
                            </td>
                        </tr>
						<?php }	?>    
					</tbody>
				</table>
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">执行月份：</td>
                            <td>
                               <input class="input input-small" type="text" id="_month1" name="bmonth" value="<?php echo Request::get('bmonth'); ?>" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_month2" name="emonth" value="<?php echo Request::get('emonth'); ?>" readonly="readonly">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">下游合作方：</td>
                            <td>
                               <select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
								@foreach ($underpartnersData as $d)
												<option value="{{$d['key']}}">{{$d['value']}}</option>
								@endforeach				
								 </select>
								 <input name="under_partner_id" type="hidden" class="hidden js-selectbox-multiple-txt"  value="{{Request::get('under_partner_id')}}">
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
                            <td><a href="javascript:;" class="btn btn-blue" id="js-search-btn">查询</a></td>
                        </tr>
                    </tbody>
                </table>
            </form>
         <div id="js-container" class="mt20"></div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

<script>
    seajs.use('view/statistic/list', function (app) {
        app({
            el:"#js-container",
            listAction:ST.ACTION.profitList
        });
    });
	seajs.use('lang/common', function (b) {
        b.com();
		b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
       });
    })

</script>

@endsection