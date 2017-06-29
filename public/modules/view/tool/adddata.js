define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/tool/addData.html");
    var teamTpl = require("tpl/tool/addTeam.html");
    var bussType = require("tpl/tool/addBussType.html");
    var depTpl = require("tpl/tool/addDep.html");
    var adTpl = require("tpl/tool/addAdPos.html");
    //var M = require("model/tool/adddata");
    var com = require("lang/common");

    require("ui/validator");
    require("ui/dialog");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.type = pars.type||"";
            t.typename = pars.typename||"";
            t.name = pars.name||"";
            t.detail = pars.detail||"";
            t.dataid = pars.id||"";
            t.value = pars.value||"";
            t.btype = pars.btype||"";

            /*执行小组  部门字段*/
            if(typeof pars.dep!="undefined"){
                t.dep = pars.dep;
            }
            /*部门  合同字段*/
            if(typeof pars.contract!="undefined"){
                t.contract = pars.contract;
            }

            if(pars.view&&pars.view=="team"){
                t.template = teamTpl;
            }

            if(pars.type&&pars.type=="business_line"){
                t.template = bussType;
            }

            if(pars.view&&pars.view=="dep"){
                t.template = depTpl;
            }

            if(pars.view&&pars.view=="ad"){
                t.template = adTpl;
            }

            if(pars&&pars.id){
                t.title="编辑"+t.typename;
            }else{
                t.title="新增"+t.typename;
            }
            t.render();
            t.afterRender();
        },
        render:function(){
            var t=this;
            
            var timestamp  = Date.parse(new Date()),obj={};
            t.id='add-data-type-pop'+timestamp;

            obj = {
                type:t.type,
                typename:t.typename,
                name:t.name,
                detail:t.detail,
                id:t.dataid,
                value:t.value,
                btype:t.btype
            };

            if(typeof t.dep!="undefined"){
                obj.dep = t.dep; 
            }
            if(typeof t.contract!="undefined"){
                obj.contract = t.contract;
            }

            var d = $.dialog(t.id, {
                title: t.title,
                content: _.template(t.template, obj)
            });
            d.open(true);
        },
        afterRender: function () {
            var t=this;
            com.com();
            com.formValidate();
            $(".js-add-data-type-n").click(function(){
                $.dialog.destroy(t.id);
            });
            
            $("#js-data-form-validate").validator({
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

        }
    });
    return function (options) {
        return new View(options);
    }
});