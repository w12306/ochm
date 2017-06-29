var ST = {
    // 调试开关
    debug: false,
    // 是否单页模式
    // 单页：仅存在一个页面视图，切换时销毁上一个页面视图
    // 多页：同时存在多个页面视图，使用显示/隐藏的方式切换
    singlePage: true,
    // 资源路径
    PATH: {
        JS: "/resource/js",
        JSDATA:"/resource/jsData",
        IMAGE: "/resource/images",
        CSS: "/resource/css",
        PUBLIC: "/",
        ROOT: "",
        SUFFIX: '.html'
    },
    // 数据缓存
    CACHE: {},
    // 数据接口
    ACTION: {},
    // 跳转地址
    URL: {},
    // 公共ajax参数
    AJAXDATA:{},
    // 页面输出的数据
    DATA: {},
    // 初始化后执行的方法（ST.todoList()前执行）
    TODOLIST: [],
    // 页面空方法（用于HTML中执行ST方法，调用：ST.todo('方法名',参数1,...,参数N)）
    todo: function () {}
};

//数据接口
if(ST.debug){
    //本地接口：用于本地开发调试
    $.extend(ST.ACTION, {
        "commonSuccess":ST.PATH.PUBLIC+'modules/action/common.success.json',
        "commonError":ST.PATH.PUBLIC+'modules/action/common.error.json'
    });
}else{
    //服务端接口：用于联调和真实环境
    $.extend(ST.ACTION, {});
}

//跳转地址
$.extend(ST.URL, {
    "login":ST.PATH.PUBLIC+'login.html'
});

//seajs配置
seajs.config({
    base: ST.PATH.PUBLIC + 'modules',
    alias: {
        "underscore": "lib/underscore.bee",
        "backbone": "lib/backbone",
        "base": "base/base"
    }
});
