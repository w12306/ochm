define(function (require) {
    var Base = require("base");
    var Backbone = require('backbone');
    var com = require("lang/common")
    var M = require("model/business/addamount"); 
    var tpl = require("tpl/business/addamount.html");
    var tplform = require("tpl/business/amountform.html")
    var amountDetail = require("tpl/business/amountDetail.html")

    require("ui/selectbox");
    require("ui/datepicker");
    require("ui/dialog");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            if(!pars.id) return false;
            t.busid = pars.id;
            t.title = pars.title||"";
            t.model = new M({id:pars.id});
            t.model.on("sync",function(){
                $.dialog.destroy('add-amount-pop');
                var d = $.dialog('add-amount-pop', {
                  title: t.title,
                  content: '<div id="js-amount-list"></div>'
                });
                
                t.$el = $("#js-amount-list");

                t.title = "新增执行额";
                t.fdata = {amount:"",month:"",id:""};
                t.render();
                t.afterRender();
                d.open(true);

                $(document).on("click",".add-amount-n,.dialog-close",function(){
                    $.dialog.destroy('add-amount-pop');
                    t.trigger("success");
                });
            });

            t.model.on({"change:delivery_amount": function(){
                console.log(this)
            }})
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON();
            t.$el.html(_.template(tplform, {data:t.fdata,title:t.title,busid:t.busid}));
            t.$el.append(_.template(t.template, {data:data.data,busid:t.busid}));

            data.data.forEach(function(item){
                if(item.month  == "总计"){
                    t.options.delivery_amount = item.amount;
                }
            })

            if(typeof this.options.delivery_amount == "string"){
                
                this.options.delivery_amount = parseFloat(this.options.delivery_amount.replace(/,/g,""))
            }else if(typeof this.options.active_amount == "string"){
                this.options.active_amount = parseFloat(this.options.active_amount.replace(/,/g,""))
            }
            t.$el.find("#amount-title").before(_.template(amountDetail, this.options));
        },
        reRender:function() {
            var t = this;
            t.model.fetch();

            t.model.on("sync",function(){
                t.title = "新增执行额";
                t.fdata = {amount:"",month:"",id:""};
                t.render();
                t.afterRender();
            });
        },
        afterRender: function () {
            var t = this;
            var dp = $('#_calender_month').datepicker({
                toSelect: "month",
                onPopupOpened:function(t,e){
                }
            });

            $(".js-amount-edit").click(function(){
                t.amountEdit.call(this,t);
            });

            $(".js-amount-delete").click(function(){
                t.amountDelete.call(this,t);
            });
            $("#js-amount-form-validate").validator({
                onFormSubmit:function(){
                  validateLoading = $.notice({state: 'loading', content: '请稍等...',autoClose:0,modal: true});
                },
                ajaxSubmitOption:{
                    success:function(d){
                        validateLoading.destroy();
                        if(d.info){
                            $.notice({content: d.info});
                        }
                        if(d.status=="success"){
                            t.reRender();
                        }
                    },
                    error:function(d){
                        validateLoading.destroy();
                        $.notice({ state: 'error',content: "操作失败" });
                    }
                }
            });
        },
        amountEdit:function(t){
            var id = $(this).data("id"),
                month = $(this).data("month"),
                amount = $(this).data("amount");
            t.title = "编辑执行额";
            t.fdata = {amount:amount,month:month,id:id};
            t.render();
            t.afterRender();
        },
        amountDelete:function(t){
            var id = $(this).data("id");
            $.dialog.destroy('amount-delete-pop');
            var d = $.dialog('amount-delete-pop', {
                title: '删除执行额',
                content: "<div>是否确认删除执行额？</div><div class='mt10 center'><a class='btn amount-delete-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 amount-delete-confirm-y' href='javascript:;'>确认</a></div>"
            });
            d.open(true);

            $(".amount-delete-confirm-y").click(function(){
                d.destroy();
                t.model.trigger("deleteAmount",id,function(){
                    t.reRender();
                });
            });
            $(".amount-delete-confirm-n").click(function(){
                d.destroy();
            });
        }
    });
    return function (options) {
        return new View(options);
    }
});