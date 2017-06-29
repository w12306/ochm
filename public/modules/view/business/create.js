define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/business/create.html");
    var partTpl = require("tpl/business/createPart.html");
    var executionPopTpl = require("tpl/business/executionPop.html");
    var apnView = require("view/tool/addpartner");
    var apdView = require("view/tool/addproduct");
    var M = require("model/business/create");
    var com = require("lang/common") 
    require("ui/selectbox");
    require("ui/combobox");
    require("ui/datepicker");
    require("ui/validator");
    require("ui/dialog");
    require("ui/popup");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.model = new M(pars.model);
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON();
            t.$el.show().html(_.template(t.template, {data:data.data}));
        },
        afterRender: function(){
            var t=this;
            com.com();

            $("#budget").on("blur",function(){
                if($("#amount").val()!=""||$(this).val()=="") return;
                $("#amount").val($(this).val());
                $(this).off("blur");
            });

            $("input[data-group='deparment']").on("click",function(){

                // if($(this).attr("is-checked") == "0"){

                    var tpl="<%for(var i=0;i<data.length;i++){%>"+
                                "<div class='m5'><span style='width:160px; display:inline-block;' class='pr10'><%=data[i].team%></span>"+
                                "<input class='input input-small' name='<%=name%>_<%=data[i].id%>' type='text' data-rules='required'/></div>"+
                            "<%}%>";
                    var data=[],oldData=[];

                    
                    function addOldData(){
                        var obj = {};
                        if($(this).val()){
                            obj.name = $(this).attr("name");
                            obj.value = $(this).val();
                            
                            oldData.push(obj);
                        }
                    }

                    /*记录已输入数据*/
                    $(".js-department-input-y input").each(addOldData);
                    $(".js-department-input-act input").each(addOldData);


                    $("input[data-group='deparment']:checked").each(function(){
                        var obj = {};
                        obj.id=$(this).val();
                        obj.team=$(this).attr("data-department-name");
                        data.push(obj);
                    });

                    if(data.length<2){
                        $(".js-department-input-act").html("");
                        $(".js-department-input-y").html("");
                        return;
                    }


                    $(".js-department-input-act").html(_.template(tpl,{data:data,name:"act"}));
                    $(".js-department-input-y").html(_.template(tpl,{data:data,name:"y"}));
                    /*重写匹配的已输入数据*/
                    for(var i=0;i<oldData.length;i++){
                        if($("input[name='"+oldData[i].name+"']")){
                            console.log(oldData[i].name)
                            $("input[name='"+oldData[i].name+"']").val(oldData[i].value);
                        }
                    }

                // }else if($(this).attr("is-checked") == "1"){
                //     return false;
                // }

            });

            $("#js-add-partner").click(function(){
                var v = apnView();
                v.on("success",function(){
                    window.location.reload();
                });
            });
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

            /*合同类型=4，隐藏合同编号*/
            $("#contract_type_select").on("change",function(){
                var id = $(this).val();
                if(id==4){
                    $("#contract_ckey_select_td").hide();
                    return false;
                }else{
                    $("#contract_ckey_select_td").show();
                }
            });

            t.showExecution();
        },
        showExecution:function(){
            var t=this;
            /*选中后改变对应客户名称，合作方，产品名称*/
            $(".js-execution-selectbox").on('selected.bee.selectbox', function(e, item, toAdd, toRemove) {
                var id = item.id;
                if(!id){
                    return false;
                }
                $.ajax({
                    url:ST.ACTION.executionDetail,
                    type:"post",
                    data:{id:id,type:1},
                    dataType:"json"
                }).done(function(d){
                    if(d.status=="success"&&d.data){
                        var html = _.template(partTpl,{data:d.data})
                        t.$el.find(".js-execution-relate-tr").remove();
                        t.$el.find(".js-execution-tr").after(html);
                        $('.js-execution-relate-tr .js-selectbox').selectbox({
                            searchable: true,
                            localSearch: true
                        });
                        com.selectRelate();
                    }
                })

            })

            /*点击显示执行单详情*/
            t.$el.find(".js-execution").on("click",function(){
                var eid = $("#execution_id").val();
                if(!eid){
                    $.notice({content:"请选择执行单id"});
                    return false;
                }

                $.ajax({
                    url:ST.ACTION.executionDetail,
                    type:"post",
                    data:{id:eid,type:2},
                    dataType:"json"
                }).done(function(d){
                    if(d.status=="success"&&d.data){
                        var data = d.data.table,
                            table = _.template(executionPopTpl,{data:d.data})
                        
                        $("body").append(table);

                        t.createTable($("#execution-table")[0],data);
                        $(".js-execution-popup .js-close").on("click",function(){
                            $(".js-execution-popup").remove();
                        });
                    }else{
                        $.notice({content:d.info});
                    }
                }).fail(function(){
                    $.notice({content:"操作失败"});
                });
            })
        },
        createTable:function($el,data){
            /*创建类excel表格*/
            var t=this;

            for(var i=0;i<data.length;i++){
                /*一个htable数据*/
                for(var j=0;j<data[i].length;j++){
                    /*一行row数据*/
                    for(var k=0;k<data[i][j].length;k++){
                        data[i][j][k] = t.changeTableValueToHtml(data[i][j][k]);
                    }
                }
            }
            new Handsontable($el,{
                data: data[0],
                rowHeaders: true,
                colHeaders: true,
                fixedRowsTop: 2,
                fixedColumnsLeft: 3,
                contextMenu: true,
                mergeCells: [
                    {row: 0, col: 0, rowspan: 2, colspan: 1},
                    {row: 0, col: 1, rowspan: 2, colspan: 1},
                    {row: 0, col: 2, rowspan: 2, colspan: 1},
                    {row: 0, col: 3, rowspan: 1, colspan: 31}
                ],
                colWidths:[150,200,80,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30,30],
                cells: function (row, col, prop) {
                    var cellProperties = {};

                    cellProperties.readOnly = true;
                    return cellProperties;
                },
                renderer:"html"
            });
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
                            val=obj[i];
                            break;
                    }
                }
            }

            res = "<p  style='"+style+"'  data-id='"+id+"'>"+val+"</p>";
            
            return res;
        }
    });

    return function (options) {
        return new View(options);
    }
});