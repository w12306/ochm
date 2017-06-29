define(function(require) {
    var Base = require("base");
    var tpl = require("tpl/tool/newUser.html");
    var M = require("model/tool/newUser");
    var com = require("lang/common");
    require("ui/validator");
    require("ui/dialog");
    var View = Base.View.extend({
        template: tpl,
        initialize: function(pars) {
            var t = this;
            t.model = new M();
            t.model.on("sync", function() {
                t.render();
                t.afterRender();
            });
        },
        render: function() {
            var t = this;
            var data = t.model.toJSON();
            t.$el.html(_.template(t.template, {
                data: data
            }));
        },
        afterRender: function() {
            var t = this;
            com.formValidate(function(d) {
                if (d.info) {
                    $.notice({
                        content: d.info
                    });
                }
                if (d.status == "success") {
                    t.trigger("success", d.data[0]);
                }
            });
            com.role();
            com.selectInit();
            $("#all-range").click(function() {
                var check = $(this).prop("checked");
                var $checkbox = $(".js-toggle-box").find(".js-toggle-con [type='checkbox']");
                $(".js-toggleall").prop("checked", check);
                $checkbox.prop("checked", check)
            })
            var checkbox = $(".js-toggle-box").find("input[type='checkbox']");
            checkbox.click(function() {
                for (var i = 0; i < checkbox.length; i++) {
                    if(!checkbox[i].checked){
                        $("#all-range").prop("checked", false);
                        break;
                    }else{
                        $("#all-range").prop("checked", true);
                    }
                }
            })
        }
    });
    return function(options) {
        return new View(options);
    }
});