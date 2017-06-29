define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/invoice/list.html");
    var M = require("model/common/table");
    var com = require("lang/common");
    //var ptpl=require("tpl/margin/refund.html");
    var Page = require("view/common/page.js");
    var Refund = require("view/advance/refund");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.search();
            com.com();
            $("#js-search-btn").click(function(){
                t.search.call(t);
            });
        },
        search:function(){
            var t=this;
            var searchData = _.toParam($("#search-box").serializeArray());
            var page = t.children.findByCustom("page");
            if(page){
                searchData["page"]=page.model.getCurPage();
            }
            if(!t.model){
                t.model = new M({url:ST.ACTION.advanceList,data:searchData});
            }else{
                t.model.changePars(searchData);
            }

            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
            t.model.on("error",function(m,res){
                t.errorRender(res);
            });
        },
        errorRender:function(res){
            this.$el.show().html("查找失败");
        },
        render:function(){
            var t=this,
                obj = t.model.convertData();

            if($.isEmptyObject(obj.data)){
                t.$el.show().html("未查找到数据");
            }else{
                t.$el.show().html(_.template(t.template,obj));
                $("#js-page").show();
            }
        },
        afterRender: function () {
            var t=this;
            com.setComTableWidth(t.$el);
            var page = new Page({
                el:$("[data-view='page']"),
                data:t.model.getPage()
            })
            t.addChildView(page, 'page');
            page.on("paging",function(p){
                console.log("go to page:",p)
                t.search();
            })

            $(".js-data-delete").click(function(){
                t.deleteData.call(this,t)
            });
            $(".js-data-refund").click(function(){
                t.refund.call(this,t)
            });
            $(".js-data-deduct").click(function(){
                t.deduct.call(this,t)
            });
        },
        deleteData:function(t){
            var id = $(this).attr("data-id");
            $.dialog.destroy('data-confirm-pop');
            var d = $.dialog('data-confirm-pop', {
              title: '删除',
              content: "<div>确认要删除么？</div><div class='mt10 center'><a class='btn js-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 js-confirm-y' href='javascript:;'>确认</a></div>",
              onOpened:function(i,a){
                this.$popup.find(".js-confirm-y").click(function(){ 
                    var url = ST.ACTION.dataDelete;
                    com.sendRq(url,{id:id},function(data){
                        /*刷新数据列表*/
                         if(data.info){
                            $.notice({
                                content: data.info
                             });
                         }
                        t.search();
                        d.destroy();
                    });
                });
                this.$popup.find(".js-confirm-n").click(function(){
                  d.destroy();
                });
              }
            });
            d.open(true);
        },
        refund:function(t){
            var id = $(this).data("id"),
                company = $(this).data("company"),
                title = "退款";
            var v = new Refund({
                id:id,
                company:company,
                title:title,
                url:ST.ACTION.advanceRefund
            });
            v.on("close",function(){
                t.search();
            })
        },
        deduct:function(t){
            var id = $(this).data("id"),
                company = $(this).data("company"),
                title = "抵款";
            var v = new Refund({
                id:id,
                company:company,
                title:title,
                url:ST.ACTION.advanceDeduct
            });
            v.on("close",function(){
                t.search();
            });
        }

    });
    return function (options) {
        return new View(options);
    }
});