define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
           pars:{}    //动态参数  需要跟随路由变化以及响应页面变化的
        },
        //静态参数方法 通常是手动添加的固定参数
        pars:{b:2}, 
        //配置接收router的参数列表, 不再此参数列表的参数不会使用 下例表示只会接收a和c参数
        parsList:{
            "a":"",
            "c":""
        },
        url:"getnav.php", //数据变更url
        initialize: function() {
            var t=this;
            t.on("change:pars",function(m,v){
                $.log("【demo/index】子视图child参数变更:"+JSON.stringify(v));
            })
        },
        parse:function(res){
            return res;
        }
    });

});