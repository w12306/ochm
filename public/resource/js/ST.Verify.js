ST.Verify = {
    //依赖列表 仅支持一层包含
    require: ["ST.Regs"],
    init: function () {
        var t = this;
        t.config = {
            ts: ['input', 'select', 'textarea']
        };
        ST.getJsList(t.require, function () {
            t.initPage();
        }, function () {
            alert(ST.LRes.RequireFail);
        });
        //需检测依赖,后面补充
        return t;
    },
    //获取页面所有需验证表单添加相关属性,事件
    //f 表单 els 验证元素 erroAray 错误信息
    initPage: function (a) {
        var t = this;
        if (!a) t.forms = [];
        var s = a ? "#" + a + " form" : "form";
        $(s).each(function () {
            $(this).attr('stverify') && t.addFormInfo(this);
        });
        $.each(t.forms, function (i, v) {
            t.initForm(i);
        });
    },
    //@idx 表单下标
    initForm: function (idx) {
        var t = this, fs = t.forms;
        t.initVelms(idx);
        fs[idx].f.onsubmit = function () {
            if ($(this).attr('dynamicForm')) {
                t.initVelms(idx);
            }
            if (t.subForm(this)) {
                t.checkIEHolder(this);
                this.submit();
            }
            return false;
        }
    },
    initVelms: function (idx) {
        var t = this, fs = t.forms, ts = t.config.ts, cf = idx >= 0 ? fs[idx] : fs.getJson('f', idx);
        cf.els = [];
        cf.erroArray = [];
        cf.succArray = [];
        var isAppend = $(cf.f).attr('erroappend');
        $(cf.f).find("*[name]").each(function (i, v) {
            var a;
            if (a = $(this), a.attr("opt") && t.inArray(ts, this.tagName)) {
                if (a.attr("opt").contains("group")) cf.group.push(a);
                cf.els.push($(this));
                cf.succArray.push(false);
                var verifySucc = function () {
                    if ($(cf.f).attr('verifySucc')) {
                        for (var m = 0; m < cf.succArray.length; m++) {
                            if (!cf.succArray[m]) {
                                if (cf.subBtn.length) $(cf.subBtn).attr("disabled", "disabled").toggleClass("disabled", true);
                                return;
                            }
                        }
                        t.hideAllErro(cf.f);
                        //验证成功后
                        $(cf.subBtn).removeAttr("disabled").toggleClass("disabled", false);
                        ST[$(cf.f).attr('verifySucc')] && ST[$(cf.f).attr('verifySucc')]();
                    }
                };
                //空内容提示
                if (isAppend && a.attr("etips")) {
                    t.showTips(a, a.attr("etips"), "info");
                }
                var tp = (a.attr('type') ||'').toUpperCase();
                var checkfix = function(flag,cond){
                  var o = $(cf.f).find('['+flag+'=' + a.attr(flag) + ']').filter(cond);
                  if (o.size() > 0) {
                    a.unbind('click.v').bind('click.v', function () {
                      cf.succArray[i] = t.check(cf.f, o, true);
                      verifySucc();
                    });
                  }
                };
                if(tp === 'RADIO'){
                    checkfix('name','[opt*=radio],[opt*=rdck]');
                }else if(tp === 'CHECKBOX'){
                    checkfix('group','[opt*=least],[opt*=partrq]');
                }else {
                    a.unbind('focus.v').bind('focus.v', function () {
                        a.toggleClass("highlight", true);
                        var icon=a.prev("i");
                        icon.toggleClass(icon.data("focus"),true);
                        if (a.attr("focusmsg")) {
                            t.showTips(a, a.attr("focusmsg"), "info");
                        }
                    });
                    a.unbind('blur.v').bind('blur.v', function () {
                        a.toggleClass("highlight", false);
                        var icon=a.prev("i");
                        icon.toggleClass(icon.data("focus"),false);
                        cf.succArray[i] = t.check(cf.f, this, true);
                        //解决compare通过后提示的问题
                        var o = $(cf.f).find('[compare][compare='+a.attr('id')+']');
                        if(o.length>0 && o.val()){
                          cf.succArray[i] = t.check(cf.f, o, true);
                        }
                        verifySucc();
                    });
                }
                //修正 ie placeholder属性，不支持密码域
                if ($.browser.msie) {
                    if (a.attr("placeholder")) {
                        if (a.val() == "") a.addClass("text-gray").val(a.attr("placeholder"));
                        a.unbind('focus.ie').bind('focus.ie',function () {
                            if (a.val().t() == a.attr("placeholder"))
                                a.val("").removeClass("text-gray");
                        }).unbind('blur.ie').bind('blur.ie', function () {
                                if (a.val().t() == "")
                                    a.val(a.attr("placeholder")).addClass("text-gray");
                            })
                    }
                }
            }
        });
    },
    //添加需要验证的表单   id
    addVform: function (id) {
        var t = this, f = document.getElementById(id)||id;
        if (t.forms.getJson("f", f)) return;
        t.addFormInfo(f);
        t.initForm(t.forms.length - 1);
    },
    //@f 表单对象
    subForm: function (f) {
        //内存中获取当前form中所有可验证元素
        var t = this, cf = t.getFormInfo(f), objs = cf.els, a, flag;
        cf.erroArray = [];
        a = $(f).attr('beforeSubFun');
        if (a && !ST.todo(a)) {
            return false;
        }
        //循环遍历验证所有验证元素
        for (var i = 0, l = objs.length; i < l; i++) {
            if (!t.check(f, objs[i]) && !$(f).attr('erroappend')) {
                return false;
            }
        }
        if (cf.erroArray.length > 0) {
            return false;
        } else {
            if (cf.isAjaxVerify>0) {
                ST.tipMsg(ST.LRes.isVerify, 1500);
                return false;
            }
            var fn = function () {
                a = $(f).attr('SubFun');
                if (a && !ST.todo(a)) {
                    return false;
                }
                if ($(f).attr("ajaxpost")) {
                    t.checkIEHolder(f);
                    ST.tipMsg(ST.LRes.IsSubmit, 0, !0);
                    //提交表单需解决提交placeholder值的问题
                    ST.postForm({
                        f: f,
                        succ: function (j) {
                            if (a = $(f).attr('afterSubFun')) {
                                ST.todo(a, j);
                            } else {
                                if (j.data.url) {
                                    window.setTimeout(function(){
                                        location.href = j.data.url;
                                    },1500);
                                } else {
                                    ST.reload();
                                }
                            }
                        },
                        error: function (e) {
                            if (a = $(f).attr('errorFun')) {
                                ST.todo(a, e);
                            }
                        }
                    });
                    return false;
                }
                return true;
            };
            if ($(f).attr('confirm')) {
                ST.confirm(
                    ST.JTE.fetch($(f).attr('conftmp') || 'form_confmsg_temp').getFilled({
                        msg: $(f).attr('confmsg') || ST.LRes.subAsk
                    }), ST.LRes.opTip, function () {
                        flag = fn();
                    }, function () {
                        return false;
                    }, 405, 215);
                return flag;
            } else {
                return fn();
            }
        }
    },
    //获取表单需要验证信息
    getFormInfo: function (f) {
        var t = this;
        for (var i in t.forms) {
            if (f == t.forms[i].f) return t.forms[i];
        }
    },
    //IE下占位符验证
    checkIEHolder: $.browser.msie ? function (f) {
        var t = this;
        var els = $(f).find(':input').filter('[name][placeholder]'), holder, val;
        els.each(function () {
            holder = $(this).attr('placeholder').trim();
            val = $(this).val().trim();
            if (holder && val && holder == val) {
                $(this).val('');
            }
        });
    } : function () {
    },
    //表单验证（不提交表单）
    checkForm: function (fid) {
        var t = this, f = document.getElementById(fid);
        if ($('#' + fid).attr('dynamicForm')) {
            t.initVelms(f);
        }
        var cf = t.getFormInfo(f), objs = cf.els;
        //内存中获取当前form中所有可验证元素
        cf.erroArray = [];
        a = $(f).attr('beforeSubFun');
        if (a && !ST.todo(a)) {
            return false;
        }
        //循环遍历验证所有验证元素
        for (var i = 0, l = objs.length; i < l; i++) {
            if (!t.check(f, objs[i]) && !$(f).attr('erroappend')) {
                return false;
            }
        }
        return !(cf.erroArray.length > 0);
    },
    //@f 指定的表单对象 o表单验证元素
    check: function (f, o, cancel) {
        var t = this, o = $(o), eno = -1, emsg = 0, key = o.val(), kl = key.l(), opts = o.attr("opt").t().split(" "), cf = t.getFormInfo(f), v1;
        //默认值
        if (o.attr("placeholder") && key == o.attr("placeholder")) {
            //return false;
            key = "";
        }
        for (var i = 0, l = opts.length; i < l; i++) {
            //内置基础常用验证 通常不依赖任何正则表达式
            switch (v1 = opts[i].toLowerCase()) {
                case"rq"://必填
                case"require":
                    if (!key) emsg = ST.LRes.Require;
                    break;
                case"nrq"://非必填
                case"notrequire":
                    //修复least验证提示，待优化
                    if (o.attr('group') && o.filter('[opt*=least],[opt*=partrq]').length==0) {
                      var groupEls = $(f).find('[group][group=' + o.attr('group') + ']');
                      groupEls.filter('[opt*=least],[opt*=partrq]').trigger('blur');
                    }

                    if (key == '') {
                        t.msg = 0;
                        if (key == "" && o.attr("etips")) {
                            t.showTips(o, o.attr("etips"), "info");
                        } else {
                            t.hideErro(o);
                        }
                        return true;
                    }
                    break;
                case"ml"://字数范围
                case"maxlength":
                    var byteml = o.attr('byteml');
                    kl = byteml ? key.l() : key.length;
                    var ml = o.attr("ml").split("-");
                    if (kl < ml[0] || kl > ml[1]) {
                        emsg = ST.LRes.FormErrorMaxLength.f(ml[0], ml[1]);
                    }
                    break;
                case"range"://数值范围（包括边界值）
                    if (!(ST.Regs.integer.reg.test(key) || ST.Regs.decimal.reg.test(key))) {
                        emsg = ST.LRes.FormErrorNumber;
                        break;
                    }
                    key = Number(key);
                    var rl = o.attr("range").split("-");
                    if (rl.length < 2 || rl[0] > rl[1]) break;
                    if (key < rl[0] || key > rl[1]) {
                        emsg = ST.LRes.FormErrorRange.f(rl[0], rl[1]);
                        break;
                    }
                    break;
                case"compare"://比较两个值
                    var cp = $("#" + o.attr("compare")), cv = o.attr("compval"), ce;
                    var op = o.attr('operator') || '==';
                    if (!cp.length) {
                        if (!cv) break;
                        ce = cv.t();
                    } else {
                        ce = cp.val().t();
                    }
                    switch (op) {
                        case '!=':
                            if (key == ce) {
                                emsg = ST.LRes.Equal + ce;
                            }
                            break;
                        case '>':
                            if (key <= ce) {
                                emsg = ST.LRes.LessOrEqual + ce;
                            }
                            break;
                        case '>=':
                            if (key < ce) {
                                emsg = ST.LRes.Less + ce;
                            }
                            break;
                        case '<':
                            if (key >= ce) {
                                emsg = ST.LRes.GreaterOrEqual + ce;
                            }
                            break;
                        case '<=':
                            if (key > ce) {
                                emsg = ST.LRes.Greater + ce;
                            }
                            break;
                        default:
                            if (key != ce) {
                                emsg = ST.LRes.NotEqual + ce;
                            }
                    }
                    if(!emsg && cp.val()) {
                        if (cp.attr("succmsg")) {
                            t.showTips(o, o.attr("succmsg"), "success");
                        } else if($(f).attr("showSucc")){
                            t.showTips(o, "", "success");
                        }else{
                            t.hideErro(o);
                        }
                    }
                    break;
                case"tag"://标签
                    var tags = key.replace(/[\s，、,]/g, ","),n,l;
                    tags = tags.split(",");
                    n = parseInt(o.attr('tagnum')||5,10);
                    l = parseInt(o.attr('taglen')||16,10);
                    if (tags.length > n) {
                        emsg = ST.LRes.FormErrorTagNumber.f(n);
                        break;
                    }
                    for (var j = 0; j < tags.length; j++) {
                        if (tags[j].l() > l) {
                            emsg = ST.LRes.FormErrorTagLength.f(l);
                            break;
                        }
                    }
                    break;
                case"checked"://选中
                    if (!o.checked) {
                        emsg = ST.LRes.FormErrorChecked;
                    }
                    break;
                case"rdck"://old version fixing
                case"radio"://单选
                    var rdbtn = $(f).find('[name=' + o.attr('name') + ']'), flag = false;
                    for (var j = 0; j < rdbtn.length; j++) {
                        if (rdbtn[j].checked) {
                            flag = true;
                            break;
                        }
                    }
                    if (!flag) {
                        emsg = ST.LRes.FormErrorRadio;
                    }
                    break;
                //待修改
                case"partrq"://old version fixing
                case "least"://至少N项必填/选
                    var num = o.attr('num'), gn = o.attr('group'), tp = o.attr('type').toUpperCase();
                    var idx = 0, ckey = '';
                    if (!gn) break;
                    if (!num) num = 1;
                    var os = $(cf.f).find(':input[group][group=' + gn + ']');
                    os.each(function () {
                        ckey = tp == 'CHECKBOX' ? $(this).attr('checked') : $(this).val();
                        if (ckey) idx++;
                    });
                    if (idx < num) emsg = ST.LRes.FormErrorLeast.f(num);
                    break;
                case "regexp"://自定义正则表达式（regexp属性中配置正则表达式）
                    if (!o.attr('regexp')) return;
                    if (!new RegExp(o.attr('regexp'), "i").test(key)) {
                        emsg = ST.LRes.VerifyFail;
                    }
                    break;
                case 'match'://自定义正则表达式（修正regexp不支持修饰符配置的bug）
                    var exp = o.attr('match');
                    if (!exp) return;
                    try{
                        exp = (new Function("return " + exp))();
                    }catch(e){

                    }
                    if ($.type(exp) == 'regexp' && !exp.test(key)) {
                        emsg = ST.LRes.VerifyFail;
                    }
                    break;
                //用于远程异步验证
                case"ajaxverify":
                    if (key.length) {
                        if (key != o.data('lastval.v')) {
                            cf.isAjaxVerify++;

                            var data = {};
                            data[o.attr('name')] = key;
                            o.data('lastval.v',key); //记录上次值
                            ST.getJSON(o.attr("ajaxVerify"), data, function (j) {
                                cf.isAjaxVerify--;
                                o.data('result.v',true); //记录验证结果
                                o.data('emsg.v','');//清空错误信息
                                if (o.attr("succmsg")) {
                                    t.showTips(o, o.attr("succmsg"), "success");
                                } else if($(f).attr("showSucc")){
                                    t.showTips(o, "", "success");
                                }else{
                                    t.hideErro(o);
                                }
                            }, function (j) {
                                cf.isAjaxVerify--;

                                var emsg = j.message || j.info || ST.LRes.VerifyFail;
                                o.data('result.v',false); //记录验证结果
                                o.data('emsg.v',emsg);//记录错误信息
                                cf.erroArray.push(emsg);
                                t.addErro(o, emsg);
                            });
                        } else {
                            if (!o.data('result.v')) {
                                var emsg = o.data('emsg.v');
                                cf.erroArray.push(emsg);
                                t.addErro(o, emsg);
                                return false;
                            }
                        }
                    }
                    break;
                //一起验证,要么都验证,要么都不验证
                case "group":
                    var flag = true;
                    $.each(cf.group, function (bb, vv) {
                        if ($(vv).val() != "") flag = false;
                    });
                    if (flag) {
                        t.hideErro(o);
                        return true;
                    }
                    break;
                //默认使用ST.Regs中相关验证配置
                default:
                    if (!ST.Regs[v1].reg.test(key, o)) {
                        emsg = ST.Regs[v1].desc;
                    }
                    break;
            }
            //用于定义错误号 现实不同错误提示
            if (emsg && eno == -1) {
                eno = i
            }
            if (emsg) break;
        }
        //fix default emsg
        var d_emsg = emsg;
        if (emsg) {
            emsg=o.attr("emsg") || o.attr("placeholder") || emsg;
            if (key && o.attr('ignore')) {//ignore属性为忽略列表，多值以空格隔开
                if (o.attr('ignore').split(' ').getIndex(key) > -1) return true;
            }
            if (o.attr("emsg")) {
                var _arr = o.attr("emsg").split(" ");
                if (_arr[eno]) {
                    emsg = _arr[eno];
                }else{
                    emsg = d_emsg;    //若emsg 列表找不到则使用默认请求
                }
            }
            //根据错误号 现实不同错误提示
            cf.erroArray.push(emsg);
            if ($(f).attr('erroappend')) {
                if ($('#ST_temp').size() == 0) {
                    alert(ST.LRes.NeedTemp);
                }
                t.addErro(o, emsg, $(f).attr('errtmp'));
            } else if ($(f).attr('errtar')) {
                $("#" + $(f).attr('errtar')).html(emsg).css("visibility", "visible");
            } else {
                ST.tipMsg({error:emsg},1500);
                //if (!cancel) t.showToolTip(o, emsg, 3000);
            }
            return false;
        } else {
            if ($(f).attr('errtar')) {
                $("#" + $(f).attr('errtar')).html("").css("visibility", "hidden");
            } else {
                if (key == "" && o.attr("etips")) {
                    t.showTips(o, o.attr("etips"), "info");
                } else if (o.attr("succmsg")) {
                    t.showTips(o, o.attr("succmsg"), "success");
                } else if($(f).attr("showSucc") && !o.attr("succcancel")){
                        t.showTips(o, "", "success");
                }else {
                    t.hideErro(o);
                }
            }
            return true;
        }
    },
    //暂不支持多个
    showToolTip: function (o, msg, time) {
        var t = this;
        if (!t.$toolTip) t.$toolTip = ST.toolTip({pos: 3, align: 3});
        t.$toolTip.changePos(o);
        t.$toolTip.changeData({
            tipmsg: msg
        });
    },
    inArray: function (a, nn) {
        for (var i = 0; i < a.length; i++) {
            if (nn.toLowerCase() == a[i])return true;
        }
        return false;
    },
    //添加错误
    addErro: function (o, emsg, errtmp) {
        var pn = $(o).parent();
        var els = $(pn).find("div[verify]");
        errtmp = errtmp || 'form_erromsg_temp';
        if (o.attr('errtar')) els = $('#' + o.attr('errtar')).attr('verify', '1');
        if (els.length < 1) {
            var div = $('<div verify="1" class="inline">');
            div.appendTo(pn);
            ST.JTE.fetch(errtmp).toFill(div, {type: "error", msg: emsg})
        } else {
            if (o.attr('vno')) els.attr('vno', o.attr('vno'));
            els.show().html(ST.JTE.fetch(errtmp).getFilled({type: "error", msg: emsg}));
        }
    },
    //隐藏所有错误信息
    hideAllErro: function (f) {
        $(f).find('div[verify="1"]').each(function () {
            $(this).hide().html("");
        });
    },
    //显示提示信息
    showTips: function (o, msg, type) {
        var pn = $(o).parent();
        type = type || "success";
        var els = $(pn).find("div[verify=1]");
        if (els.length < 1) {
            var div = $("<div verify='1' class='inline'>");
            div.appendTo(pn);
            ST.JTE.fetch('form_erromsg_temp').toFill(div, {type: type, msg: msg})
        } else {
            els.show().html(ST.JTE.fetch("form_erromsg_temp").getFilled({type: type, msg: msg}));
        }
    },
    //隐藏错误
    hideErro: function (o) {
        if ($(o).attr('errtar')) {
            var errtar = $('#' + $(o).attr('errtar'));
            if (!errtar.length) return;
            if (errtar.attr('vno') == $(o).attr('vno')) errtar.hide().html("");
        } else {
            var pn = $(o).parent();
            $(pn).find('div[verify="1"]').each(function () {
                $(this).hide().html("");
            });
        }
    },
    //添加表单信息
    addFormInfo: function(form) {
        this.forms.push({f: form, els: [], erroArray: [], isAjaxVerify:0,  group: [], subBtn: $(this).find("input[type='submit'],button[type='submit']")});
    }
};
$.extend(ST,{
    initVerify:function(){
        ST.Verify.init();
    }
});
ST.TODOLIST.push({method:"initVerify",pars:""});
