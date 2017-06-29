define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/common/table.html");
    var M = require("model/common/table");
    var com = require("lang/common");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.model = new M({url:ST.ACTION.dataList});
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
            com.com();
        },
        render:function(){
            var t=this,
                obj = t.model.convertData();
            console.log(obj)
            t.$el.show().append(_.template(t.template,obj));
        },
        afterRender: function () {
            var t=this;

            $(".js-data-delete").click(t.deleteElement);
        },
        deleteElement:function(){
            var id = $(this).attr("data-id");
            $.dialog.destroy('data-confirm-pop');
            var d = $.dialog('data-confirm-pop', {
              title: '删除',
              content: "<div>确认要删除么？</div><div class='mt10 center'><a class='btn js-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 js-confirm-y' href='javascript:;'>确认</a></div>",
              onOpened:function(i,a){
                this.$popup.find(".js-confirm-y").click(function(){ 
                  var url = ST.ACTION.dataDelete+"/"+id;
                  com.sendRq(url,{});
                });
                this.$popup.find(".js-confirm-n").click(function(){
                  d.destroy();
                });
              }
            });
            d.open(true);
        }
    });
    return function (options) {
        return new View(options);
    }
});