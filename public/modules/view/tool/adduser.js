define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/tool/addUser.html");
    var M = require("model/tool/adduser");
    var com = require("lang/common");

    require("ui/validator");
    require("ui/dialog");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            console.log(pars)
            if(pars&&pars.id){
                t.title="编辑用户";
            }else{
                t.title="新增用户";
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
            console.log(data)
            
            var timestamp  = Date.parse(new Date());
            t.id='add-user-pop'+timestamp
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
            com.selectInit();
            com.formValidate(function(d){
                if(d.info){
                    $.notice({ content: d.info });
                }
                if(d.status=="success"){
                    t.trigger("success",d.data[0]);
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