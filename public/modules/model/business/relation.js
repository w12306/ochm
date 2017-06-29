define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            pars:{}    //动态参数  需要跟随路由变化以及响应页面变化的
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:"/resource/jsData/table1.json",
        initialize: function(pars) {
            var t=this;
            if(pars&&pars.url){
                t.url = pars.url;
            }

            t.on("error",function(m,res,o){
                $.log(res);
            });
            t.fetch();
        },
        /*转换数据格式*/
        convertData:function(){
            var data = this.toJSON(),
                obj={},length,tbdata,tbtitle;
            
            tbdata = data.data.table;
            tbtitle = data.data.title;
            length = tbdata.length;

            if(data&&tbdata.length>0){
                for(var tmp in tbdata[0]){
                    obj[tmp]=[];
                }
            }   
            for(var i=0;i<tbdata.length;i++){
                var o = tbdata[i];
                for(var tmp in obj){
                    if(tmp in o){
                        o[tmp].num = 1;
                        obj[tmp].push(o[tmp]);
                    }
                }
            }
            var k=0;
            for(var tmp in obj){
                var o=obj[tmp];
                for(var i=1; i<o.length;i++){
                    if(o[i].key==o[i-1].key){
                        o[i-1].num = o[i-1].num + o[i].num;
                        o.splice(i,1);
                        i=i-1;
                    }
                }
            }

            return {data:obj,num:length,title:tbtitle,h:30};
        },
        parse:function(res){
            return res;
        }
    });
});