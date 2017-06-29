define(function (require) {
    var Base = require("base");
    var com = require("lang/common");
    var tpl=require("tpl/advance/refund.html");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.id = pars.id||"";
            t.company = pars.company||"";
            t.title = pars.title||"";
            t.url = pars.url||"";

            t.amount = "";
            t.render();
        },
        render:function(){
            var t=this;

            $.dialog.destroy('margin-refund-confirm-pop');
            var d = $.dialog('margin-refund-confirm-pop', {
                title: '保证金'+t.id+t.title,
                content: _.template(tpl, {
                    company:t.company,
                    title:t.title,
                    actionUrl:t.url,
                    id:t.id,
                    amount:t.amount
                }),
                onOpened:function(i,a){
                    this.$popup.find(".js-confirm-n").click(function(){ 
                        d.destroy();
                    });
                },
                onClosed:function(){
                    t.trigger("close");
                }
            });
            com.formValidate(function(data){
                 /*刷新数据列表*/
                 if(data.info){
                    $.notice({content:data.info});
                 }
                 t.render();
                 //d.destroy();
            });
            d.open(true);
        }
    });
    return function (options) {
        return new View(options);
    }
});