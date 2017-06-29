{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>保证金列表 - {{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {
		marginList:"{{ route('admin.earnestcash.api.list') }}",  //获取保证金列表接口, 
		dataDelete:"{{ route('admin.earnestcash.delete') }}", //删除保证金
		
		marginRefundList:"{{route('admin.earnestcash.api.refund-list')}}", //保证金抵款列表
        marginRefund:"{{ route('admin.earnestcash.store-refund') }}", //新增/编辑保证金抵款
        marginRefundDelete:"{{ route('admin.earnestcash.delete-refund')}}", //删除保证金抵款
		
		
        
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>保证金管理>保证金列表</h2>
        </div>
        <div class="pull-right">
            <a title="新增保证金" href="{{ route('admin.earnestcash.add') }}" class="btn mr5">新增保证金</a>
            <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.earnestcash.export-excel') }}"
                    id="export-btn">当前数据导出</a>
        </div>
    </div>

    <form action="" method="" onSubmit="return false" id="search-box">
        <table class="table table-listing">
            <tbody>
            <tr>
                <td class="span4">合作方</td>
                <td>
                    <select name="" id="" class="js-selectbox" placeholder="支持多选" multiple="multiple">
                        @foreach ($partners as $partner)
                            <option value="{{ $partner['key'] }}">{{ $partner['value'] }}</option>
                        @endforeach
                    </select>
                    <input name="partner_id_csv" type="hidden" class="hidden js-selectbox-multiple-txt" value="">
                </td>
            </tr>

            <tr>
                <td class="span4">收款时间</td>
                <td>
                    <input class="input input-small" type="text" id="_calender1" name="btime" value="" readonly="readonly">&nbsp;-&nbsp;
                    <input class="input input-small" type="text" id="_calender2" name="etime" value="" readonly="readonly">
                </td>
            </tr>
            <tr>
                <td class="span4">票据编号</td>
                <td>
                    <input type="text" name="bill_num" class="input input-small" value="">
                </td>
            </tr>

            <tr class="tr_bg">
                <th></th>
                <td>
                    <a href="javascript:;" class="btn btn-blue" id="js-search-btn">查询</a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <div id="js-container" class="mt20"></div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('view/margin/list', function (app) {
            app({
                el: "#js-container"
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