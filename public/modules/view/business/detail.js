define(function (require) {
    var Base = require("base");
    var com = require("lang/common");
    var tpl = require("tpl/business/relation.html");
    var M = require("model/business/relation");

    require("ui/selectbox");
    require("ui/datepicker");
    require("ui/dialog");
    require("ui/notice");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.model1 = new M({url:ST.ACTION.bussinessDetail1});
            t.model2 = new M({url:ST.ACTION.bussinessDetail2});

            /*业务关联表1*/
            t.model1.on("sync",function(){
                t.render(t.model1);
                t.afterRender();
            });

            /*业务关联表2*/
            t.model2.on("sync",function(){
                t.render(t.model2);
                t.afterRender();
            });

            t.editAmount();
            t.checkBus();
        },
        editAmount:function(){
            /*编辑金额交互*/
            var id = id = $("#busid").val();
            var f = function(e){
                if($(this).find("input").length>0) return false;
                var t=this;
                $(t).off("click"); 
                var name = $(t).data("name"),
                    val = $.trim($(t).find(".js-txt").text());

                $(t).find(".js-txt").html("<input type='text' value='"+val+"'/>");
                $(t).find("input").keydown(function(event){
                    if(event.which == "13"){
                        send(this);
                    }   
                });
                $(t).find("input").blur(send);

                function send(){
                    var curval = $.trim($(this).val());
                    if(curval&&/^(-?\d+)(\.\d+)?$/.test(curval)&&val!=curval){
                        $.ajax({
                            url:ST.ACTION.bussinessAmountEdit,
                            data:{name:name,value:curval,id:id},
                            type:"post",
                            dataType:"json",
                            success:function(d){
                                if(d.info){
                                    $.notice({ content: d.info});
                                }
                                if(d.status=="success"){
                                    $(t).find(".js-txt").text(curval);
                                }
                                setTimeout(function(){
                                    $(t).on("click",f); 
                                },300);
                            },
                            error:function(e){
                                $(t).find(".js-txt").text(val);
                                $.notice({state: 'error', content: '操作失败'});
                                setTimeout(function(){
                                    $(t).on("click",f); 
                                },300);
                            }
                        });
                    }else{
                        $(t).find(".js-txt").text(val);
                        setTimeout(function(){
                            $(t).on("click",f); 
                        },300);
                    }                    
                }
            }
            $(".js-edit-amount").on("click",f);
        },
        checkBus:function(){
            /*审核操作*/
            var id = id = $("#busid").val();
            $(".js-bus-check").on("click",function(){
                var type = $(this).data("type"),
                    url = ST.ACTION.businessCheck;
                com.sendRq(url,{type:type,id:id});
            });
        },
        render:function(model){
            var t=this,
                obj = model.convertData();
            t.$el.show().append(_.template(t.template,obj));
        },
        afterRender: function () { 
            var t=this;
            $(".js-scroll-box").each(function(){
                var totalw=0;
                $(this).find(".datatable-col").each(function(){
                    totalw = totalw + $(this).width();
                });
                $(this).width(totalw);
            });
            com.setComTableWidth(t.$el);
        }
    });
    return function (options) {
        return new View(options);
    }
});