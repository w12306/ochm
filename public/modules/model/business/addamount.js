define(function (require) {
    var _ = require('underscore');
    var Base = require('base');

    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            pars:{}    //动态参数  需要跟随路由变化以及响应页面变化的
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.debug?"/resource/jsData/amountlist.js":ST.ACTION.amountList,
        initialize: function(pars) {
            var t=this;
            if(pars&&pars.id){
                if(!ST.debug){
                    t.url=t.url+"/"+pars.id;
                }
            }
            t.on("deleteAmount",t.deleteAmount)
            t.on("error",function(m,res,o){
                $.log(res);
            });
            t.fetch();
        },
        deleteAmount:function(id,cb){
            $.ajax({
                method:"post",
                url:ST.ACTION.amountDelete+"/"+id,
                dataType:"json",
                success:function(d){
                    if(d.info){
                        $.notice({content: d.info});
                    }
                    if(d.status=="success"){
                        cb();
                    }
                },
                error:function(e){
                    $.notice({state:"error",content: "操作失败"});
                }
            })
        },
        parse:function(res){
            return res;
        }
    });
});