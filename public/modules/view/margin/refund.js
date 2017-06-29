define(function (require) {
    var Base = require("base");
    var M = require("model/margin/refundlist");
    var com = require("lang/common");
    var tpl=require("tpl/margin/refund.html");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.id = pars.id||"";
            t.company = pars.company||"";
            t.title = pars.title||"";
            t.url = pars.url||"";
            t.deleteUrl = pars.deleteUrl;
            t.amount = "";
            t.aid = "";
            t.model = new M({
                url:pars.listUrl,
                id:t.id,
            });

            t.model.on("sync",function(){
                t.render();
            })
            //com.com();
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON().data;
            $.dialog.destroy('margin-refund-confirm-pop');
            var d = $.dialog('margin-refund-confirm-pop', {
                title: '保证金'+t.id+t.title,
                content: _.template(tpl, {
                    company:t.company,
                    title:t.title,
                    actionUrl:t.url,
                    id:t.id,
                    amount:t.amount,
                    data:data,
                    aid:t.aid
                }),
                onOpened:function(i,a){
                    this.$popup.find(".js-confirm-n").click(function(){ 
                        d.destroy();
                    });
                    this.$popup.find(".js-amount-edit").click(function(){
                        t.amount = $(this).data("amount");
                        t.aid = $(this).data("id");
                        t.render();
                    });
                    this.$popup.find(".js-amount-delete").click(function(){
                        var id=$(this).data("id");
                        t.deleteData(id);
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
                 t.aid = "";
                 t.amount = "";
                 t.model.fetch()
                 //t.render();
                 //d.destroy();
            });
            d.open(true);
        },
        afterRender: function () {
            var t=this;
        },
        deleteData:function(id){
            var t=this;
            $.dialog.destroy('data-confirm-pop');
            var d = $.dialog('data-confirm-pop', {
                title: '删除',
                content: "<div>确认要删除么？</div><div class='mt10 center'><a class='btn js-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 js-confirm-y' href='javascript:;'>确认</a></div>",
                onOpened:function(i,a){
                    this.$popup.find(".js-confirm-y").click(function(){ 
                        var url = t.deleteUrl;
                        com.sendRq(url,{id:id},function(data){
                            /*刷新数据列表*/
                            if(data.info){
                                $.notice({
                                    content: data.info
                                });
                            }
                            d.destroy();
                            t.model.fetch();
                        });
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