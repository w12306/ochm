{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {
            contractFiling     : "{{ route('admin.contract.api.archive') }}", //合同归档接口
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>合同管理>合同列表</h2>
        </div>
        <div class="pull-right">
            <a title="新增合同" href="{{ route('admin.contract.add') }}" class="btn mr5">新增合同</a>
            <a title="当前数据导出" href="javascript:;" class="btn mr5"
                    data-url="{{ route('admin.contract.export-excel') }}"
                    id="export-btn">当前数据导出</a>
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true" id="search-box">
        <table class="table table-listing">
            <tbody>
            <tr>
                <td class="span4">合同类型：</td>
                <td>
                    @foreach ($contractTypeList as $key => $contractType)
                        <label>
                            <input type="checkbox" name="contract_types[]" value="{{ $key }}" @if (in_array($key, $queryContractTypes)) checked @endif>
                            <span class="pr10">{{ $contractType }}</span>
                        </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td class="span4">签约时间：</td>
                <td>
                    <input class="input input-small" type="text" id="_calender1" name="btime" value="{{ Request::get('btime') }}" readonly="readonly">&nbsp;-&nbsp;
                    <input class="input input-small" type="text" id="_calender2" name="etime" value="{{ Request::get('etime') }}" readonly="readonly">
                </td>
            </tr>

            <tr>
                <td class="span4">业务编号：</td>
                <td>
                    <input type="text" name="business_key" class="input input-small" value="{{ Request::get('business_key') }}">
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
            <th>合同编号</th>
            <th>相关业务</th>
            <th>签约时间</th>
            <th>合同金额</th>
            <th>合同补充内容</th>
            <th>合同类型</th>
			<th>合同状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @if ($contracts->count() == 0)
            <tr>
                <td colspan="7">没有内容</td>
            </tr>
        @else
            @foreach ($contracts as $contract)
                <tr>
                    <td><a href="{{route("admin.contract.detail", $contract->id )}}">{{ $contract->ckey }}</a></td>
                    <td>
                        @foreach ($contract->businesses as $business)
                            <a href="{{ route('admin.business.business-detail' , ['id' =>$business->id]) }}" class="block">{{ $business->business_key }}</a>
                        @endforeach
                    </td>
                    <td>{{ $contract->signtime }}</td>
                    <td>{{ $contract->amount }}</td>
                    <td>{{ str_limit($contract->remark, 60) }}</td>
                    <td>{{ $contract->type_text }}</td>
					<td>{{ $contract->status_text }}</td>
                    <td>
                        <a href="{{ route('admin.contract.edit', ['id' => $contract->id]) }}" ><i hidid="" class="icon i-edit" title="编辑合同"></i></a>
                        @if ($contract->status == \App\Models\Contract::STATUS_SENDED)
                            <a href="javascript:;" class="js-contract-filing" data-id="{{ $contract->id }}"><i hidid="" class="icon i-pigeonhole" title="合同归档"></i></a>
                        @endif
                       <!-- <a href="{{ route('admin.contract.download', ['id' => $contract->id]) }}" target="_blank"><i hidid="" class="icon i-download" title="下载电子档合同"></i></a>-->
                    </td>
                </tr>
            @endforeach
        @endif
        </tbody>
    </table>

    <div class="clearfix mt10">
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($contracts))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')
    <script>
        seajs.use('lang/common', function (b) {
            b.com();
            b.contract();

            b.exportExcel({
                id          : "export-btn",
                searchFormId: "search-box"
            });
        });
    </script>
@endsection