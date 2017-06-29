define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/tool/addproduct.html");
    var M = require("model/tool/addproduct");
    var com = require("lang/common")

    require("ui/validator");
    require("ui/dialog");
    require("ui/selectbox");
    require("ui/notice");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            if(pars&&pars.id){
                t.title="编辑产品";
            }else{
                t.title="新增产品";
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
            t.id='add-product-pop'+timestamp
            var d = $.dialog(t.id, {
                title: t.title,
                content: _.template(t.template, {data:data.data})
            });
            d.open(true);
        },
        afterRender: function () {
            var t=this;
            $('.js-selectbox').selectbox({
                searchable: true,
                localSearch:true
            });
            var flag=false,$special=$("#js-add-partner-form-validate .type-special");
            $("#js-add-partner-form-validate input[name='type']:checked").each(function(){
                if($(this).val()=="网页游戏"||$(this).val()=="网络游戏"||$(this).val()=="手机游戏"){
                    flag=true;
                    $special.show();
                    return false;
                }
            });

            $("#js-add-partner-form-validate input[name='type']").click(function(){
                if($(this).val()=="网页游戏"||$(this).val()=="网络游戏"||$(this).val()=="手机游戏"){
                    flag=true;
                    $special.show();
                }else{
                    $special.hide();
                }
            });

            $("#js-add-partner-form-validate").validator({
                onFormSubmit:function(){
                    validateLoading = $.notice({state: 'loading', content: '请稍等...',autoClose:0,modal: true});
                },
                ajaxSubmitOption:{
                    success:function(d){
                        validateLoading.destroy();
                        if(d.status=="success"){
                            t.trigger("success",d.data.id);
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
            $(".js-cancel").click(function(){
                t.closePop();
            });
        },
        closePop:function(){
            $.dialog.destroy(this.id);
        }
    });
    return function (options) {
        return new View(options);
    }
});