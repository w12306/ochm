define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/tool/addpartner.html");
    var M = require("model/tool/addpartner");

    require("ui/validator");
    require("ui/dialog");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            if(pars&&pars.id){
                t.title="编辑合作方";
            }else{
                t.title="新增合作方";
            }
            t.model = new M({id:pars.id});
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON();
            
            var timestamp  = Date.parse(new Date());
            t.id='add-partner-pop'+timestamp
            var d = $.dialog(t.id, {
                title: t.title,
                content: _.template(t.template, {data:data.data})
            });
            d.open(true);
        },
        closePop:function(){
            var t=this;
            $.dialog.destroy(t.id);
        },
        afterRender: function () {
            var t=this;

            $("#js-add-partner-form-validate").validator({
                onFormSubmit:function(){
                    validateLoading = $.notice({state: 'loading', content: '请稍等...',autoClose:0,modal: true});
                },
                ajaxSubmitOption:{
                    success:function(d){
                        validateLoading.destroy();
                        if(d.info){
                            $.notice({ content: d.info });
                        }
                        if(d.status=="success"){
                            t.trigger("success",d.data[0]);
                        }
                    },
                    error:function(d){
                        validateLoading.destroy();
                        $.notice({ state: 'error',content: "操作失败" });
                    }
                }
            });
            $(".js-cancel").click(function(){
                t.closePop();
            });
        }
    });
    return function (options) {
        return new View(options);
    }
});