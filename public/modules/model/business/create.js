define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            pars:{}    //动态参数  需要跟随路由变化以及响应页面变化的
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.debug?"/resource/jsData/business1.json":ST.ACTION.businessDetail,
        initialize: function() {

            var t=this;
            var hash = t.get("hash"),con={};
            // #a:1/b:2
            if(hash && hash.indexOf(':') > -1) {
                hash.replace(/(\w+)\s*:\s*([^ \f\n\r\t\v:\/]+)/g, function(a, b, c) {
                    con[b]=c;
                });
            }
            t.set("con",con);
            t.pars =  $.extend(con, t.pars);

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