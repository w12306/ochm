define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/execution/add.html");
    var M = require("model/execution/add");
    var com = require("lang/common");
    var apdView = require("view/tool/addproduct");
    require("lang/ST.cityList");

    var View = Base.View.extend({
        //template: tpl,
        initialize: function (pars) {
            var t = this;
            
            t.model = new M();
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
            t.model.fetch();
        },
        curStyle:{'font-weight': '','color':'#FFFFFF'},
        render:function(){
            var t=this,
                data = t.model.get("data");

            t.$el.html(_.template(tpl,{data:data}));
            t.tableRender();
        },
        tableRender:function(){
            var t=this,
                obj = t.model.convertTableData(),
                data = obj.table,
                disabled = obj.disabled;

            for(var table_i=0;table_i<data.length;table_i++){
                t.$el.find("#js-container-table").append($("<div class='js-table'></div>"));
                t.createTable(t.$el.find(".js-table").eq(table_i)[0],data[table_i],t.model.get("data").advertis,t.model.get("data").sell_type,disabled[table_i]);
            }
        },
        setDataAtCell:function(index,row,i,subHtml){
            var t=this,
                data = t.model.toJSON();
            data.data.table[index][row][i]=subHtml;
        },
        createTable:function($el,data,s1,s2,disabled){
            /*创建类excel表格*/
            var t=this,dis=[
                    {row: 0, col: 0, readOnly: true},
                    {row: 0, col: 1, readOnly: true},
                    {row: 0, col: 2, readOnly: true}
                ];
            if(disabled){
                dis = dis.concat(disabled)
            }
           

            var ht = new Handsontable($el,{
                data: data,
                rowHeaders: true,
                colHeaders: true,
                fixedRowsTop: 2,
                height:(data.length+1)*40+10,
                contextMenu: true,
                mergeCells: [
                    {row: 0, col: 0, rowspan: 2, colspan: 1},
                    {row: 0, col: 1, rowspan: 2, colspan: 1},
                    {row: 0, col: 2, rowspan: 1, colspan: 31}
                ],
                colWidths:[100,100,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30],
                columns: [
                    {
                        editor: 'select',
                        selectOptions: s1,
                        multiple: true
                    },{
                        editor: 'select',
                        selectOptions: s2,
                        multiple: true
                    },
                    {},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{},{}
                ],
                contextMenu: {
                    items: {
                        "row_above": {
                            name: '向前插入一行'
                        },
                        "row_below": {
                            name: '向后插入一行'
                        },
                        "remove_row": {
                            name: '移除当前行'
                        }
                    }
                },
                renderer:"html",
                afterChange:function(obj,type){
                    t.hTableChage(this,obj,type)
                }
            });
            ht.updateSettings({
                cell:dis
            })
        },
        hTableChage:function(ht_this,obj,type){
            var t=this,index = $(".js-table").index($(ht_this.rootElement));

            if(type=="paste"){
                //粘贴过来的数据去空格
                for(var i=0;i<obj.length;i++){
                    obj[i][3] = $.trim(obj[i][3])
                    t.setDataAtCell(index,obj[i][0],obj[i][1],obj[i][3]);
                }
                return;
            }

            if(!(obj&&type=="edit"))  return;
            
            var col=obj[0][1],
                row=obj[0][0],
                td = ht_this.getCell(row,col),
                val = $.trim($(td).text()),
                html,
                table_i=$(td).parents(".js-table").index();
            if(!val){
                return false;
            }
            if(ht_this.getCellEditor(row, col)=="select"){
                //选中售卖方式  改变对应行的背景色
                if(col==1){
                    var cols_num = ht_this.countCols(),subTd,subStyle,subColor=t.getColor(val),subHtml,subVal,tmpcurStyle;

                    if(subColor){
                        tmpcurStyle=t.curStyle;
                        t.curStyle=null;
                        for(var i=2;i<cols_num;i++){
                            subTd = ht_this.getCell(row,i);
                            subVal = $.trim($(subTd).text());

                            $(subTd).find("p").css("background-color","");
                            subStyle = $(subTd).find("p").attr("style")||"";
                            subStyle = subStyle+";background-color:"+subColor;

                            if(subVal){
                                subHtml = "<p style='"+subStyle+"'>"+subVal+"</p>";
                                $(subTd).html(subHtml);
                                $(subTd).attr("data-style",subStyle);
                                if(ht_this.getDataAtCell(row, i)!=subHtml){
                                    //console.log(row,i,subHtml,$(subTd),"edited")
                                    t.setDataAtCell(index,row,i,subHtml);
                                }
                            }
                        }
                        t.curStyle=tmpcurStyle;
                    }

                }
                //选中广告位，查询对应售罄信息
                if(col==0){
                    var aid = $(td).find("p").data("id"),
                        date = $(ht_this.getCell(0,2)).find("p").text(),
                        oldVal = obj[0][2];

                    t.selectAd(aid,date,ht_this,index,row,col,oldVal);
                }
            }else{
                var saleType =  $(ht_this.getDataAtCell(row, 1)).text(),
                    saleColor =t.getColor(saleType),style="";

                /*自定义样式*/
                if(t.curStyle&&!$.isEmptyObject(t.curStyle)){
                    for(i in t.curStyle){
                        $(td).find("p").css(i,"");
                    }

                    if(!t.curStyle.hasOwnProperty("background-color") && saleColor){
                        $(td).find("p").css("background-color","");
                    }

                    style = $(td).find("p").attr("style")||"";
                    for(i in t.curStyle){
                        style = style + ";"+i+":"+t.curStyle[i];
                    }
                    /*售卖方式对应样式*/
                    if(!t.curStyle.hasOwnProperty("background-color") && saleColor){
                        style = style + ";background-color:"+saleColor;
                    }
                }
                style=style.replace(";;",";")

                html="<p style='"+style+"'>"+val+"</p>";
                $(td).html(html)
                $(td).attr("data-style",style);

                if(ht_this.getDataAtCell(row, col)!=html){
                    index = $(td).parents(".js-table").index();
                    t.setDataAtCell(index,row,col,html);
                }
            }
        },
        checkRowEmpty:function(ht_this,row){
            var res = true,scol,td;
            for(var i=1;i<=31;i++){
                scol = i+1;
                td=ht_this.getCell(row,scol);
                //有值，不为空
                if($(td).find("p").text()){
                    res = false;
                    break;
                }
            }
            return res;
        },
        selectAd:function(aid,date,ht_this,index,row,col,oldVal){
            var t=this;

            var flag = t.checkRowEmpty(ht_this,row);
            var cbn = function(ht_this,row,col,oldVal){
                return function(){
                    ht_this.setDataAtCell(row,col,oldVal,"edited");
                }
            }(ht_this,row,col,oldVal);

            var cby = function(aid,date,ht_this,index,row,col,oldVal){
                return function(){
                    com.sendRq(ST.ACTION.getSoldOut,{ id:aid,date:date },function(d){
                        /*刷新数据列表*/
                        var disabled = ht_this.getSettings().cell,
                            disTd,
                            disHtml,
                            disabledObj,
                            scol;


                        //老的disable设置，删除之前该行的disable设置
                        t.deleteByRow(disabled,row);

                        if ( d && (d.status==200||d.status=="success") &&d.data) {
                            if(d.data.length<1){
                                d.data = t.getDefaultAddata()
                            }
                            for(var i=0;i<d.data.length;i++){
                                scol = d.data[i].key+1;
                                if(!d.data[i].value){
                                    disabledObj={};
                                    disabledObj["row"]=row;
                                    disabledObj["col"]=scol;
                                    disabledObj["readOnly"]=true;
                                    
                                    disabled.push(disabledObj);

                                    //设置当前背景色为灰色
                                    disTd=ht_this.getCell(row,disabledObj["col"]);
                                    disHtml="<p class='disabled' data-type='disabled'></p>";
                                    $(disTd).html(disHtml);

                                    if(ht_this.getDataAtCell(row, disabledObj["col"])!=disHtml){
                                        t.setDataAtCell(index,row,disabledObj["col"],disHtml);
                                    }
                                }else{
                                    //**清空当前行数据
                                    disTd=ht_this.getCell(row,scol);
                                    disHtml="<p></p>";
                                    $(disTd).html(disHtml);

                                    if(ht_this.getDataAtCell(row, scol)!=disHtml){
                                        t.setDataAtCell(index,row,scol,disHtml);
                                    }
                                }
                            }
                            ht_this.updateSettings({
                                cell: disabled
                            })
                        }
                    });
                }
            }(aid,date,ht_this,index,row,col,oldVal)

            if(flag){
                cby();
            }else{
                t.changeConfirm("提示","选择广告位后，当前行数据会被清空，确认执行么？",cby,cbn)
            }
           
        },
        getDefaultAddata:function(){
            var arr=[];
            for(var i=1;i<=31;i++){
                arr.push({
                    key:i,
                    value:1
                })
            }
            return arr;
        },
        changeConfirm:function(title,content,cby,cbn){
            $.dialog.destroy('ad-confirm-pop');
            var dialog = $.dialog('ad-change-confirm-pop', {
                title: title,
                content: "<div>"+content+"</div><div class='mt10 center'><a class='btn js-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 js-confirm-y' href='javascript:;'>确认</a></div>",
                onOpened:function(i,a){
                    this.$popup.find(".js-confirm-y").click(function(){ 
                        dialog.destroy();
                        cby()
                    });
                    this.$popup.find(".js-confirm-n").click(function(){
                        dialog.destroy();
                        cbn();
                    });
                }
            });
            dialog.open(true);
        },
        deleteByRow:function(arr,row){
            for(var i=0;i<arr.length;i++){
                if(arr[i].row == row){
                    arr.splice(i,1);
                    i--;
                }
            }
        },
        afterRender: function () {
            var t=this;
            com.com();
            t.areaChange();
            t.toolBar();

            $("#js-add-product").click(function(){
                var v = apdView();
                v.on("success",function(defaultkey){
                    /*window.location.reload();*/
                    var id = $("#company_id").val(),
                        url = ST.ACTION.getProduct,
                        rid= "product_id_select".split(";");
                          
                      for(var i=0;i<rid.length&&i<url.length;i++){
                          var suc = (function(id){
                            return function(d){
                              var relate = $("#"+id);
                              var name = relate.attr("name");
                              var rules = relate.attr("data-rules");
                              var obj = relate.parent();
                              var tpl='<select name="'+name+'" placeholder="请选择"  class="js-selectbox" data-rules="'+rules+'" id="'+id+'">'+
                                      '<%for(var i=0;i<data.length;i++){%>'+
                                          '<option value="<%=data[i].key%>" <%=(data[i].s==1?"selected":"")%> <%if(data[i].key==defaultkey){%> selected<%}%> ><%=data[i].value%></option>'+
                                      '<%}%></select>';
                              obj.html(_.template(tpl, {data:d.data,defaultkey:defaultkey}))
                              $("#"+id).selectbox({ searchable: true,localSearch:true});
                              v.closePop();
                            }
                          })(rid[i]);

                          if(id==""||id=="0"){
                            suc({data:[]})
                          }else{
                            $.ajax({
                              method:"post",
                              url:url+"/"+id,
                              dataType:"json"
                            }).done(suc).fail(function(){});
                          }
                          
                      }  
                });
            });

            t.$el.on("click",".js-datepicker-month",function(){
                var $el = $(this),

                    index = $el.parents(".js-table").index(),
                    $table = $el.parents(".js-table");
                WdatePicker({
                    skin:'whyGreen',
                    dateFmt:'yyyy-MM',
                    skin:'default',
                    isShowClear:false,
                    onpicking:function(dp){
                        var val = dp.cal.getNewDateStr();

                        var cby = function(t,$table,index){
                            return function(){
                                var tableData = t.model.getDefaultTable(val);
                                var data =  t.model.get("data");
                                data.table[index]=tableData;

                                obj = t.model.convertTableData();

                                t.createTable($table[0],obj.table[index],t.model.get("data").advertis,t.model.get("data").sell_type);                            }
                        }(t,$table,index)
                        t.changeConfirm("提示","改变日期将清空当前表格数据，是否执行改操作？",cby,function(){})
                    }
                });

            });

            t.$el.find(".js-save").on("click",function(){
                var status = $(this).data("status")
                /*存草稿交互*/
                //console.log(table);
                $("#js-execution-validate").attr("data-status",status);
                $("#js-execution-validate").submit();
            });

            $("#js-execution-validate").validator({
                onFormSubmit: function(e) {
                    var $f = $(e.currentTarget),
                        par = $f.serializeArray(),
                        obj={};

                    $.each(par, function() {
                        obj[this.name] = this.value;
                    });

                    /*复制table数组*/
                    obj["table"] = JSON.stringify(t.model.toJSON().data.table);
                    obj["table"] = JSON.parse(obj["table"]);

                    for(var i=0;i<obj["table"].length;i++){
                        /*一个htable数据*/
                        for(var j=0;j<obj["table"][i].length;j++){
                            /*一行row数据*/
                            for(var k=0;k<obj["table"][i][j].length;k++){
                                obj["table"][i][j][k] = t.model.changeTableVauleToObj(obj["table"][i][j][k]);
                            }
                        }
                    }

                    obj["table"] = JSON.stringify(obj["table"]);
                    obj["status"] = $f.attr("data-status");

                    validateLoading = $.notice({
                        state: 'loading',
                        content: '请稍等...',
                        autoClose: 0,
                        modal: true
                    });

                    $.ajax({
                        url:$f.attr("action"),
                        type:"post",
                        data:obj
                    }).done(function(d){
                        validateLoading.destroy()
                        if(d.status==200||d.status=="success"){
                            $.notice({
                                state: "success",
                                content: d.info
                            });
                        }else{
                            $.notice({
                                state: "error",
                                content: d.info
                            });
                        } 
                        if (d&&d.data&&d.data.url) {
                            setTimeout(function() {
                                window.location.href = d.data.url;
                            }, 2000)
                        }
                        if (d&&d.data&&d.data.id) {
                            /*id赋值到页面*/
                            $f.find("input[name='id']").val(d.data.id)
                        }
                    }).fail(function(e){
                        validateLoading.destroy()
                        $.notice({
                            state: 'error',
                            content: "操作失败"
                        });
                        console.log("form error:", e)
                    });

                    return false;
                }
            });

            /*添加跨月排期*/
            $(".js-add-htable").off("click");
            $(".js-add-htable").on("click",function(){
                var tableData=[],data;
                if(t.$el.find(".js-table").length>=3){
                    $.notice({content:"只能添加三个月份的执行单！"})
                }else{
                    tableData.push(t.model.getDefaultTable());
                    data=t.model.get("data");
                    data.table.push(tableData[0]);
                    t.model.set("data",data);
                    $(".js-delete-htable"). remove();
                    t.$el.find("#js-container-table").append($("<div class='js-table'></div><a href='javascript:;' class='btn mr5 mt20 js-delete-htable'>删除</a>"));
                    
                    obj = t.model.convertTableData();
                    index = $(".js-table").length;

                    t.createTable(t.$el.find(".js-table:last")[0],obj.table[index-1],t.model.get("data").advertis,t.model.get("data").sell_type);
                    /*删除表格*/
                    $(".js-delete-htable").on("click",function(){
                        t.$el.find(".js-table:last")[0].remove();
                        var table = t.model.get("data").table;
                        table.pop();
                        if(t.$el.find(".js-table").length<=1){
                           $(this).remove(); 
                        }
                    })
                }
            })
        },
        getColor:function(key){
            var obj = {
                "购买":"#FF7271",
                "配送":"#92d050",
                "框架":"#4bbac5",
                "额外支持":"#7598b8"
            },res = "";
            if(obj[key]){
                res = obj[key]
            }
            return res;
        },
        areaChange:function(){
            var t=this,target_type=t.model.get("data").target_type;

            //区域定向
            $(".js-area").click(function(){
                var value = $(".js-area:checked").val();
                if(value==2){
                    $("#netbar").hide()
                    $("#_cityselect").show();
                    //获取服务端数据
                    ST.getJSON(ST.ACTION.area, '', function (j) {
                        if (! j || ! j.data) return;
                        var CityData = j.data;
                        /*获得已选值*/
                        var $cityIpt = $('#_city'), $pvsIpt = $('#_pvs'),
                            pvs      = $pvsIpt.val(), cities = $cityIpt.val();
                        var selector = new ST.CityDataSelector({
                            dataSource: CityData,
                            idkey     : 'id'
                        });
                        pvs          = pvs ? pvs.split(',') : [];
                        cities       = cities ? cities.split(',') : [];
                        $.each(pvs, function (i, v) {
                            var clist = selector.getClistByPid(v);
                            clist && $.each(clist, function (j, vv) {
                                cities.push(vv[selector.idkey]);
                            });
                        });
                        /*初始化插件*/
                        var cityselect = new ST.CitySelect('_cityselect', {
                            dataSource: CityData,
                            selected  : cities
                        });
                        /*定义接口*/
                        cityselect.onChange = function () {
                            var that  = this, cityValue;
                            cityValue = that.getSelected();
                            /*将省市值输出到相应的INPUT*/
                            $cityIpt.val(cityValue.join(','));
                        };
                    }, '', 'GET');

                }else if(value==1){
                    $("#_cityselect").hide();
                    $("#netbar").hide();
                }else if(value==3){
                    $("#_cityselect").hide();
                    $("#netbar").show();
                }
            });
            $("#netbar").on("keyup paste",function(){t.splitBycomma(this)});
            /*初始化*/

            $(".js-area[value='"+target_type+"']").prop("checked",true);
            $(".js-area[value='"+target_type+"']").trigger("click");
        },
        toolBar:function(){
            var t=this;
            $(".s-toolbar-icon").on("mouseenter",function(){
                $(this).siblings(".s-tooltip").show();
            })
            $(".s-toolbar-icon").on("mouseleave",function(){
                $(this).siblings(".s-tooltip").hide();
            })

            $(".js-bold").on("click",function(){
                $(this).toggleClass("selected");
                if(t.curStyle["font-weight"]){
                    t.curStyle["font-weight"]="";
                }else{
                    t.curStyle["font-weight"]="bold";
                }
            })


            $(".js-color").on("mouseenter",function(){
                $(this).addClass("selected");
                $(this).find(".menu-wrap").removeClass("hide");
                
            })
            $(".js-color").on("mouseleave",function(){
                $(this).removeClass("selected");
                $(this).find(".menu-wrap").addClass("hide");
                
            })

            $(".js-color .color-menu-item").on("click",function(){
                var color = $(this).data("val");
                $(".js-color .color-underline").css("background-color",color)

                t.curStyle["color"]=color;
            })

            $(".js-bgcolor").on("mouseenter",function(){
                $(this).addClass("selected");
                $(this).find(".menu-wrap").removeClass("hide");
            })
            $(".js-bgcolor").on("mouseleave",function(){
                $(this).removeClass("selected");
                $(this).find(".menu-wrap").addClass("hide");
            });

            $(".js-bgcolor .color-menu-item").on("click",function(){
                var color = $(this).data("val");
                $(".js-bgcolor .color-underline").css("background-color",color)

                t.curStyle["background-color"]=color;

            })

            $(".js-align").on("mouseenter",function(){
                $(".js-align").removeClass("unactive");
            })
            $(".js-align").on("mouseleave",function(){
                $(".js-align").addClass("unactive");
            })

            $(".js-align .align-menu-item").on("click",function(event){
                var align = $(this).data("val");
                var cls = $(this).find(".s-toolbar-icon").attr("class")
                $(".js-align .d-menu-display-wrap .s-toolbar-icon:first").attr("class",cls)

                t.curStyle["text-align"]=align;
                $(".js-align").addClass("unactive");
                event.stopPropagation();
            })
        },
        splitBycomma:function(node,delay){
            if(!node) return;
            var $this =$(node);
            var fn=function(){
                $this.val($this.val().replace(/[^0-9]/g,',').replace(/,{2,}/,','));
            };
            if(delay){
                setTimeout(fn,50);
            }else{
                fn();
            }
        }
    });
    return function (options) {
        return new View(options);
    }
});