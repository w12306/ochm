define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.ACTION.executionDetail,
        type:"get",
        initialize: function() {
        },
        parse:function(res){
            var res,t=this;
            /*新建*/
            if(res.data&& !res.data.id){
                res.data.table = [];
                res.data.table.push(t.getDefaultTable());
            }
            return res;
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
                for(var i=0;i<data.length;i++){
                    /*一个htable数据*/
                    for(var j=0;j<data[i].length;j++){
                        /*一行row数据*/
                        for(var k=0;k<data[i][j].length;k++){
                            data[i][j][k] = t.changeTableValueToHtml(data[i][j][k]);
                            if(j==0&&k>1){
                               data[i][j][k] = $(data[i][j][k]).addClass("js-datepicker-month")[0].outerHTML
                            }
                            
                        }
                    }
                }
            }
           // console.log("convertTableData",data)
            return data;
        },
        changeTableValueToHtml:function(obj){
            var style="",val="",res="",id="";
            if(obj){
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
            }
            
            res = "<p style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
            return res;
        },
        getDefaultTable:function(){
            var date = new Date(),
                str_ym,
                table = [],
                row;
            str_ym = date.getFullYear() + "-" + (date.getMonth()+1<10? "0"+ (date.getMonth()+1) : date.getMonth()+1);
            
            
            for(var i=0;i<14;i++){
                row=[];
                for(var j=0;j<33;j++){
                    row.push({"weight":"","color":"","background":"","align":"","value":""});
                    if(i<2&&j==0){
                        row[j].value="广告位"
                    }
                    if(i<2&&j==1){
                        row[j].value="售卖方式"
                    }
                    if(i==0&&j>1){
                        row[j].value=str_ym;
                    }
                    if(i==1&&j>1){
                        row[j].value=j-1;
                    }
                }
                table.push(row);
            }

            return table;
        }
    });
});