<!-- 边栏菜单 start -->
<div class="aside" id="aside">
    <ul class="nav nav-menu">
        @foreach ($leftMenus as $menu)
            @if ($judge->isAllowed($menu->permission()))
                <li class="menu-on {{ $menu->isActive() ? 'act' : '' }}">
                    @if ($menu->isLink())
                        <a class="dropdown-toggle" href="{{ route($menu->route()) }}" data-cmd="slideNav">{{ $menu->name() }}</a>
                    @else
                        <a class="dropdown-toggle" href="javascript:;" data-cmd="slideNav">{{ $menu->name() }}</a>
                    @endif

                    @if (! $menu->isEmpty())
                        <ul class="dropdown-menu" style="display: block;">
                            @foreach ($menu as $subMenu)
                                @if ($judge->isAllowed($subMenu->permission()))
                                    <li class="{{ $subMenu->isActive() ? 'active' : '' }}">
                                        <a href="{{ route($subMenu->route()) }}" data-id="1">{{ $subMenu->name() }}</a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endif
        @endforeach
    </ul>
</div>
<!-- 边栏菜单 end-->