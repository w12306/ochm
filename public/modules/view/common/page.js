define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/common/page.html");
    var M = Base.Model.extend({
        defaults: {
            "total":0,
            "perpage":20,
            "curpage":1,
            "totalpage":0,
            "prevpage":0,
            "nextpage":0,
            "nextfivepage":0
        },
        initialize: function(data) {
            var t=this,total,perpage,totalpage,curpage,prevpage,nextpage,nextfivepage;

            total = parseInt(t.get("total"));
            t.set("total",total);

            perpage = parseInt(t.get("perpage"));
            t.set("perpage",perpage);

            totalpage = total/perpage;
            t.set("totalpage",Math.ceil(totalpage));

            curpage = parseInt(t.get("curpage"));
            t.set("curpage",curpage);

            if(curpage-1<1){
                prevpage = 0
            }else{
                prevpage = curpage - 1;
            }
            t.set("prevpage",Math.ceil(prevpage));

            if(curpage+1>totalpage){
                nextpage = 0;
            }else{
                nextpage = curpage+1;
            }
            t.set("nextpage",Math.ceil(nextpage));

            if(curpage+5>totalpage){
                nextfivepage = 0;
            }else{
                nextfivepage = curpage + 5;
            }
            t.set("nextfivepage",Math.ceil(nextfivepage));
        },
        getCurPage:function(){
            return this.get("curpage");
        },
        parse:function(res){
            return res;
        }
    });

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t=this;

            t.model = new M(pars.data);
            
            t.render();
            t.afterRender();
            
        },
        render:function(){
            var t=this,
                data=t.model.toJSON();
            t.$el.show().html(_.template(t.template,data));
        },
        afterRender: function(){
            var t=this;
            $(".js-page").click(function(){
                t.goPage.call(this,t);
            });
        },
        goPage:function(t){
            var p = $(this).data("p");
            t.model.set("curpage",p);
            t.trigger("paging",p);
            $("body").scrollTop(0);
        }
    });
    return function (options) {
        return new View(options);
    }
});