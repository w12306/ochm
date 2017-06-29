define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/invoice/list.html");
    var M = require("model/common/table");
    var com = require("lang/common");
    var Page = require("view/common/page.js");
    require("ui/selectbox");
    require("ui/datepicker");
    require("ui/dialog");
    require("ui/notice");
    require("ui/validator");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            
            t.listAction = pars.listAction;
            t.deleteAction = pars.deleteAction;

            com.com();
            if(!t.listAction){
                t.bindDelete();
                return;
            }

            t.search();
            $("#js-search-btn").click(function(){
                if($(".js-tb-name input[type='checkbox']").length>0){
                    if($(".js-tb-name input[type='checkbox']:checked").length>10||$(".js-tb-name input[type='checkbox']:checked").length<10){
                        $.notice({
                            content: '定制字段必选且最多只能选择10项'
                        });
                        return;
                    }else{
                        t.search.call(t);
                    }
                }else{
                    t.search.call(t);
                }
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
                t.model = new M({url:t.listAction,data:searchData});
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
                t.search();
            })
            
            $(".js-data-delete").click(function(){
                t.deleteData.call(this,t)
            });
        },
        bindDelete:function(){
            var t=this; 
            $(".js-data-delete").click(function(){
                t.deleteData.call(this,t)
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
                  var url = t.deleteAction;
                  com.sendRq(url,{id:id},function(data){
                     /*刷新数据列表*/
                     if(data.info){
                        $.notice({
                            content: data.info
                         });
                     }

                    if(t.listAction){
                        t.search();
                    }else{
                        window.location.reload();
                    }

                     d.destroy();
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