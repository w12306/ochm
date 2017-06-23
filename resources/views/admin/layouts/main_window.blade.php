<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,user-scalable=no"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <!-- 必须加载 start -->
    <link rel="stylesheet" href="/static/css/ST_admin.css">
    <link rel="stylesheet" href="/static/css/vabm.css">
    <link rel="stylesheet" href="/static/css/ST_admin_fix.css"/>
    <script src="/static/js/jquery-min.js"></script>
    <script type="text/javascript">
        var ST = {
            // 资源路径
            PATH    : {
                JS          : "/static/js/", //JS路径
                JSTMP       : "/static/jsTemplate/", //JS模板路径
                IMAGE       : "/static/images/",    //图片路径
                CSS         : "/static/css/",      //CSS路径
                UPLOAD      : "/",           //上传图片目录
                UPLOAD_ADPOP: "",//网址弹窗物料上传目录 桌面图标  ie收藏用此目录
                ROOT        : "/",            //网站根目录
                VCODE       : "",            //获取验证码路径
                EDITORCSS   : ''          //百度编辑器CSS样式路径
            },
            // 数据缓存
            CACHE   : {},
            //百度编辑器对象
            EDITORS : [],
            // 使用的JS模板
            JSTMP   : {},
            // 服务端使用的URL
            ACTION  : {
                //会被“API地址配置”覆盖
            },
            // 服务端输出的数据
            PHPDATA : {
                mtype: {
                    "1": "图片",
                    "2": "图片组",
                    "3": "文字链",
                    "4": "dll",
                    "5": "flash",
                    "6": "代码",
                    "7": "游戏图片"
                }
            },
            // 初始化后执行的方法（ST.todoList()前执行）
            TODOLIST: [],
            // 页面空方法（用于HTML中执行ST方法，调用：ST.todo('方法名',参数1,...,参数N)）
            todo    : function () {
            }
        };
    </script>
    <!-- 必须加载 end -->

    {{-- API地址配置 --}}
    @include('admin.parts.api')

    {{-- 头部插入区块 --}}
    @yield('head')

</head>

{{-- 插入服务 --}}
@inject('judge', 'App\Services\Admin\PermissionJudge')

<body style="background:none;">
            <!-- 主要内容区块 start -->
    <div class="container" style="min-width: 961px;">

        <div class="content p20" style="margin-left:0px;min-width: 936px;">
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

    {{-- JS模板 --}}
    @include('admin.parts.js-template')
    {{-- 通用JS --}}
    @include('admin.parts.common-js')

</body>
</html>