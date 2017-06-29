define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            pars:{}//动态参数  需要跟随路由变化以及响应页面变化的
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.debug?"/resource/jsData/newUser.js":ST.ACTION.newUser,
        initialize: function(pars) {
            var t=this;
            if(pars&&pars.id){
                if(!ST.debug){
                    t.url=t.url+"/"+pars.id;
                }
            }
            t.on("error",function(m,res,o){
                $.log(res);
            });
            t.fetch();
        },
        parse:function(res){
            return res;
        }
    });
});