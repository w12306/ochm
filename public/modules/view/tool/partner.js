define(function (require) {
    var Base = require("base");
    var apdView = require("view/tool/addpartner");

    var View = Base.View.extend({
        //template: tpl,
        initialize: function (pars) {
            var t = this;
            t.afterRender();
        },
        render:function(){
        },
        afterRender: function () {
            $("#js-add-partner").click(function(){
                var v = apdView();
                v.on("success",function(){
                    window.location.reload();
                });
            });
            $(".js-partner-edit").click(function(){
                var id = $(this).data("id");
                var v = apdView({id:id});
                v.on("success",function(){
                    window.location.reload();
                });
            })
        }
    });
    return function (options) {
        return new View(options);
    }
});