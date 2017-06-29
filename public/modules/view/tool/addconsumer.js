define(function (require) {

    var Base = require("base");
    var tpl = require("tpl/tool/addconsumer.html");
    var userTpl = require("tpl/tool/getUser.html");
    var ctpl = require("tpl/tool/choosepartner.html");
    var M = require("model/tool/addconsumer");
    var apdView = require("view/tool/addpartner");
    var com = require("lang/common");

    require("ui/selectbox");
    require("ui/combobox");
    require("ui/datepicker");
    require("ui/notice");
    require("ui/validator");
    require("ui/dialog");

    var cdtpl="<%for(var i=0;i<data.length;i++){%>"+
                    "<div><label><input type=\"checkbox\" name='partner_id[]' value='<%=data[i].id%>' class='mr10 partner_id' checked/><%=data[i].name%></label></div>"+
                "<%}%>";


    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.model = new M(pars.model);

            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON();

            t.$el.show().html(_.template(t.template, {data:data.data}));
        },
        afterRender: function () {
            var t=this;
            com.com();
            $("#js-consumer-form-validate").validator({
                onBefore:function(){
                    if($("#choosed-box input").length<1){
                        $.notice({content:"请选择合作方"});
                        return false;
                    }
                },
                onFormSubmit:function(){
                    validateLoading = $.notice({state: 'loading', content: '请稍等...',autoClose:0,modal: true});
                },
                ajaxSubmitOption:{
                    success:function(d){
                        validateLoading.destroy();

                        if(d.info){
                            if(d.status=="success"){
                                $.notice({state:"success",content: d.info});
                            }else{
                                $.notice({state:"error",content: d.info});
                            }

                        }
                        if(d.data.url){
                            setTimeout(function(){
                                window.location.href = d.data.url;
                            },2000)
                        }

                    },
                    error:function(e){
                        validateLoading.destroy();
                        $.notice({state: 'error', content: "操作失败"});
                        console.log("form error:",e)
                    }
                }
            });
            $("#js-add-partner").click(function(){
                var v=apdView();
                v.on("success",function(d){
                    var data = [];
                    data[0]=d;
                    $("#choosed-box").append(_.template(cdtpl,{data:data}));
                    v.closePop();
                })
            });

            $("#js-choose-partner").click(function(){  
                t.showPartner();
            });

            $("input[data-group=deparment]").click(function(){
                t.renderTeams();
            })

            this.renderTeams()
        },
        renderTeams(){
            var params = [];
			var company_id=0;
			if($("#company_id").val()!=""){
				company_id=$("#company_id").val();
			}
			
            $("input[data-group=deparment]:checked").each(function(index,ele){
                params.push($(ele).attr('value'));
            })

            if(params.length>0){
                $.ajax({
                    url:ST.debug?"/resource/jsData/getUser.json":ST.ACTION.getUsers,
                    type:'post',
                    data:{teams:params,id:company_id},
                    dataType:'json',
                    success:function(res){
                        if(res.status=="success"){
                            $("#user-choosed-box").html(_.template(userTpl,{data:res.data}));    
                        }else{
                            $.notice({state: 'error', content: res.info});
                        }
                    },
                    error:function(e){
                        $.notice({state: 'error', content: "操作失败"});
                        console.log("form error:",e)
                    }
                })
            }
            

        },
        showPartner:function(){
            $.dialog.destroy('choose-partner-pop');
            $.ajax({
                url:ST.ACTION.addProductList,
                dataType:"json",
                success:function(d){
                    var d = $.dialog('choose-partner-pop', {
                        title: '添加合作方',
                        content: _.template(ctpl, {data:d.data})
                    });
                    d.open(true);

                    $(".choose-partner-confirm-n").click(function(){
                        $.dialog.destroy('choose-partner-pop');
                    })
                    $(".choose-partner-confirm-y").click(function(){
                        var data=[];
                        $("input[name='partner-check']:checked").each(function(){
                            var obj={};
                            obj.id = $(this).data("id");
                            obj.name=$(this).data("name");
                            data.push(obj);
                        })
                        $("#choosed-box").html(_.template(cdtpl,{data:data}));
                        $.dialog.destroy('choose-partner-pop');
                    })
                    $("input[name='partner-checkall']").click(function(){
                        var flag = $(this).prop("checked");
                        $("input[name='partner-check']").prop("checked",flag);
                    });
                },error:function(){}
            });
        }
    });
    return function (options) {
        return new View(options);
    }
});