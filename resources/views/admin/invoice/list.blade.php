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
            invoiceList:"/authed/invoice/api/list-seach",  //获取发票列表接口, 
            dataDelete:"{{ route('admin.invoice.delete') }}" //删除发票接口
        });
    </script>
    <div class="heading clearfix">
         <div class="pull-left">
                    <h2>发票管理>发票列表</h2>
         </div>
         <div class="pull-right">
            <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.invoice.export-excel') }}"
                    id="export-btn">当前数据导出</a>
        </div>
    </div>

	  <form action="" method="" onSubmit="return false"  id="search-box">
                <table class="table table-listing" id="search-box">
                    <tbody>
                        <tr>
                            <td class="span4">开票时间：</td>
                            <td>
                                <input class="input input-small" type="text" id="_calender1" name="btime" value="" readonly="readonly">&nbsp;-&nbsp;
                                <input class="input input-small" type="text" id="_calender2" name="etime" value="" readonly="readonly">
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
                            <td class="span4">合作方：</td>
                            <td>
                                <select name="partner_id" class="js-selectbox" placeholder="支持多选" multiple="multiple">
                                    @foreach ($partnerData as $d)
                                    <option value="{{$d->id}}" <?php if($d->id==Request::get('partner_id')){echo 'selected="selected"';}?> >{{$d->company_name}}</option>
							   		@endforeach	
                                </select>
                                <input name="" type="hidden" class="hidden js-selectbox-multiple-txt"  value="">
                            </td>
                        </tr>

                        <tr>
                            <td class="span4">发票类型：</td>
                            <td>
							<input type="text" name="invoice_type" class="input input-small" value="" >
                         
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
    seajs.use('view/invoice/invoice', function (app) {
        app({
            el:"#js-container"
        });
    })
	
		seajs.use('lang/common', function (b) {
            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
</script>
@endsection