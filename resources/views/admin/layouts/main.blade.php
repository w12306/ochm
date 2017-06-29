<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
	
	<link rel="stylesheet" href="/resource/css/style.css"/>
    <link rel="stylesheet" href="/resource/css/style.fix.css"/>
    <link rel="stylesheet" href="/resource/css/ST_admin.css">
    <link rel="stylesheet" href="/resource/css/beeui.css">
	<link rel="stylesheet" href="/resource/css/vabm.css">
    <script src="/modules/lib/sea.js"></script>
    <script src="/resource/js/jquery.js"></script>
    <script src="/resource/js/ST.Config.js"></script> 
	


    {{-- API地址配置 --}}
    @include('admin.parts.api')

    {{-- 头部插入区块 --}}
    @yield('head')

</head>

{{-- 插入服务 --}}
@inject('judge', 'App\Services\Admin\PermissionJudge')

<body>
    {{-- 顶部导航 --}}
    @include('admin.parts.topbar')

            <!-- 主要内容区块 start -->
    <div class="container">
        {{-- 左侧菜单 --}}
        @include('admin.parts.left-sidebar')

        <div class="content p20">
            <!-- 如果有错误（比如FormRequest产生的错误） start -->
            @if (count($errors) > 0)
                @foreach ($errors->all() as $error)
                    <div class="alert alert-error">
                        <i class="icon"></i>

                        <div class="alert-inner">{{ $error }}</div>
                    </div>
                @endforeach
            @endif
            <!-- 如果有错误 end -->

            {{-- 内容区块 --}}
            @yield('content')
        </div>
    </div>
    <!-- 主要内容区块 end -->

    <!-- 行内通用JS start -->
    <script>

    </script>
    <!-- 行内通用JS end -->

    {{-- 尾部代码，通常放script --}}
    @yield('end')

 

</body>
</html>