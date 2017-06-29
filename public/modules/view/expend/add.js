define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/invoice/add.html");
    var M = require("model/common/table");
    var com = require("lang/common");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.listAction = pars.listAction;
            com.com();

            /*新增页面*/
            if($(".js-search-btn").length>0){
                $(".js-search-btn").click(function(){
                    t.checkModelLoad();
                });
                if($(".js-search-btn").attr("data-type")=="auto"){
                    t.checkModelLoad();
                }
            }else{
                t.checkModelLoad();
            }
        },
        checkModelLoad:function(){
            var t=this;
            var partner_id = $(".js-partner").val(),
                business_key = $(".js-business").val(),
                id = $("#id").val();
            
            if(!t.model){
                t.model = new M({
                    url:t.listAction,
                    data:{
                        partner_id:partner_id,
                        business_key:business_key,
                        id:id
                    }
                });
            }else{
                t.model.changePars({
                    partner_id:partner_id,
                    business_key:business_key,
                    id:id
                });
            }
            
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
            t.model.on("error",function(m,res){
                t.errorRender(res);
            });
        },
        errorRender:function(){
            this.$el.show().html("未查询到数据");
        },
        render:function(){
            var t=this,
                obj = t.model.convertData();

            if($.isEmptyObject(obj.data)){
                t.errorRender();
                t.$el.show().append(_.template(t.template,obj));
            }else{
                t.$el.show().html(_.template(t.template,obj));
            }
        },
        afterRender: function () {
            var t=this;
            com.setComTableWidth(t.$el);
            t.sumAmount();
            $(".js-amount-input").on("blur",function(){
                var val = $(this).val();
                var max = $(this).data("max").toString();
                var tips = $(this).data("tips");

                if(tips){
                    // if(!/^(\d*)(\.\d*)?$/.test(val)){
                    //     $(this).val("");
                    // }

                    if(max&& parseFloat(val)>parseFloat(max)){
                        $(this).val("");
                        $.notice({content:tips})
                    }    
                }
                

                /*
                if(val==0){
                    if($("#invoice_id").val()!="0"){
                        var id=$(this).data("id")
                        t.deleteAmount(id,$(this).data("value"),$(this));
                    }else{
                        $(this).val("");
                    }
                    return;
                }
                */
                
                //$(this).attr("data-value",val);
                t.sumAmount();
            });     
        },
        deleteAmount:function(id,val,$el){
            var t=this;
            $.dialog.destroy('select-add-pop');
            var dialog = $.dialog('select-add-pop', {
                title: '提示',
                content: "<p>确认要删除？</p>"+
                        "<a class='btn btn-blue js-amount-delete-btn' href='javascript:;'>确认</a>"+
                        "<a class='btn ml10 js-amount-delete-close' href='javascript:;'>取消</a>",
            });
            dialog.open(true);
            $(".js-amount-delete-close").click(function(){
                $el.val(val)
                dialog.destroy();
            });
            $(".js-amount-delete-btn").click(function(){
                $.ajax({
                    url:ST.ACTION.deleteAmount,
                    data:{id:id},
                    type:"post",
                    dataType:"json",
                    success:function(d){
                        $.notice({content:d.info});
                        if(d.status=="success"){
                            /*刷新列表*/
                            if(d.data&&d.data.url){
                                location.href=d.data.url;
                            }else{
                                t.checkModelLoad();
                            }
                        }
                        if(d.status=="error"){
                            $el.val(val)
                        }
                        dialog.destroy();
                    }
                });
            });
        },
        /*计算发票金额总和*/
        sumAmount:function(){
            var total = 0;
            var t = this;

            $(".js-amount-input").each(function(){
                var val = $(this).val();
                if(val!=""){
                    total = parseFloat(val)+total;
                    total = t.formatFloat(total,5)
                }
            });
            /*
            $(".js-amount-txt").each(function(){
                var val = $.trim($(this).text());
                total = parseFloat(val)+total;
            });
            */
            $("#total_amount").val(total.toFixed(2));
        },
        formatFloat:function(f, digit) { 
            var m = Math.pow(10, digit); 
            return parseInt(f * m, 10) / m; 
        } 
    });
    return function (options) {
        return new View(options);
    }
});