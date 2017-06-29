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
			bExpnedList:"{{ route('admin.expenses.api.list-expenses-data') }}",  //获取业务支出列表接口, 
            bExpnedDelete:"{{ route('admin.expenses.delete') }}" //删除业务支出接口
        });
    </script>
	<div class="heading clearfix">
        <div class="pull-left">
            <h2>支出管理>支出列表</h2>
        </div>
        <div class="pull-right">
            <a title="当前数据导出" href="javascript:;" class="btn mr5" data-url="{{ route('admin.expenses.export-excel') }}" id="export-btn" >当前数据导出</a>
        </div>
    </div>
			<ul class="nav nav-tabs mt10 mb10">
                <li class="active"><a href="#">业务支出</a></li>    
                <li><a href="{{ route('admin.stand-expenses.list-expenses') }}">独立支出</a></li>     
            </ul>
	  <form action="" method="" onSubmit="return false"  id="search-box">
                <table class="table table-listing">
                    <tbody>
                        <tr>
                            <td class="span4">执行月份：</td>
                            <td>
                                <input class="input input-small" type="text" id="_month1" name="bmonth" value="" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_month2" name="emonth" value="" readonly="readonly">
                            </td>
                        </tr>
                        <tr>
                            <td class="span4">下游合作方：</td>
                            <td>
							<select name="" id="" class="js-selectbox" placeholder="请选择-支持多选" multiple="multiple">
							@foreach ($under_partnerData as $d)
											<option value="{{$d['id']}}">{{$d['company_name']}}</option>
							@endforeach				
							 </select>
							 <input name="under_partner" type="hidden" class="hidden js-selectbox-multiple-txt"  value="">

                            </td>
                        </tr>

                        <tr>
                            <td class="span4">支出类型：</td>
                            <td>
							@foreach ($expenses_type as $key=>$d)
								<label class="ml10">
                                <input type="checkbox" name="expend_type[]" value="{{$key}}"  <?php if(!empty(Request::get('expend_type')) && in_array($key,Request::get('expend_type'))){echo 'checked="checked"';}?>>{{$d}}
                                </label>
								
							@endforeach	
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
    seajs.use('view/expend/list', function (app) {
        app({
            el:"#js-container",
            listAction:ST.ACTION.bExpnedList,
            deleteAction:ST.ACTION.bExpnedDelete
        });
    });
	seajs.use('lang/common', function (b) {
            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
</script>
@endsection