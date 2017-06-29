<!-- 顶部 start -->
<div class="header">
    <div class="header-inner clearfix">
        <h1 class="logo pull-left"><a href="javascript:;">LOGO</a></h1>
        <ul class="nav nav-inline pull-right">
            <li>你好,{{ $admin->real_name }}</li>
			<li><a href="{{ route('admin.password') }}?id={{ $admin->id }}">修改密码</a></li>
            <li><a href="{{ route('admin.logout') }}">退出</a></li>
        </ul>
    </div>

    <div class="tab">
        <ul class="nav nav-tabs nav-blue tab-trigger">
            @foreach ($menus as $menu)
                @if ($judge->isAllowed($menu->permission()))
                    <li class="{{ $menu->isActive() ? 'active' : '' }}">
                    <a href="{{ route($menu->route()) }}">{{ $menu->name() }}</a>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
</div>
<!-- 顶部 end -->