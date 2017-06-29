define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            pars:{}    //动态参数  需要跟随路由变化以及响应页面变化的
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        initialize: function() {
            var t=this;
            t.on("change:pars",function(m,v){
                $.log("【demo/index】视图参数变更:"+JSON.stringify(v));
            })
        },
        parse:function(res){
            return res;
        }
    });

});