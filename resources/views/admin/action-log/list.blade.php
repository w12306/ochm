{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {});
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>工具箱>历史操作记录</h2>
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true">
        <table class="table table-listing" id="search-box">
            <tbody>
            <tr>
                <td class="span4">操作时间</td>
                <td>
                    <input class="input input-small" type="text" id="_calender1"
                            name="btime" value="{{ Request::input('btime', '') }}" readonly="readonly">
                    &nbsp;-&nbsp;
                    <input class="input input-small" type="text" id="_calender2"
                            name="etime" value="{{ Request::input('etime', '') }}" readonly="readonly">
                </td>
            </tr>

            <tr>
                <td class="span4">功能模块</td>
                <td>
                    <select name="module" id="" class="js-selectbox js-operate"
                            data-url="{{ route('admin.config.action-log.api.module-widget') }}"
                            data-relate="js-operate-relate"
                            data-relate-value="{{ Request::input('object_data', '') }}"
                            palceholder="请选择功能模块">
                        <option value="">全部</option>
                        @foreach($modules as $key => $moduleInfo)
                            <option value="{{ $key }}" @if (Request::input('module', '') == $key) selected="selected" @endif>{{ $moduleInfo['name'] }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td class="span4">合作方</td>
                <td>
                    {!! Form::select('company_id',
                        $companies->pluck('company_name', 'id')->prepend('全部', ''),
                        Request::input('company_id', ''),
                        [
                            'class' => 'js-selectbox',
                        ]
                    ) !!}
                </td>
            </tr>

            <tr>
                <td class="span4">操作员</td>
                <td>
                    <input type="text" name="admin_user_real_name" class="input input-small"
                            value="{{ Request::input('admin_user_real_name', '') }}">
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
            <th>操作时间</th>
            <th>操作员</th>
            <th>功能模块</th>
            <th>操作数据</th>
            <th>操作描述</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->adminUser->real_name }}</td>
                <td>{{ $modules[$log->module]['name'] }}</td>
                <td>@include('admin.action-log.parts.main-object', ['log' => $log])</td>
                <td width="30%">{{ $log->message }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="clearfix mt10">
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($logs))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        seajs.use('lang/common', function (b) {
            b.com();
        });
    </script>

@endsection