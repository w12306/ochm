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
            @if (isset($role))
                <h2>编辑角色<span style="font-size: 12px;font-weight: normal; color:#FF0000;">(添加模块里非查看权限时，注意同时加上查看权限！)</span>
                </h2>
            @else
                <h2>新建角色<span style="font-size: 12px;font-weight: normal; color:#FF0000;">(添加模块里非查看权限时，注意同时加上查看权限！)</span>
                </h2>
            @endif
        </div>
    </div>

    @if (isset($role))
        {!! Form::model($role, [
            'url'        => route('admin.config.permission.update-role', ['id' => $role->id]),
            'id'         => 'js-form-validate',
            'stverify'   => 'true',
            'erroappend' => 'true',
            'ajaxpost'   => 'true',
        ]) !!}
    @else
        {!! Form::open([
            'url'        => route('admin.config.permission.store-role'),
            'id'         => 'js-form-validate',
            'stverify'   => 'true',
            'erroappend' => 'true',
            'ajaxpost'   => 'true',
        ]) !!}
    @endif

    <table class="table table-listing">
        <tbody>
        <tr>
            <td class="span4"><em class="text-red">* </em>角色名称</td>
            <td>
                {!! Form::text('name', null, [
                    'class'       => 'input input-small',
                    'opt'         => 'rq',
                    'placeholder' => '请输入角色名称',
                    'maxlength'   => '30',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4"><em class="text-red">* </em>角色代码</td>
            <td>
                {!! Form::text('slug', null, [
                    'class'       => 'input input-small',
                    'opt'         => 'rq',
                    'placeholder' => '英文或英文句号',
                    'maxlength'   => '20',
                ]) !!}
            </td>
        </tr>

        <tr>
            <td class="span4">角色权限</td>
            <td>
                @foreach ($permissionGroups as $permissionGroup)
                    <table class="table table-listing table-hovered mb5 js-toggle-box">
                        <tbody>
                        <tr>
                            <td class="role-title">
                                <label class="checkbox" title="{{ $permissionGroup['name'] }}">
                                    <input class="js-toggleall" type="checkbox"> {{ $permissionGroup['name'] }}
                                </label>
                            </td>
                            <td>
                                <ul class="inline-group js-toggle-con">
                                    @foreach ($permissionGroup['permissions'] as $permission)
                                        <li title="{{ $permission['name'] }}">
                                            <label class="checkbox">
                                                @if (isset($role))
                                                    <input type="checkbox" value="{{ $permission['slug'] }}" name="permissions[]" @if ($role->permissions->has($permission['slug'])) checked="checked" @endif> {{ $permission['name'] }}
                                                @else
                                                    <input type="checkbox" value="{{ $permission['slug'] }}" name="permissions[]"> {{ $permission['name'] }}
                                                @endif
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                @endforeach
            </td>
        </tr>

        <tr>
            <td class="span4"></td>
            <td>
                <input class="btn btn-blue" type="submit" value="提交">
                <a class="btn" href="{{ route('admin.config.permission.role-list') }}">取消</a>
            </td>
        </tr>
        </tbody>
    </table>

    {!! Form::close() !!}

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        seajs.use('lang/common', function (app) {
            app.com();
            app.role();
        });
    </script>

@endsection