define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
        },
        pars:{}, //静态参数方法 通常是手动添加的固定参数
        url:ST.ACTION.executionDetail,
        type:"post",
        initialize: function() {
        },
        parse:function(res){
            var res,t=this,adObj={},sellObj={};
            /*新建*/
            if(res.data&& !res.data.id){
                res.data.table = [];
                res.data.table.push(t.getDefaultTable());
            }

            if(res.data&&res.data.advertis){
                for(var i=0;i<res.data.advertis.length;i++){
                    adObj[res.data.advertis[i].key]=res.data.advertis[i].value;
                }
            }
            res.data.advertis=adObj;

            if(res.data&&res.data.sell_type){
                for(var i=0;i<res.data.sell_type.length;i++){
                    sellObj[res.data.sell_type[i].key]=res.data.sell_type[i].value;
                }
            }

            res.data.sell_type = sellObj;

            return res;
        },
        convertTableData:function(par){
            var t=this,
                obj = t.get("data"),
                data,htable,row,disArr=[],disabled,res;

            if(par){
                data = par;
            }else{
                data= obj?obj.table:null;
            }

            if(data){
                for(var i=0;i<data.length;i++){

                    disabled=[]; //存储当前table的 diabled 的值

                    /*一个htable数据*/
                    for(var j=0;j<data[i].length;j++){
                        /*一行row数据*/
                        for(var k=0;k<data[i][j].length;k++){

                            //查找type=disabled 表示为不可编辑
                            if(data[i][j][k].type=="disabled"){
                                disabled.push({
                                    row:j,
                                    col:k,
                                    readOnly:true
                                })
                            }

                            if(typeof data[i][j][k]=="object"){
                                data[i][j][k] = t.changeTableValueToHtml(data[i][j][k]);
                            }
                            
                            if(j==0&&k>1){
                               data[i][j][k] = $(data[i][j][k]).addClass("js-datepicker-month")[0].outerHTML
                            }
                        }
                    }

                    disArr[i] = disabled;
                }
            }
            res = {
                table:data,
                disabled:disArr
            }
            return res;
        },
        changeTableValueToHtml:function(obj){
            var style="",val="",res="",id="",cls="";
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
                        case "type":
                            if(obj[i]=="disabled"){
                                cls ="disabled";
                                val ="";
                                style="";
                                id="";
                            }
                            
                            break;
                    }
                }
            }
            
            res = "<p class='"+cls+"' style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
            return res;
        },
        changeTableVauleToObj:function(html){
            var obj = {},val="",style="",$el = $(html);

            //$el.data("type")=="disabled" 售罄框，其值至空，不回传给后端
            if($el.length>0 && $el.data("type")!="disabled"){
                obj.value = $el.text();
                style = $el.attr("style");
                obj.weight = $el.css("font-weight");
                obj.color = $el.css("color");
                obj.background = $el.css("background-color");
                obj.align = $el.css("text-align");
                obj.id = $el.data("id");
            }else {
                if($el.data("type")!="disabled"){
                    obj.value = html;
                }else{
                    obj.value = "";
                }
                obj.weight = "";
                obj.color = "";
                obj.background = "";
                obj.align = "";
                obj.id = "";
            }
            
            return obj;
        },
        setTableData:function(i,row,col,val){
            var t=this,obj = t.get("data"),data;
            if(obj&&obj.table&&val){
               obj["table"][i][row][col] = t.changeTableVauleToObj(val);
               //t.set("data",obj);
            }
        },
        getDefaultTable:function(mydate){
            var date = new Date(),
                str_ym,
                table = [],
                row;
            str_ym = date.getFullYear() + "-" + (date.getMonth()+1<10? "0"+ (date.getMonth()+1) : date.getMonth()+1);
            
            if(mydate){
                str_ym = mydate;
            }
            
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