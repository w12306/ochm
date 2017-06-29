define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/common/table1.html");
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

            com.com();
            t.search();
            $("#js-search-btn").click(function(){
                if($(".js-tb-name input[type='checkbox']").length>0){
                    if($(".js-tb-name input[type='checkbox']:checked").length>10){
                        $.notice({
                            content: '最多只能选择10项'
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
        }
    });
    return function (options) {
        return new View(options);
    }
});