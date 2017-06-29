{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>操作人员管理</h2>
        </div>

        <div class="pull-right">
            <a title="新建权限角色" href="{{ route('admin.config.permission.create-role') }}" class="btn mr5"><i class="icon i-add"></i> 新建权限角色</a>
        </div>
    </div>

    <table class="table table-listing border-table">
        <thead class="thead-gray">
        <tr>
            <th>角色名称</th>
            <th>角色代码</th>
            <th>操作权限</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($roles as $role)
            <tr>
                <td>
                    {{ $role->name }}
                </td>
                <td>
                    {{ $role->slug }}
                </td>
                <td>
                    {{ str_limit($role->permissions->pluck('name')->toBase()->implode(','), 120) }}
                </td>
                <td>
                    <a href="{{ route('admin.config.permission.edit-role', ['id' => $role->id]) }}" title="修改">
                        <i class="icon i-edit"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="clearfix mt10">
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($roles))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        $.extend(ST.ACTION, {});
        $.extend(ST, {
            //页面初始化方法写在此处
            pageInit: function () {

            }
        });
    </script>

@endsection