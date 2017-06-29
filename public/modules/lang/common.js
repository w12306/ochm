/*业务管理公用*/
define(function(require) {
    require("ui/selectbox");
    require("ui/datepicker");
    require("ui/dialog");
    require("ui/notice");
    require("ui/validator");
    var commonFn = {
        com: function() {
            var t = this;
            t.selectInit();
            /*起始时间--结束时间*/
            initCal("#_calender1", "#_calender2");
            initCal("#_calender3", "#_calender4");
            initCal("#_month1", "#_month2", "month");

            function initCal(start, end, type) {
                var $start = $(start),
                    $end = $(end);
                $start.datepicker({
                    toSelect: type || "date",
                    maxDate: $end.val() || new Date().getFullYear() + '-12-31 09:09:09',
                    onChanged: function(e, text, date, oldDate) {
                        $end.datepicker('setMin', type?text:date).datepicker('closePopup');
                    }
                });
                $end.datepicker({
                    toSelect: type || "date",
                    minDate: $start.val() || new Date().getFullYear() + '-01-01 00:00:00',
                    onChanged: function(e, text, date, oldDate) {
                        $start.datepicker('setMax', type?text:date).datepicker('closePopup');
                    }
                });
            }

            /*单个日期选择*/
            $('.js-calender').datepicker();
            /*表单验证*/
            t.formValidate();
        },
        selectInit: function() {
            var t=this;
            /* select下拉框单选+查询 */
            $('.js-selectbox').selectbox({
                searchable: true,
                localSearch: true
            });
            
            t.selectRelate();
            
            /*初始化多选框*/
            $('.js-selectbox[multiple="multiple"]').each(function() {
                if ($(this).siblings(".js-selectbox-multiple-txt").length < 1) return false;
                var ids = $(this).siblings(".js-selectbox-multiple-txt").val();
                if (ids == "") return;
                ids = ids.split(",");
                for (var i = 0; i < ids.length; i++) {
                    $(this).selectbox('selectById', ids[i]);
                }
            });
            /*多选框选中赋值*/
            $('.js-selectbox[multiple="multiple"]').on('changed.bee.selectbox', function(e, item, toAdd, toRemove) {
                var ids = [];
                for (var i = 0; i < item.length; i++) {
                    ids.push(item[i].id)
                }
                $(this).siblings(".js-selectbox-multiple-txt").val(ids);
            });
        },
        selectRelate:function(){
            /*select联动*/
            $(".js-relate-selectbox").on('selected.bee.selectbox', function(e, item, toAdd, toRemove) {
                var id = item.id,
                    url = $(this).attr("data-relate-action").split(";"),
                    rid = $(this).attr("data-relate-id").split(";");
                for (var i = 0; i < rid.length && i < url.length; i++) {
                    var suc = (function(id) {
                        return function(d) {
                            var relate = $("#" + id);
                            var name = relate.attr("name");
                            var rules = relate.attr("data-rules");
                            var obj = relate.parent();
                            var tpl = '<select name="' + name + '" placeholder="请选择"  class="js-selectbox" data-rules="' + rules + '" id="' + id + '">' + '<%for(var i=0;i<data.length;i++){%>' + '<option value="<%=data[i].key%>" <%=(data[i].s==1?"selected":"")%> ><%=data[i].value%></option>' + '<%}%></select>';
                            obj.html(_.template(tpl, {
                                data: d.data
                            }))
                            $("#" + id).selectbox({
                                searchable: true,
                                localSearch: true
                            });
                        }
                    })(rid[i]);
                    if (id == "" || id == "0") {
                        suc({
                            data: []
                        })
                    } else {
                        $.ajax({
                            method: "post",
                            url: url[i] + "/" + id,
                            dataType: "json"
                        }).done(suc).fail(function() {});
                    }
                }
            });
        },
        formValidate: function(suc) {
            /*表单验证*/
            if ($("#js-form-validate").length > 0) {
                var validateLoading;
                $("#js-form-validate").validator({
                    onFormSubmit: function() {
                        validateLoading = $.notice({
                            state: 'loading',
                            content: '请稍等...',
                            autoClose: 0,
                            modal: true
                        });
                    },
                    ajaxSubmitOption: {
                        success: function(d) {
                            validateLoading.destroy();
                            if (suc) {
                                suc(d);
                            } else {
                                if (d.info) {
                                    if (d.status == "success") {
                                        $.notice({
                                            state: "success",
                                            content: d.info
                                        });
                                    } else {
                                        $.notice({
                                            state: "error",
                                            content: d.info
                                        });
                                    }
                                }
                                if (d.data.url) {
                                    setTimeout(function() {
                                        window.location.href = d.data.url;
                                    }, 2000)
                                }
                            }
                            //console.log("success",d)
                        },
                        error: function(e) {
                            validateLoading.destroy();
                            $.notice({
                                state: 'error',
                                content: "操作失败"
                            });
                            console.log("form error:", e)
                        }
                    }
                });
            }
        },
        sendRq: function(url, data, suc) {
            var loading = $.notice({
                state: 'loading',
                content: '请稍等...',
                autoClose: 0
            });
            $.ajax({
                url: url,
                type: "post",
                data: data,
                dataType: "json",
                success: function(d) {
                    loading.destroy();
                    if (suc) {
                        suc(d);
                    } else {
                        if (d.info) {
                            $.notice({
                                state: d.status,
                                content: d.info
                            });
                        }
                        if (d.status == "success") {
                            setTimeout(function() {
                                window.location.reload();
                            }, 2000);
                        }
                    }
                },
                error: function(e) {
                    loading.destroy();
                    $.notice({
                        state: 'error',
                        content: "操作失败"
                    });
                    console.log(e);
                }
            })
        },
        business_del_pop: function() {
            var t = this;
            /*业务删除*/
            $(".js-bus-del").click(function() {
                var id = $(this).attr("data-id");
                $.dialog.destroy('check-delete-confirm-pop');
                var d = $.dialog('check-delete-confirm-pop', {
                    title: '删除业务',
                    content: "<div>一旦删除业务，与该业务关联的全部数据将一并删除，请确认！</div><div class='mt10 center'><a class='btn check-delete-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 check-delete-confirm-y' href='javascript:;'>确认</a></div>"
                });
                d.open(true);
                $(".check-delete-confirm-y").click(function() {
                    var url = ST.ACTION.businessDelete + "/" + id;
                    t.sendRq(url, {});
                })
                $(".check-delete-confirm-n").click(function() {
                    d.destroy();
                })
            })
        },
        execution_operate:function(){
            var t = this;
            /*执行单删除*/
            $(".js-execution-del").click(function() {
                var id = $(this).attr("data-id");
                $.dialog.destroy('check-delete-confirm-pop');
                var d = $.dialog('check-delete-confirm-pop', {
                    title: '删除执行单',
                    content: "<div>确认删除执行单么?</div><div class='mt10 center'><a class='btn check-delete-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 check-delete-confirm-y' href='javascript:;'>确认</a></div>"
                });
                d.open(true);
                $(".check-delete-confirm-y").click(function() {
                    var url = ST.ACTION.execuntionDelete + "/" + id;
                    t.sendRq(url, {});
                })
                $(".check-delete-confirm-n").click(function() {
                    d.destroy();
                })
            });
            /*执行单下单*/
            $(".js-execution-order").click(function() {
                var id = $(this).attr("data-id");
                var url = ST.ACTION.execuntionOrder + "/" + id;
                t.sendRq(url, {});
            });
        },
        addamount_pop: function() {
            require.async("view/business/addamount", function(amountView) {
                $(".js-add-amount").click(function() {
                    var obj = {
                        id: $(this).data("id"),
                        title: $(this).data("title").split(":")[0],
                        active_amount: $(this).data("active_amount"),
                        // delivery_amount: $(this).data("delivery_amount"),
                    }
                    var v = new amountView(obj);
                    v.on("success", function() {
                        window.location.reload();
                    })
                })
            });
        },
        contract: function() {
            var t = this;
            $(".js-contract-filing").click(function() {
                var id = $(this).attr("data-id"),
                    url = ST.ACTION.contractFiling,
                    data = {
                        id: id
                    };
                var teamTpl = require("tpl/dialog/affirmDialog.html");
                var d = $.dialog("sendRq", {
                    title: "请确认操作：",
                    content: _.template(teamTpl, {
                        content: "该合同已存档，将执行归档操作!"
                    })
                });
                d.open(true);
                $(".js-close").click(function() {
                    $.dialog.destroy("sendRq");
                });
                $(".js-submit").click(function() {
                    $.dialog.destroy("sendRq");
                    t.sendRq(url, data);
                })
            })
        },
        debt: function() {
            var t = this;
            require.async("view/baddebt/adddebt.js", function(addbedtView) {
                /*新增、修改坏账*/
                $(".js-add-debt").click(function() {
                    var busid = $(this).data("busid");
                    var deliveryid = $(this).data("deliveryid");
                    var month = $(this).data("month");
                    var team = $(this).data("team");
                    var v = addbedtView({
                        busid: busid,
                        deliveryid: deliveryid,
                        month: month,
                        team: team
                    })
                    v.on("success", function() {
                        window.location.reload();
                    });
                });
                $(".js-edit-debt").click(function() {
                    var busid = $(this).data("busid");
                    var id = $(this).data("id");
                    var amount = $(this).data("amount");
                    var month = $(this).data("month");
                    var team = $(this).data("team");
                    var v = addbedtView({
                        busid: busid,
                        id: id,
                        amount: amount,
                        month: month,
                        team: team
                    })
                    v.on("success", function() {
                        window.location.reload();
                    });
                });
            });
            /*删除坏账*/
            $(".js-del-debt").click(function() {
                var id = $(this).data("id");
                $.dialog.destroy('debt-del-confirm-pop');
                var d = $.dialog('debt-del-confirm-pop', {
                    title: '删除坏账',
                    content: "<div>是否确认删除？</div><div class='mt10 center'><a class='btn debt-del-confirm-n' href='javascript:;'>取消</a><a class='btn btn-blue ml20 debt-del-confirm-y' href='javascript:;'>确认</a></div>"
                });
                d.open(true);
                $(".debt-del-confirm-n").click(function() {
                    d.destroy();
                })
                $(".debt-del-confirm-y").click(function() {
                    var url = ST.ACTION.deleteDebt;
                    t.sendRq(url, {
                        id: id
                    });
                })
            })
        },
        role: function() {
            var t = this;
            $('.js-toggleall').click(function() {
                var check = $(this).prop("checked");
                var $checkbox = $(this).parents(".js-toggle-box").find(".js-toggle-con [type='checkbox']");
                $checkbox.prop("checked", check)
            });
            $(".js-user-status").click(function() {
                var id = $(this).data("id"),
                    status = $(this).data("status"),
                    url = ST.ACTION.userStatusChange;
                t.sendRq(url, {
                    id: id,
                    status: status
                });
            })
            $(".js-role-change-status").click(function() {
                var id = $(this).data("id"),
                    status = $(this).data("status"),
                    url = ST.ACTION.roleStatusChange;
                t.sendRq(url, {
                    id: id,
                    status: status
                });
            })
            require.async("view/tool/adduser", function(addUserView) {
                /*新增、修改坏账*/
                $(".js-user-add").click(function() {
                    var v = addUserView({})
                    v.on("success", function() {
                        window.location.reload();
                    });
                });
                $(".js-user-edit").click(function() {
                    var id = $(this).data("id");
                    var amount = $(this).data("amount");
                    var v = addUserView({
                        id: id
                    })
                    v.on("success", function() {
                        window.location.reload();
                    });
                });
            });
        },
        setComTableWidth: function(el) {
            el.find("ol.datatable-bd").each(function() {
                var ol = $(this);
                /*设置滚动宽度*/
                var lw = 0,
                    pw = ol.find(".js-scroll-container").parent().width();
                ol.find(".js-scroll-container").siblings("li.datatable-col").each(function() {
                    lw = lw + $(this).width() + 1;
                });
                ol.find(".js-scroll-container").width(pw - lw);
                ol.find(".js-scroll-box").each(function() {
                    var totalw = 0;
                    $(this).find(".datatable-col").each(function() {
                        totalw = totalw + $(this).width();
                    });
                    $(this).width(totalw + 1);
                });
                /*设置总宽度*/
                var ow = 0;
                ol.find("li.datatable-col").each(function() {
                    ow = ow + $(this).width();
                })
                ol.width(ow + 1);
            })
        },
        exportExcel: function(obj) {
            var id = obj.id || "",
                searchId = obj.searchFormId || "",
                pars, el, url;
            el = $("#" + id);
            url = el.data("url");
            
            el.click(function() {
                pars = $("#" + searchId).serialize();
                window.open(url + "?" + pars);
            })
        },
        operate: function() {
            var t = this;
            $(".js-operate").on('selected.bee.selectbox', function(e, item, toAdd, toRemove) {
                relateChange($(this));
            })

            function relateChange(obj, tp) {
                var id = obj.val(),
                    rid = obj.data("relate"),
                    url = obj.data("url"),
                    el = $("#" + rid).find(".js-con"),
                    val = "",
                    stpl, itpl;
                if (tp == "init") {
                    val = obj.data("relate-value") || "";
                }
                stpl = '<select name="" class="js-selectbox" placeholder="支持多选" multiple="multiple">' + '<%for(var i=0;i<data.length;i++){%>' + '<option value="<%=data[i].key%>"><%=data[i].value%></option>' + '<%}%>' + '</select>' + '<input name="<%=name%>" type="text" class="hidden js-selectbox-multiple-txt" hidden value="' + val + '">';
                itpl = '<input name="<%=name%>" type="text" class="input input-small" value="' + val + '">'
                t.sendRq(url, {
                    id: id
                }, function(d) {
                    if (d.data.type == "select") {
                        el.html(_.template(stpl, d.data));
                        t.selectInit();
                    } else {
                        el.html(_.template(itpl, d.data));
                    }
                    $("#" + rid).find(".js-name").html(d.data.column);
                })
            }
            relateChange($(".js-operate"), "init");
        },
        selectAdd: function(par) {
            var t = this;
            if (!par || !par.id) return;
            var tpl = '<select class="js-selectbox" name="<%=name%>" rules="<%=rules%>">' + '<%for(var i=0;i<data.length;i++){%>' + '<option value="<%=data[i].key%>"><%=data[i].value%></option>' + '<%}%>' + '</select>';
            var $el = $('#' + par.id),
                $btn = $("#" + par.btn);

            function loadSelect(id) {
                $.ajax({
                    url: par.listUrl,
                    type: "post",
                    dataType: "json",
                    success: function(d) {
                        $el.html(_.template(tpl, {
                            data: d.data,
                            name: par.name,
                            rules: par.rules
                        }))
                        if (id) {
                            $el.find("option[value='" + id + "']").prop("selected", true);
                        }
                        t.selectInit();
                        t.formValidate();
                    }
                });
            }
            loadSelect();
            $btn.click(function() {
                $.dialog.destroy('select-add-pop');
                var dialog = $.dialog('select-add-pop', {
                    title: '新增',
                    content: "<input type='text' class='input-small input js-input' name='name'/><input class='btn btn-blue ml10 js-d-submit' type='button' value='确定'>",
                });
                dialog.open(true);
                $(".js-d-submit").click(function() {
                    var val = $(".js-input").val();
                    if (val) {
                        $.ajax({
                            url: par.addUrl,
                            data: {
                                value: val,
                                type: par.type
                            },
                            type: "post",
                            dataType: "json",
                            success: function(d) {
                                if (d.status == "success") {
                                    $.notice({
                                        content: "添加成功"
                                    });
                                    var id = d.data.id || "";
                                    dialog.destroy();
                                    loadSelect(id);
                                }
                            }
                        });
                    } else {
                        $.notice({
                            content: "输入框值不能为空"
                        });
                    }
                })
            })
        }
    }
    return commonFn;
});