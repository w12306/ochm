{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <script>
        $.extend(ST.ACTION, {
            userStatusChange: "{{ route('admin.config.permission.api.update-admin-status') }}", //用户管理启用，停用接口
            userDetail      : "{{ route('admin.config.permission.api.admin-user-info') }}", //编辑用户时加载详细信息
            addUser         : "{{ route('admin.config.permission.api.save-admin-user') }}" //添加、编辑用户接口
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>权限管理>用户管理</h2>
        </div>

        <div class="pull-right">
            <a title="创建新用户" href="{{ route('admin.config.permission.create-admin-user')}}" class="btn mr5 js-user-add">创建新用户</a>
        </div>
    </div>

    <form action="{{ Request::getRequestUri() }}" method="get" stverify="true">
        <table class="table table-listing" id="search-box">
            <tbody>
            <tr>
                <td class="span4">角色：</td>
                <td>
                    <select name="role" class="js-selectbox" placeholder="请选择">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @if (isset($queryRole) && $queryRole == $role->id) selected="selected" @endif>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </td>
            </tr>

            <tr>
                <td class="span4">执行小组：</td>
                <td>
                    @foreach ($teams as $team)
                        <label>
                            <input type="checkbox" name="teams[]" value="{{ $team->value }}" @if (isset($queryTeams) && in_array($team->value, $queryTeams)) checked="checked" @endif>
                            <span class="pr10">{{ $team->value }}</span>
                        </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td class="span4">真实姓名：</td>
                <td>
                    <input type="text" name="real_name" class="input input-small"
                            value="{{ Request::get('real_name') }}">
                </td>
            </tr>

            <tr>
                <td class="span4">用户名：</td>
                <td>
                    <input type="text" name="username" class="input input-small"
                            value="{{ Request::get('username') }}">
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
            <th>用户ID</th>
            <th>真实姓名</th>
            <th>用户名</th>
            <th>角色</th>
            <th>执行小组</th>
            <th>录入时间</th>
			<th>状态</th>
            <th>操作</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($adminUsersEnd as $adminUser)
            <tr>
                <td>
                    {{ $adminUser['id'] }}
                </td>
                <td>
                    {{ $adminUser['real_name'] }}
                </td>
                <td>
                    {{ $adminUser['username'] }}
                </td>
                <td>
                    {{ $adminUser['rolename'] }}
                </td>
                <td title="{{ $adminUser['team'] }}">
                    {{ $adminUser['team_text'] }}
                </td>
                <td>
                    {{ $adminUser['created_at'] }}
                </td>
				<td>
                    {{ $adminUser['status_text'] }}
                </td>
                <td>
					<?php if(($adminUser['is_system'] !=1 && $adminUser['username']!="admin") || (session('isSystem')==1)){ ?>
						<a href="{{ route('admin.config.permission.edit-admin-user',$adminUser['id'])}}" class="js-user-edit" data-id="{{ $adminUser['id'] }}">修改</a>
					<?php }?>	
					
					<?php if($adminUser['is_system'] !=1){ ?>	
						<?php if($adminUser['status'] ==1){ ?>
							<a href="javascript:" class="js-user-status" data-id="{{ $adminUser['id'] }}" data-status="0">停用</a>
						<?php }else{ ?>
							<a href="javascript:" class="js-user-status" data-id="{{ $adminUser['id'] }}" data-status="1">启用</a>
						<?php }?>
					<?php }?>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="clearfix mt10">
        {!! with(new \App\Presenters\SemanticUi\PaginatorPresenter($adminUsers))->render() !!}
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
		seajs.use('view/tool/newUser', function (app) {
        app({
            el: '#js-container'
        });
    });
	
	 seajs.use('lang/common', function (b) {
            b.com();
            b.role();
        });
    </script>

@endsection