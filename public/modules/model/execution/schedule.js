define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.ACTION.scheduleDetail,
        type:"post",
        initialize: function() {
        },
        setPars:function(par){
            this.pars= par;
        },
        convertTableData:function(par){
            var t=this,
                obj = t.get("data"),
                data,htable,row;

            if(par){
                data = par;
            }else{
                data= obj?obj.table:null;
            }


            if(data){
                var cloneData =  JSON.parse(JSON.stringify(data));
                for(var i=0;i<cloneData.length;i++){
                    /*一个htable数据*/
                    for(var j=0;j<cloneData[i].length;j++){
                        /*一行row数据*/
                        for(var k=0;k<cloneData[i][j].length;k++){
                            cloneData[i][j][k] = t.changeTableValueToHtml(cloneData[i][j][k]);
                            if(j==0&&k>1){
                               cloneData[i][j][k] = $(cloneData[i][j][k]).addClass("js-datepicker-month")[0].outerHTML
                            }
                        }
                    }
                }
            }
            return cloneData;
        },
        changeTableValueToHtml:function(obj,cls){
            var style="",
                val="",
                res="",
                res1="<p class='schedule-part1'></p>",
                res2="<p class='schedule-part2'></p>",
                id="",
                obj1,
                obj2,
                className = cls || "";

            if(!obj){
                return res;
            }

            /*排期中有1/2情况时，obj格式为 “half:[{}]”，里面包含两个对象*/
            if("half" in obj){
                obj1 = obj["half"][0];
                obj2 = obj["half"][1];
                if(obj1){
                    res1 = this.changeTableValueToHtml(obj1,"schedule-part1");
                }
                if(obj2){
                    res2 = this.changeTableValueToHtml(obj2,"schedule-part2");
                }

                res = res1+res2;
            }else{
                for(var i in obj){
                    switch (i){
                        case "weight":
                            if(obj[i]){
                                style = style + ";font-weight:" + obj[i];
                            }
                            break;
                        case "color":
                            if(obj[i]){
                                style = style + ";color:" + obj[i];
                            }
                            break;
                        case "background":
                            if(obj[i]){
                                style = style + ";background-color:" + obj[i];
                            }
                            break;
                        case "align":
                            if(obj[i]){
                                style = style + ";text-align:" + obj[i];
                            }
                            break;
                        case "id":
                            id=obj[i];
                            break;
                        case "value":
                            val=obj[i]||"";
                            break;
                    }
                }
                if(id){
                    res = "<p class='js-ad "+className+"' style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
                }else{
                    res = "<p class='" + className + "' style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
                }
            }

            return res;
        }
    });
});
