define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/baddebt/addbedt.html");
    

    require("ui/validator");
    require("ui/dialog");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            if(pars&&pars.id){
                t.title="编辑坏账";
            }else{
                t.title="新增坏账";
            }

            t.busid = pars.busid || "";
            t.deliveryid = pars.deliveryid || "";
            t.debtid = pars.id || "";
            t.amount = pars.amount || "";
            t.month = pars.month||"";
            t.team = pars.team||"";
            t.render();
            t.afterRender();

        },
        render:function(){
            var t=this;
            
            var timestamp  = Date.parse(new Date());
            t.id='add-debt-pop'+timestamp
            var d = $.dialog(t.id, {
                title: t.title,
                content: _.template(t.template, {
                    busid:t.busid,
                    deliveryid:t.deliveryid,
                    id:t.debtid,
                    amount:t.amount,
                    month:t.month,
                    team:t.team
                })
            });
            d.open(true);
        },
        afterRender: function () {
            var t=this;
            $(".js-add-debt-n").click(function(){
              $.dialog.destroy(t.id);
            });

            $("#js-debt-form-validate").validator({
                onFormSubmit:function(){
                  validateLoading = $.notice({state: 'loading', content: '请稍等...',autoClose:0,modal: true});
                },
                ajaxSubmitOption:{
                    success:function(d){
                        validateLoading.destroy();
                        if(d.status=="success"){
                            t.trigger("success");
                        }else{
                            $.notice({ state: 'error',content: d.info });
                        }
                    },
                    error:function(d){
                        validateLoading.destroy();
                        $.notice({ state: 'error',content: "操作失败" });
                    }
                }
            });
        }
    });
    return function (options) {
        return new View(options);
    }
});