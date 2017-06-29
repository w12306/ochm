define(function (require) {

    var Base = require("base");
    //var tpl = require("tpl/tool/data.html");

    //var M = require("model/tool/data");
    var addDataView = require("view/tool/adddata");
    var com = require("lang/common");

    require("ui/selectbox");
    require("ui/combobox");
    require("ui/datepicker");
    require("ui/validator");
    require("ui/dialog");

    var View = Base.View.extend({
        initialize: function (pars) {
            var t = this;
            t.afterRender();
        },
        render:function(){
        },
        afterRender: function () {
            var t=this,
                type = $("#type-input").data("type"),
                typename = $("#type-input").data("type-name");

            $(".js-add-data-type").click(function(){
                var v = addDataView({type:type,typename:typename});
                v.on("success",function(){
                    window.location.reload();
                });
            })

            $(".js-edit-data-type").click(function(){
                var id=$(this).attr("data-id"),
                    name=$(this).data("name"),
                    detail=$(this).data("detail"),
                    value=$(this).data("value");

                var v = addDataView({id:id,type:type,typename:typename,name:name,detail:detail,value:value});
                v.on("success",function(){
                    window.location.reload();
                });
            })


            /*执行小组*/
            var errFn = function(){
                $.notice({ state: 'error',content: "获取部门信息失败" });
            }
            $(".js-add-team-type").click(function(){
                $.ajax({
                    url:ST.ACTION.getdep,
                    type:"post",
                    data:{},
                    dataType:"json",
                    success:function(d){
                        if(d.status=="success"||d.status==200){
                            var dep = d.data;
                            var v = addDataView({view:"team",type:type,typename:typename,dep:dep});
                            v.on("success",function(){
                                window.location.reload();
                            });
                        }else{
                            errFn();
                        }
                    },
                    error:errFn
                });
            })
            $(".js-edit-team-type").click(function(){
                var id=$(this).attr("data-id"),
                    name=$(this).data("name"),
                    detail=$(this).data("detail"),
                    value=$(this).data("value"),
                    btype=$(this).data("btype");
                    
                $.ajax({
                    url:ST.ACTION.getdep,
                    type:"post",
                    data:{id:id},
                    dataType:"json",
                    success:function(d){
                        if(d.status=="success"||d.status==200){
                            var dep = d.data;
                            var v = addDataView({view:"team",id:id,type:type,typename:typename,name:name,detail:detail,value:value,dep:dep,btype:btype});
                            v.on("success",function(){
                                window.location.reload();
                            });
                        }else{
                            errFn();
                        }
                    },
                    error:errFn
                });
            })


            /*部门*/
            $(".js-add-dep-type").click(function(){
                var contract = "";
                var v = addDataView({view:"dep",type:type,typename:typename,contract:contract});
                v.on("success",function(){
                    window.location.reload();
                });
            })
            $(".js-edit-dep-type").click(function(){
                var id=$(this).attr("data-id"),
                    name=$(this).data("name"),
                    detail=$(this).data("detail"),
                    value=$(this).data("value"),
                    contract = $(this).data("contract");
                var v = addDataView({view:"dep",id:id,type:type,typename:typename,name:name,detail:detail,value:value,contract:contract});
                v.on("success",function(){
                    window.location.reload();
                });
            });

            /*广告位*/
            $(".js-add-ad-type").click(function(){
                var v = addDataView({view:"ad",type:type,typename:typename});
                v.on("success",function(){
                    window.location.reload();
                });
            })
            $(".js-edit-ad-type").click(function(){
                var id=$(this).attr("data-id"),
                    obj={};

                $.ajax({
                    url:ST.ACTION.adPosDetail,
                    type:"post",
                    data:{id:id},
                    dataType:"json",
                    success:function(d){
                        if( (d.status=="success"||d.status==200)&&d.data ){
                            obj = d.data;
                            var v = addDataView({view:"ad",id:id,type:type,typename:typename,detail:obj});
                            v.on("success",function(){
                                window.location.reload();
                            });
                        }else{
                            $.notice({ state: 'error',content: "获取广告位信息失败" });
                        }
                    },
                    error:function(){
                        $.notice({ state: 'error',content: "获取广告位信息失败" });
                    }
                });
                
            });
            $(".js-delete-ad").click(function(){
                var id = $(this).data("id");
                $.dialog.destroy('debt-del-confirm-pop');
                var d = $.dialog('debt-del-confirm-pop', {
                    title: '删除广告',
                    content: "<div>是否确认删除？</div><div class='mt10 center'><a class='btn debt-del-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 debt-del-confirm-y' href='javascript:;'>确认</a></div>"
                });
                d.open(true);
                $(".debt-del-confirm-n").click(function() {
                    d.destroy();
                })
                $(".debt-del-confirm-y").click(function() {
                    var url = ST.ACTION.deleteAdPos;
                    com.sendRq(url, {
                        id: id
                    });
                })
            })


            $(".js-data-status").click(function(){
                var id=$(this).data("id"),
                status = $(this).data("status");
                com.sendRq(ST.ACTION.dataStatusChange,{id:id,status:status});
            })
        }
    });
    return function (options) {
        return new View(options);
    }
});