define('lib/ST', '', function(require) {
    //依赖jQuery 人为控制
    require('lang/lres');
    //添加JTE模板默认容器
    if (!$("#ST_temp").size()) {
        $("<div id='ST_temp' style='display: none;'>").appendTo("body");
    }

    $.extend(ST, {
		init: function () {
            ST.setMethod();
        },
        __todo: function (fn, pars) {
            ST.__lastfn = fn;
            ST.__lastarg = $.Lang.toArray(arguments).splice(1, arguments.length - 1);
            var a = ST;
            if (~fn.indexOf(".")) {
                fn = fn.split(".");
                while (fn.length > 1) a = a[fn.shift()] || ST;
                fn = fn[0];
            }
            return a[fn] && a[fn].apply(a, ST.__lastarg);
        },
        setMethod: function () {
            ST.todo = ST.__todo;
            if (ST.TODOLIST) {
                for (var i = 0, l = ST.TODOLIST.length, dl; i < l, dl = ST.TODOLIST[i]; i++) {
                    ST[dl.method] && ST[dl.method](dl.pars);
                }
            }
            ST.todoList();
        },
        _d: new Date().getTime(),
        emptyFn: function() {}, //空方法
        debugErro: function(e, xhr, opt) {
            ST.tipMsg({
                error: opt + ",地址:" + e.url + "状态: " + e.status + " " + e.statusText
            }, 5000, true);
        },
        debugClientErro:function(bus,pars){
            ST.tipMsg({
                error: "业务:"+bus+",参数:"+pars
            }, 5000, true);
        },
        /*
         统一的方法入口
         @desc
         par1  为执行的方法
         其他参数均为 方法fn的参数传递
         @eg
         ST.todo("goto","a","b");
         执行了goto方法 参数为 a ,b;
         */
        todo: function(fn, pars) {
            ST.__lastfn = fn;
            ST.__lastarg = $.Lang.toArray(arguments).splice(1, arguments.length - 1);
            var a = ST;
            if (~fn.indexOf(".")) {
                fn = fn.split(".");
                while (fn.length > 1) a = a[fn.shift()] || ST;
                fn = fn[0];
            }
            return a[fn] && a[fn].apply(a, ST.__lastarg);
        },
        queryString:function(str,name){
            var result = str.match(new RegExp("[\?\&]?" + name+ "=([^\&]+)","i"));
            if(result == null || result.length < 1){
                return "";
            }
            return result[1];
        },
        /*查找iframe设置高度*/
        IfmSetHeight:function(pars){
            var height = ST.queryString(pars,"height");
            var url = ST.queryString(pars,"url");
            if(url){
                $("iframe[src='"+url+"']").attr("height",height);
            }
        },
        /*选择序列化数据 暂不支持深度*/
        serData:function(data,tag){
            var obj={};
            for(var i in tag){
                if (tag.hasOwnProperty(i)){
                    //效验数据
                    obj[i] = (data[i]==undefined? tag[i] : data[i]);
                }
            }
            return obj;
        },
        /*
         计算位置
         */
        _posCalculate: function(em, em1, pos, align) {
            if (!em.size()) throw new Error("em not found!");
            var x, y, l, t, a = $.getBound(em[0]),
                b = {
                    w: em1.innerWidth(),
                    h: em1.innerHeight()
                }, c = $.documentSize();
            pos = Number(pos || 3);
            align = Number(align || 3);
            var dir = pos;
            //上 右 下 左
            switch (pos) {
                case 1:
                    y = a.y - b.h;
                    if (y < 0) {
                        dir = 3;
                        y = a.y + a.h;
                    }
                    break;
                case 2:
                    x = a.x + a.w;
                    if (x + b.w > c.fullWidth) {
                        dir = 4;
                        x = a.x - b.w;
                    }
                    break;
                case 3:
                    y = a.y + a.h;
                    if (y + b.h > c.fullHeight) {
                        dir = 1;
                        y = a.y - b.h;
                    }
                    break;
                case 4:
                    x = a.x - b.w;
                    if (x - b.w < 0) {
                        dir = 2;
                        x = a.x + a.w;
                    }
                    break;
            }
            //判断对齐方式
            //上 居中 下
            switch (pos) {
                case 1:
                case 3:
                    switch (align) {
                        case 1:
                            l = Math.min(a.w, b.w) / 2;
                            x = a.x;
                            break;
                        case 2:
                            l = b.w / 2;
                            x = a.x + a.w / 2 - b.w / 2;
                            break;
                        case 3:
                            x = a.x - b.w + a.w;
                            l = (a.w > b.w) ? b.w / 2 : (a.x - x) + a.w / 2;
                            break;
                    }
                    break;
                case 2:
                case 4:
                    switch (align) {
                        case 1:
                            t = Math.min(a.h, b.h) / 2;
                            y = a.y;
                            break;
                        case 2:
                            t = b.h / 2;
                            y = a.y + a.h / 2 - b.h / 2;
                            break;
                        case 3:
                            y = a.y - b.h + a.h;
                            t = (a.h > b.h) ? b.h / 2 : (a.y - y) + a.h / 2;
                            break;
                    }
                    break;
            }
            return {
                x: x,
                y: y,
                l: l,
                t: t,
                w: b.w,
                h: b.h,
                dir: dir
            }
        },
        /*
         设置页面hash参数
         backbone路由格式   /p:1/b:2
         */
        setHashPar:function(a,b){
            if (location.hash.match(new RegExp("(?:#|\/)" + a + ":(.*?)(?=\/|$)"))) {
                location.hash = location.hash.replace(new RegExp("(?:#|\/)" + a + ":(.*?)(?=\/|$)"), '/'+ a + ":" + b);
            } else {
                location.hash += '/' + a + ":" + b;
            }
        },
        /*
         获取页面hash参数
         backbone路由格式   /p:1/b:2
         */
        getHashPar:function(a){
            var hash = location.hash.replace("#", "");
            return (hash.match(new RegExp("(?:^|\/)" + a + ":(.*?)(?=\/|$)")) || ['', null])[1];
        },
        /*
         设置页面hash
         */
        setHash: function(a, b) {
            if (location.hash.match(new RegExp("(?:#|&)" + a + "=(.*?)(?=&|$)"))) {
                location.hash = location.hash.replace(new RegExp("(?:#|&)" + a + "=(.*?)(?=&|$)"), a + "=" + b);
            } else {
                location.hash += location.hash ? '&' + a + "=" + b : a + "=" + b;
            }
        },
        /*
         获取页面hash
         */
        getHash: function(a) {
            var hash = location.hash.replace("#", "");
            return (hash.match(new RegExp("(?:^|&)" + a + "=(.*?)(?=&|$)")) || ['', null])[1];
        },
        /*
         顺序执行方法
         @desc
         func 需要顺序执行的方法数组
         @eg
         ST._ProcessFun([fun1,fun2]);
         */
        _ProcessFun: function(func) {
            var ms = 20;
            setTimeout(function() {
                func.shift()();
                if (func) {
                    setTimeout(arguments.callee, ms);
                }
            }, ms);
        },
        /*
         延迟加载器
         @desc
         延迟加载指定的区域,延迟加载的对象必须具备指定的高宽
         o
         {
         tag:"img",                    //延迟加载的标签
         attr:"lazy_src"              //延迟属性
         }
         @eg

         */
        lazyLoader: function(a) {
            a = a || {};
            var f, img = new Image(),
                idx = 0,
                isCompleted = false,
                hasHiden = false,
                ds = $.documentSize(),
                t = ds.scrollTop,
                h = ds.viewHeight,
                l,
                last_offset,
                listener,
                o = {
                    tag: a.tag || "img",
                    attr: a.attr || "lazy_src",
                    lazy_method: a.lazy_method ||
                        function() {
                            if (idx >= l) {
                                /*
                                 if(hasHiden){
                                 listener&&window.clearTimeout(listener)
                                 listener = window.setTimeout(function(){
                                 idx=hasHiden-1;
                                 loadNext();
                                 },500); //添加监听
                                 }
                                 */
                                return;
                            }
                            if (els[idx] == undefined) {
                                loadNext();
                                return;
                            }
                            var a = $(els[idx]);
                            var ah = a.hasClass('holder') ? a.parent().height() : a.height();
                            //是否在可视区域内
                            if (a.offset().top > (t + h) || (a.offset().top + ah) < t) {
                                loadNext();
                                return;
                            }
                            /*//是否真实可见性 lazy_method除外
                             else if(a[0].clientHeight==0 && !a.attr('lazy_method')){
                             hasHiden=hasHiden||idx;
                             loadNext();
                             return;
                             }
                             */
                            //延迟加载图片
                            if (a.attr('lazy_src') != undefined) {
                                if ($.Lang.Browser.isIE6) {
                                    //img.onload = function() {
                                    //a.attr('src', this.src);
                                    a.attr('src', a.attr('lazy_src'));
                                    img.onload = null;
                                    delete els[idx];
                                    //修正防止循环溢出
                                    window.setTimeout(function() {
                                        loadNext();
                                    }, 17);
                                    //}
                                    //img.src = a.attr("lazy_src");
                                } else {
                                    a.attr('src', a.attr('lazy_src'));
                                    delete els[idx];
                                    loadNext();
                                }
                                //延迟加载方法
                            } else if (f = a.attr('lazy_method'), ST[f]) {
                                ST.todo(f, a);
                                delete els[idx];
                                window.setTimeout(function() {
                                    loadNext();
                                }, 17);
                            }
                        }
                }
            var els = $(o.tag + "[lazy_method]," + o.tag + "[lazy_src]"),
                lazy_method = o.lazy_method;
            l = els.length;
            var loadNext = function() {
                idx++;
                lazy_method();
            };
            var listenerHandle = function() {
                ds = $.documentSize();
                t = ds.scrollTop;
                h = ds.viewHeight;
                if (last_offset == t + h) {
                    return;
                } else {
                    last_offset = t + h;
                    if ($("#bottom_top").length > 0) {
                        if (ds.scrollTop < 20) {
                            $("#bottom_top").hide();
                        } else {
                            $("#bottom_top").show();
                        }
                    }
                    hasHiden = false;
                    for (var i = 0; i < els.length; i++) {
                        if (els[i] != undefined) {
                            idx = i;
                            lazy_method();
                            return;
                        }
                    }
                    isCompleted = true;
                    if ($("#bottom_top").length == 0)
                        $(window).unbind("scroll.laz");
                }
            };
            //if (els.length) {
            lazy_method();
            $(window).bind("scroll.laz", function() {
                //if(isCompleted) return;
                listener && window.clearTimeout(listener)
                listener = window.setTimeout(listenerHandle, 200); //添加监听
            });
            //};
            var bt;
            if (bt = $("#bottom_top"), bt.length > 0) {
                if (screen.width < 1025) {
                    bt.css("margin-right", "+=" + bt.width());
                }
            }
        },

        /*
         序列化表单
         查找所有具备name的节点信息
         @desc
         f:jqueryObject           //表单对象或者 "#form"
         @eg
         ST.serObj("#form1");
         序列化表单form1  返回序列化后的Object

         */
        serObj: function(f) {
            var a = {};
            $(f).find("*[name]").each(function() {
                if (this.type && this.type.c(/radio|checkbox/i) && !this.checked) return true
                if (b = $(this).val(), b.t() != $(this).attr("placeholder"))
                    a[$(this).attr("name")] = b;
            });
            return a;
        },
        /*
         序列化表单
         查找所有具备name的节点信息
         @desc
         f:jqueryObject           //表单对象或者 "#form"
         s:是否序列化为对象
         @eg
         ST.serForm("#form1");
         序列化表单form1
         也可以使用 var $("#form1").serialize();// jquery方法
         */
        serForm: function(f, s) {
            var data = {}, a = [],
                b, t;
            if (s) a = {};
            $(f).find("*[name]").each(function() {
                if (this.type && this.type.c(/radio|checkbox/i) && !this.checked) return true;
                b = $(this).val();
                if ($.Lang.isArray(b)) {
                    t = this;
                    $.each(b, function(i, v) {
                        if (s) {
                            if (!a[t.name]) a[t.name] = [];
                            a[t.name].push(v);
                        } else {
                            a.push(t.name + '=' + window.encodeURIComponent(v));
                        }
                    });
                } else {
                    if (s) {
                        if (a[this.name] == undefined) {
                            a[this.name] = b;
                        } else {
                            if (!$.Lang.isArray(a[this.name]))
                                a[this.name] = [a[this.name].toString()];
                            a[this.name].push(b);
                        }
                    } else {
                        a.push(this.name + '=' + window.encodeURIComponent(b));
                    }
                }
            });
            return s ? a : a.join("&");
        },
        /*
         ajax提交表单
         @desc
         o:object                     //相关设置
         {
         f:jqueryObject           //表单对象或者 "#form"
         succ:function            //成功后的回调 return j(json对象)
         error:function           //成功后的回调 return e(错误对象)
         }
         @eg
         ST.postForm({
         f:"#Form1",
         succ:function(j){
         ST.tipMsg(j.info||j.info||j.message||'提交成功',1000,!0);
         window.setTimeout(function(){
         if(j.url){location.href=j.url;}else{ST.reload();}
         },1000);
         }
         },
         error:function(e){
         ST.hideMsg(!0);
         ST.tipMsg("提交失败!",2000);
         }
         });
         ajax 提交 Form1表单
         */
        postForm: function(o) {
            var t = this,
                f = $(o.f),
                datatype;
            if (f.size() == 0) return;
            datatype = f.attr("isJsonP") ? "jsonp" : ""
            ST.getJSON(f.attr('action') || location.href, t.serForm(o.f), function(json) {
                if (json) {
                    o.succ && o.succ(json);
                }
            }, function(e) {
                o.error && o.error(e);
                ST.CurVode && ST.CurVode.scode(); //刷新验证码
            }, "post", datatype);
            return false;
        },
        /*
         从服务端获取Json数据
         @desc
         url  服务端数据地址
         data post 数据
         sfn  成功后的回调
         errfn 失败后的回调
         @eg
         ST.getJson();
         */
        getJSON: function(url, data, sfn, errfn, method, datatype) {
            if(!url) return;
            var _data;
            if ($.Lang.isObject(data)) {
                //参数排序处理
                _data = $.extend(data, ST.AJAXDATA);
            } else if ($.Lang.isString(data)) {
                var str = ST.O2S(ST.AJAXDATA) ;
                _data = data ? data + "&" + str : str;
            }
            return  $.ajax({
                    type: method || "get",
                    dataType: datatype || "json",
                    contentType: 'application/x-www-form-urlencoded;charset=utf-8',
                    url: url,
                    data: _data || "",
                    error: function(e, xhr, opt) {
                        if (xhr == "abort") {
                            $.log("abort");
                            return;
                        } else {
                            e.url = url;
                            e.data = _data;
                            ST.debug && ST.debugErro(e, xhr, opt);
                            errfn && errfn(e);
                        }
                    },
                    success: function(j) {
                        if (!j) ST.debug && ST.tipMsg("no value has returned!");
                        var s = j.status || j.state,fn,
                            flag = false;
                        if(s.indexOf("#")>-1) {
                            fn = s.split("#");
                            s = fn.shift();
                            fn = fn.toString();
                        }
                        switch (s.toLowerCase()) {
                            case "notice_login":
                                ST.login(j.message || "", "", function() {
                                    ST.getJSON(url, data || "", sfn || "", errfn || "")
                                });
                                break;
                            case "js_method":
                                if (j.jsmethod) {
                                    flag = ST.todo(j.jsmethod, j.data);
                                }
                                break;
                            case "notice": //notice notice
                            case "success":
                                flag = true;
                                break;
                            case "nologin":
                                ST.tipMsg("登录超时!", 2000);
                                window.setTimeout(function(){
                                    location.href = ST.PATH.LOGIN;
                                },2000);
                                break;
                            case "tip":
                                ST.tipMsg(j.info || j.message || ST.LRes.opFailed, 2000);
                                break;
                            case "tip_alert":
                                ST.alert(j.info || j.message || ST.LRes.opFailed);
                                break;
                            case "vcode":
                                ST._tipVcode && ST._tipVcode(j);
                                break;
                            case "tip_success":
                                ST.tipMsg({
                                    success: j.info || j.message || ST.LRes.opSuccess
                                }, 2000);
                                flag = true;
                                break;
                            case "tip_error":
                                ST.tipMsg({
                                    error: j.info || j.message || ST.LRes.opFailed
                                }, 2000, true);
                                break;
                        }
                        if(fn){
                            ST[fn] && ST[fn](j);
                        }
                        if (flag) {
                            sfn && sfn(j);
                        } else {
                            ST.debug && ST.tipMsg({error: j.info},3000);
                            errfn && errfn(j);
                        }
                    }
                });
        },
        //hack方法 ,勿用,仅支持一级
        O2A:function(o){
            var a = [];
            for (var i in o) {
                a.push({
                    text:o[i],
                    value:i
                });
            }
            return a;
        },
        O2S: function(o) {
            var a = [];
            for (var i in o) {
                a.push(i + "=" + o[i]);
            }
            return a.join("&");
        },
        /*
         在指定的时间片断后重载页面
         @desc
         time:num                           //事件（单位毫秒）
         url:string                         //地址URL
         @eg
         ST.reload();        					刷新页面
         ST.reload(100,"http://www.baidu.com")    1S后跳转到百度
         */
        reload: function(time, url) {
            window.setTimeout(function() {
                if (!url) location.reload();
                else location.href = url;
            }, time || 800);
        },
        /*
         限制字符输入
         @desc
         cid:string                           //限制的输入控件
         lmt:num                              //限制的数字
         sid:string                           //文字显示ID
         @eg
         见 limitLn
         */
        limit: function(cid, lmt, sid, flag) {
            var v = $("#" + cid).val(),
                l = v.l();
            if (flag) l = parseInt(l / 2, 10);
            l = l > lmt ? '<font color="red">{0}</font>'.format(l) : l;
            $("#" + sid).val(l).html(l);
        },
        /*
         限制字符长度
         @desc
         cid:string                           //限制的输入控件
         lmt:num                              //限制的数字
         sid:string                           //文字显示ID
         @eg
         ST.limitLn("title",80,"title_count");
         <input name="title" id="title" type="text"  /><span><b id="title_count">0</b>/80</span>
         */
        limitLn: function(cid, lmt, sid, flag) {
            var t = this,
                fn = function() {
                    t.limit(cid, lmt, sid, flag);
                };
            document.getElementById(cid).onpropertychange = fn;
            document.getElementById(cid).oninput = fn;
            fn();
        },
        /*
         滚动到制定区域
         @desc
         el 		滚动到的元素
         s       滚动时间 //默认800号码 小于50毫秒内则使用scrollIntoView
         @eg
         ST.Scrollto("id")
         */
        Scrollto: function(el, s) {
            var em = document.getElementById(el);
            if (!em) return;
            if (s && (s | 0) < 50) {
                em.scrollIntoView(); //滚动到可视区域
                $("#bottom_top").hide();
                return false;
            }
            var z = this;
            z.o = s || 800;
            z.p = $.getBound(el);
            z.s = $.documentSize();
            z.clear = function() {
                clearInterval(z.timer);
                z.timer = null
            };
            z.t = (new Date).getTime();
            $("#bottom_top").hide();
            z.step = function() {
                var t = (new Date).getTime();
                var p = (t - z.t) / z.o;
                if (t >= z.o + z.t) {
                    z.clear();
                    setTimeout(function() {
                        z.scroll(z.p.y);
                    }, 13);
                } else {
                    st = ((-Math.cos(p * Math.PI) / 2) + 0.5) * (z.p.y - z.s.scrollTop) + z.s.scrollTop;
                    z.scroll(st);
                }
            };
            z.scroll = function(t) {
                window.scrollTo(0, t)
            };
            z.timer = setInterval(function() {
                z.step();
            }, 13);
        },
        /*获取焦点*/
        inputFocus: function(inputObj) {
            if (inputObj.get(0)) {
                inputObj.focus();
                ST.setCursorPosition(inputObj.get(0), inputObj.val().length);
            }
        },
        /*设置光标位置*/
        setCursorPosition: function(obj, pos) {
            if (obj.setSelectionRange) {
                obj.focus();
                obj.setSelectionRange(pos, pos);
            } else if (obj.createTextRange) {
                var range = obj.createTextRange();
                range.collapse(true);
                range.moveEnd('character', pos);
                range.moveStart('character', pos);
                range.select();
            }
        },
        Cookie:{
            get: function (n) {
                var a = document.cookie.match(new RegExp("(^| )" + n + "=([^;]*)(;|$)"));
                return a ? a[2].e(!0) : a;
            },
            set: function (name, value, hour, domain, path) {
                if ($.Lang.isObject(value))
                    value = ST.objTostr(value);
                var sc = name + '=' + (value + '').u();
                path = path || '/';
                if ($.Lang.Browser.isIE) path = path.replace(/[^\/]+$/, "");//针对IE
                if( hour !== 0 ){
                    hour = Number(hour) || 48;
                    var date = new Date();
                    date.setTime(date.getTime() + hour * 3600 * 1000);
                    sc += ';expires=' + date.toGMTString();
                }
                if (domain)sc += ';domain=' + domain;
                sc += ';path=' + path;
                document.cookie = sc;
            },
            del: function (n, domain, path) {
                var t = this;
                t.set(n, "", -10000, domain, path);
            }
        },
        //对象转化为字符串
        objTostr: function(o) {
            var t = this;
            if (o == undefined) {
                return "";
            }
            var r = [];
            if (typeof o == "string") return "\"" + o.replace(/([\"\\])/g, "\\$1").replace(/(\n)/g, "\\n").replace(/(\r)/g, "\\r").replace(/(\t)/g, "\\t") + "\"";
            if (typeof o == "object") {
                if (!o.sort) {
                    for (var i in o)
                        r.push("\"" + i + "\":" + ST.objTostr(o[i]));
                    if ( !! document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
                        r.push("toString:" + o.toString.toString());
                    }
                    r = "{" + r.join() + "}"
                } else {
                    for (var i = 0; i < o.length; i++)
                        r.push(ST.objTostr(o[i]))
                    r = "[" + r.join() + "]";
                }
                return r;
            }
            return o.toString().replace(/\"\:/g, '":""');
        },
        /*添加收藏*/
        AddFavorite: function(url, title) {
            try {
                window.external.addFavorite(url, title);
            } catch (e) {
                try {
                    window.sidebar.addPanel(title, url, "");
                } catch (e) {
                    alert("加入收藏失败，请使用Ctrl+D进行添加");
                }
            }
        },
        /*设为首页*/
        SetHome: function(obj, url) {
            try {
                obj.style.behavior = 'url(#default#homepage)';
                obj.setHomePage(url);
            } catch (e) {
                if (window.netscape) {
                    try {
                        netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
                    } catch (e) {
                        alert("此操作被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将 [signed.applets.codebase_principal_support]的值设置为'true',双击即可。");
                    }
                    var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
                    prefs.setCharPref('browser.startup.homepage', url);
                } else {
                    alert('您的浏览器不支持自动自动设置首页, 请使用浏览器菜单手动设置!');
                }
            }
        },
        Code: {
            REGX_HTML_ENCODE: /"|&|'|<|>|[\x00-\x20]|[\x7F-\xFF]|[\u0100-\u2700]/g,
            REGX_HTML_DECODE: /&\w{1,};|&#\d{1,};/g,
            REGX_ENTITY_NUM: /\d{1,}/,
            HTML_DECODE: {
                "&lt;": "<",
                "&gt;": ">",
                "&amp;": "&",
                "&nbsp;": " ",
                "&quot;": "\"",
                "&copy;": "©"
            },
            encodeHtml: function(s) {
                s = (s != undefined) ? s : this;
                return (typeof s != "string") ? s : s.replace(this.REGX_HTML_ENCODE, function($0) {
                    var c = $0.charCodeAt(0),
                        r = ["&#"];
                    c = (c == 0x20) ? 0xA0 : c;
                    r.push(c);
                    r.push(";");
                    return r.join("");
                });
            },
            decodeHtml: function(s) {
                var HTML_DECODE = this.HTML_DECODE,
                    REGX_NUM = this.REGX_ENTITY_NUM;
                s = (s != undefined) ? s : this;
                return (typeof s != "string") ? s : s.replace(this.REGX_HTML_DECODE, function($0) {
                    var c = HTML_DECODE[$0];
                    if (c == undefined) {
                        var m = $0.match(REGX_NUM);
                        if (m) {
                            var cc = m[0];
                            cc = (cc == 160) ? 32 : cc;
                            c = String.fromCharCode(cc);
                        } else {
                            c = $0;
                        }
                    }
                    return c;
                });
            }
        },
        /*
         JS模板渲染引擎
         @desc
         methods
         {
         fetch:             //获取制定的模板字符串
         @par
         a:string    //模板名称

         toFill:             //添加到制定的ID
         @par
         a:string    //使用模板的HTML ID
         b:Object   	//传入模板内的数据

         getFilled:          //获取模板填充数据后的字符串 (常用于模板内部内容嵌套)
         @par
         a:Object   	//传入模板内的数据

         }
         @eg
         var virtualData=[{id:1,name:"test"},{id:2,name:"test1"},{id:3,name:"test2"},{id:4,name:"test3"}];
         ST.JTE.fetch("drag_tmp").toFill("dragarea",{virtualData:virtualData});

         */
        JTE: (function(){
            var _={
                /*hack escape方法*/
                escape:function(s){
                    return(s+'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\s/g,'&nbsp;').replace(/'/g,'&#039;').replace(/"/g,'&quot;');
                }
            },ext=$.extend; //ST依赖jquery 直接使用jquery extend
            /*标签匹配设置*/
            _.templateSettings = {
                evaluate    : /<%([\s\S]+?)%>/g,
                interpolate : /<%=([\s\S]+?)%>/g,
                escape      : /<%-([\s\S]+?)%>/g
            };

            /*未匹配*/
            var noMatch = /(.)^/;

            /*字符转义*/
            var escapes = {
                "'":      "'",
                '\\':     '\\',
                '\r':     'r',
                '\n':     'n',
                '\t':     't',
                '\u2028': 'u2028',
                '\u2029': 'u2029'
            };

            var escaper = /\\|'|\r|\n|\t|\u2028|\u2029/g;

            /*模板引擎*/
            _.template = function(text, data, settings) {
                var render;
                settings = ext({}, settings, _.templateSettings);

                //合并多个正则表达式
                var matcher = new RegExp([
                    (settings.escape || noMatch).source,
                    (settings.interpolate || noMatch).source,
                    (settings.evaluate || noMatch).source
                ].join('|') + '|$', 'g');

                // 模板编译
                var index = 0;
                var source = "__p+='";
                /*利用replace进行正则匹配*/
                text.replace(matcher, function(match, escape, interpolate, evaluate, offset) {
                    source += text.slice(index, offset)
                        .replace(escaper, function(match) { return '\\' + escapes[match]; });

                    if (escape) {
                        source += "'+\n((__t=(" + escape + "))==null?'':_.escape(__t))+\n'";
                    }
                    if (interpolate) {
                        source += "'+\n((__t=(" + interpolate + "))==null?'':__t)+\n'";
                    }
                    if (evaluate) {
                        source += "';\n" + evaluate + "\n__p+='";
                    }
                    index = offset + match.length;
                    return match;
                });
                source += "';\n";

                //替换data到局部作用链域
                if (!settings.variable) source = 'with(obj||{}){\n' + source + '}\n';

                source = "var __t,__p='',__j=Array.prototype.join," +
                    "print=function(){__p+=__j.call(arguments,'');};\n" +
                    source + "return __p;\n";

                try {
                    render = new Function(settings.variable || 'obj', '_', source);
                } catch (e) {
                    e.source = source;
                    throw e;
                }

                if (data) return render(data, _);
                var template = function(data) {
                    return render.call(this, data, _);
                };

                // 编译成方法缓存到source;
                template.source = 'function(' + (settings.variable || 'obj') + '){\n' + source + '}';
                return template;
            };
            //以上直接使用underscore自带模板解析
            //以下为JTE模板包装
            var w=[], y, h, n='ST_temp',r;
            return $.extend(_,{
                using: function(a, c) {
                    if(!w[n]) {
                        if(a.length<50){
                            w[a] = ($("#"+a).size()!=0) ? document.getElementById(a).innerHTML : c;
                            w[a] = (w[a] + '').replace(/\s+/g,' ');
                        }else{
                            n = "str_temp";
                            w[n] = a;
                            return this;
                        }
                    }
                    n = a;
                    return this;
                },
                getString: function() {
                    return y || w;
                },
                fetch: function(a, c) {
                    if(!w[n]) this.using(n,n);
                    if(w[n]) c = w[n].match(new RegExp('{' + a + '}([\\s\\S]*?){/' + a + '}'));
                    if (!c) throw new Error('no tpl blk:' + a);
                    y = c[1];
                    return this
                },
                getFilled: function(a,s) {
                    if(!y)  throw new Error('no tpl match,first use fetch');
                    r = this.template(y,a,s);
                    return r;
                },
                toFill: function(a, b, s) {
                    h = this.getFilled(b,s);
                    (a.jquery)? a.html(h):(document.getElementById(a) && (document.getElementById(a).innerHTML=h));
                    return this;
                },
                end:function(){
                    n='ST_temp';
                    return this;
                },
                onError: function(a, b) {

                }
            });
        })()
    });

    /*一些日期算法*/
    ST.CalenderCal = {
        Month: ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', "十一", "十二"],
        getWeek: function (year, month, day) {
            var dd, ds, obj = {}, y, m, d;
            dd = new Date(year, parseInt(month, 10) - 1, parseInt(day, 10));//当前日期对象
            ds = dd.getDay();               //当前星期几
            //算出星期一的日期
            dd = new Date(dd.getTime() - ds * 86400000);//当前日期周一所对应对象
            for (var i = 0; i <= 6; i++) {
                y = dd.getFullYear();
                m = dd.getMonth() + 1;
                d = dd.getDate();
                obj[i] = y + "-" + (m < 10 ? '0' + m : m) + "-" + (d < 10 ? '0' + d : d);
                dd = new Date(dd.getTime() + 86400000); //获取上一天日期对象
            }
            return obj;
        },
        getDays: function (year, month, dismonth) {
            month = parseInt(month, 10);
            if (dismonth) {
                if (-12 < dismonth && dismonth > 12) {
                    dismonth = 0;
                }
                month += dismonth;
                if (month > 12) {
                    year++;
                    month -= 12
                }
                ;
                if (month < 1) {
                    year--;
                    month += 12
                }
                ;
            }
            var days = 30;
            if (month == 2) {
                if ((year % 4 == 0 && year % 100 != 0) || (year % 400 == 0)) {
                    days = 29;
                } else {
                    days = 28;
                }
            } else {
                if ((month <= 7 && month % 2 == 1) || (month >= 8 && month % 2 == 0)) {
                    days = 31;
                }
            }
            return days;
        },
        getMonthStr: function (month) {
            return this.Month[month] + "月";
        },
        getIntYears: function (year, fix) {
            if (!fix) fix = 1;
            var a, b = fix, c;
            year = year.toString();
            while (fix > 0) {
                c = Number(year.charAt(year.length - fix));
                a = (fix > 1 ? (c == 0 ? 1 : c) : c) * Math.pow(10, fix - 1);
                year = year - a;
                year = year.toString();
                fix--;
            }
            year = year - 1;
            return year;
        }
    };
    /*扩展原生对象String
     trim  去掉前后字符串
     @reg {RegEx} 可选的正则
     byteLen 字节长度
     contains 是否包含某个字符串|正则|字符等
     format 格式化字符串
     @eg "哈哈哈{0}".format("呵呵");
     r replace 增强的replace功能

     */
    $.extend(String.prototype, {
        /*
         去掉前后字符串
         @desc
         reg    正则表达式
         @eg
         "  aaa ".trim();
         */
        trim: function(reg) {
            return this.replace(reg || /^[\s\xa0\u3000\uFEFF]+|[\s\xa0\u3000\uFEFF]+$/g, '');
        },
        /*
         获取文字字节长度
         @eg
         "中文aaab".byteLen();
         */
        byteLen: function() {
            return this.replace(/[^\x00-\xff]/g, '**').length;
        },
        /*
         是否包含某个字符串|正则|字符
         @eg
         "我得aaab".contains("c");
         */
        contains: function(str) {
            var r = RegExp;
            var p = str;
            if (!$.Lang.is(str, r)) p = new r((p + '').replace(/([?|.{}\\()+\-*\/^$\[\]])/g, '\\$1'));
            return p.test(this);
        },
        escape: function(flag) {
            return window[(flag ? 'un' : '') + 'escape'](this);
        },
        encodeURI: function(flag) {
            return window[(flag ? 'de' : 'en') + 'codeURI'](this)
        },
        encodeURIComponent: function(flag) {
            return window[(flag ? 'de' : 'en') + 'codeURIComponent'](this)
        },
        /*
         格式化字符串
         @eg "哈哈哈{0}1{1}".format("呵呵","gaga");
         */
        format: function() {
            var s = this,
                a = arguments;
            if (s)
                s = s.replace(/{(\d+)}/g, function(b, c) {
                    return a[c]
                });
            return s
        },
        /*
         增强的replace功能
         */
        r: function(p, v, s, b) {
            s = this;
            b = $.Lang.isArray(v);
            if ($.Lang.isArray(p))
                while (p.length) s = s.replace(p.shift(), b && v.length ? v.shift() : '');
            else s = s.replace(p, $.Lang.isUndefined(v) ? '' : v);
            return s
        },
        /*
         用0补全位数：
         @eg:
         ST.prefixInteger(5,2);  // 05
         */
        prefixInteger: function(num, length) {
            return (num / Math.pow(10, length)).toFixed(length).substr(2);
        },
        /*
         日期格式化
         "2011-11-12".getDateFromFormat("YYYY-MM-DD");
         "2011-04-12".getDateFromFormat("YYYY-MM-DD");
         "12/11/2011".getDateFromFormat("DD/MM/YYYY");
         "2011-4-1".getDateFromFormat("YYYY-M-D");
         "2011-12-20".getDateFromFormat("YYYY-M-D");
         "12月20日(2011年)".getDateFromFormat("M月D日(YYYY年)");
         */
        getDateFromFormat: function(format) {
            var date, result = {
                year: 0,
                month: 0,
                day: 0
            }; //当然这里可以默认1970-1-1日
            if (date = this) {
                format.replace(/y+|Y+|M+|d+|D+/g, function(m, a, b, c) { //这里只做了年月日  加时分秒也是可以的
                    date.substring(a).replace(/\d+/, function(d) {
                        c = parseInt(d, 10)
                    });
                    if (/y+/i.test(m) && !result.year) result.year = c;
                    if (/M+/.test(m) && !result.month) result.month = c;
                    if (/d+/i.test(m) && !result.day) result.day = c;
                });
            }
            return result;
        }
    });
    /*扩展原生对象Array
     getJson  		获取array中的json 根据 name,value
     getJsonIndex    在Array数组中 获取指定name,val的位置
     removeJson   	移除array中的json 根据 name,value
     insert   		插入array 在 idx 位置 插入 obj
     getIndex  		获取obj 在array 中的位置
     remove          在Array数组中 获取指定val的位置
     */
    $.extend(Array.prototype, {
        /*
         获取array中的json 根据 name,value
         @eg
         [{id:1,name:"一"},{id:2,name:"二"}].getJson("id",1);
         */
        getJson: function(jsonName, jsonValue) {
            if (!this)
                return false;
            var tm = $.grep(this, function(n, i) {
                if (!n)
                    return false;
                return (n[jsonName] == jsonValue)
            });
            if (tm.length == 0)
                return false;
            else
                return tm[0];
        },
        //查询
        /*
         获取array中的json 根据select语法
         @eg
         [{id:1,name:"一"},{id:2,name:"二"}].fSelect("@id>1");
         */
        fSelect: (function() {
            var __proto = this;
            var __tmpl = function(_list) {
                var _ret = [];
                var _i = -1;
                for (var _k in _list) {
                    var _e = _list[_k];

                    if (_e && _e != __proto[_k]) {
                        if ($C)
                            _ret[++_i] = _e;
                    }
                }
                return _ret;
            }.toString();

            var __alias = [
                /@/g, '_e.', // 用 @ 访问子元素属性
                /<>/g, '!=', // 可以用 <> 代替 !=
                /AND/gi, '&&', // 可以用 AND 代替 &&
                /OR/gi, '||', // 可以用 OR 代替 ||
                /NOT/gi, '!', // 可以用 NOT 代替 !
                /([^=<>])=([^=]|$)/g, '$1==$2'
                // 可以用 = 代替 ==，但不影响原先的"==", "<=", ">="
            ];

            var __rQuote = /""/g;
            var __rQuoteTmp = /!~/g;

            function __interpret(exp) {
                exp = exp.replace(__rQuote, '!~');
                var arr = exp.split('"');
                var i, n = arr.length;
                var k = __alias.length;

                for (i = 0; i < n; i += 2) {
                    var s = arr[i];
                    // 扩展运算符
                    for (var j = 0; j < k; j += 2)
                        s = s.replace(__alias[j], __alias[j + 1]);
                    arr[i] = s;
                }
                for (i = 1; i < n; i += 2) {
                    arr[i] = arr[i].replace(__rQuoteTmp, '\\"')
                }
                return arr.join('"');
            }


            function __compile() {
                return eval('0,' + arguments[0]);
            }

            var __cache = {};
            return function(exp) {
                if (!exp)
                    return [];
                var fn = __cache[exp];
                try {
                    if (!fn) {
                        var code = __interpret(exp); //解释表达式
                        code = __tmpl.replace('$C', code); //应用到模版
                        fn = __cache[exp] = __compile(code); //实例化函数
                    }
                    return fn(this); //查询当前对象
                } catch (e) {
                    return [];
                }
            }
        })(),
        /*
         在Array数组中插入 指定的位置idx 插入对象object
         @eg
         [{id:1,name:"一"},{id:2,name:"二"}].insert({id:3,name:""},1)
         */
        insert: function(obj, idx) {
            var arr = this;
            if ($.Lang.isArray(arr)) arr.splice(idx == 0 ? 0 : idx || arr.length, 0, obj);
            return arr;
        },
        /*
         在Array数组中 获取指定name,val的位置
         @eg
         [{id:1,name:"一"},{id:2,name:"二"}].getJsonIndex("id",1);
         */
        getJsonIndex: function(jsonName, jsonValue) {
            var arr = this,
                i = -1;
            $.each(arr, function(idx, v) {
                if (v[jsonName] == jsonValue) {
                    i = idx;
                    return false;
                }
            });
            return i
        },
        /*
         移除Array中的JSon对象 根据 键->值
         @eg
         [{id:1,name:"一"},{id:2,name:"二"}].removeJson("id",1);
         */
        removeJson: function(jsonName, jsonValue) {
            var arr = this,
                result, i = -1;
            if (this.length == 0) return i;
            $.each(arr, function(idx, v) {
                if (v[jsonName] == jsonValue) {
                    arr.splice(idx, 1);
                    return false;
                }
            });
            return arr;
        },
        /*
         在Array数组中 获取指定val的位置
         @eg
         ["a","b"].getIndex("a");
         */
        getIndex: function(obj) {
            var arr = this,
                i = -1;
            $.each(arr, function(idx, v) {
                if ($.Lang.arrEqual(v, obj)) {
                    i = idx;
                    return false;
                }
            });
            return i
        },
        /*
        * 数组排序, 会对自身排序
        * */
        sortByField:function(sortBy,order){
            var arr = this;
            arr.sort(function(){
                var ordAlpah = (order == 'asc') ? '>' : '<';
                var sortFun = new Function('a', 'b', 'return a.' + sortBy + ordAlpah + 'b.' + sortBy + '?1:-1');
                return sortFun;
            }());
            return arr;
        },
        /*
         在Array数组中移出指定对象
         @eg
         ["a","b"].remove("a");
         */
        remove: function(obj) {
            var arr = this,
                i = this.getIndex(obj);
            if (i > -1) arr.splice(i, 1);
            return arr;
        }
    });
    /*扩展jquery
     getType  获取对象类型
     toText   转化为网页文本
     getUid   获取唯一ID
     toText   HTML转化为text文本
     genUrl   生成url地址
     documentSize 获取文档大小
     return {
     fullWidth
     fullHeight
     viewWidth
     viewHeight
     scrollLeft
     scrollTop
     clientLeft
     clientTop
     }

     Lang     常用方法
     {
     isString
     isMethod
     isArray
     isUndefined
     isNumber
     isObject
     is
     toArray
     arrEqual    //判断2个数组是否相等
     bind        //this指针相关绑定
     Browser     //浏览器版本
     }
     createClass 创建类
     @eg   new tipMsg ...
     */

    $.extend({

        _d: new Date().getTime(),
        /*
         JS调试
         @eg
         $.log("test");
         */
        log: function() {
            window.console && console.log(arguments[0]);
        },
        /*
         获取唯一的时间戳
         @eg
         $.getUid();
         @return
         "ST1293219328193"
         */
        getUid: function() {
            return 'ST' + ($._d++);
        },
        /*
         获取对象类型
         @eg
         var a={a:"123"};
         $.getType(a);
         */
        getType: function(obj) {
            var type;
            return (type = typeof(obj)) == 'object' ? obj == null && 'null' || Object.prototype.toString.call(obj).slice(8, -1).toLowerCase() : type;
        },
        /*
         替换HTML标记 转化为text
         @eg
         $.toText("<span>test</span>");
         */
        toText: function(s) {
            return (s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\s/g, '&nbsp;').replace(/'/g, '&#039;').replace(/"/g, '&quot;');
        },
        /*
         根据模块生成一个 URL地址
         @eg
         $.genUrl("diancan");
         */
        genUrl: function(a, b) {
            var Path = ST.PATH;
            return Path.ROOT + '/' + a.replace(/[&=]/g, Path.P) + (Path.U ? Path.P + Path.U : '') + (Path.SUFFIX ? Path.SUFFIX : '');
        },
        /*
         取消事件冒泡，阻止默认事件
         @eg
         $.stopEvent(e);
         */
        stopEvent: function(e) {
            if (!e) return;
            if (window.event) {
                window.event.cancelBubble = true;
                window.event.returnValue = false;
            } else {
                e.stopPropagation();
                e.preventDefault();
            }
        },
        /*
         是否是鼠标移出或进入 ,解决IE6下 hover bug
         @eg
         $.isMouseLeaveOrEnter(e,function(){
         //todo
         });
         */
        isMouseLeaveOrEnter: function(e, handler) {
            if (e.type != 'mouseout' && e.type != 'mouseover') return false;
            var reltg = e.relatedTarget ? e.relatedTarget : e.type == 'mouseout' ? e.toElement : e.fromElement;
            while (reltg && reltg != handler)
                reltg = reltg.parentNode;
            return (reltg != handler);
        },
        /*
         获取文档大小
         @eg
         $.documentSize();
         */
        documentSize: function(d) {
            d = d || document;
            var c = d.documentElement,
                b = d.body,
                e = d.compatMode == 'CSS1Compat' ? c : b,
                y = 'clientHeight',
                l = 'scrollLeft',
                t = 'scrollTop',
                w = 'scrollWidth',
                h = 'scrollHeight';
            return {
                fullWidth: e.scrollWidth,
                fullHeight: Math.max(e.scrollHeight, e[y]),
                viewWidth: e.clientWidth,
                viewHeight: e[y],
                scrollLeft: c[l] || b[l],
                scrollTop: c[t] || b[t],
                scrollWidth: c[l] || b[l],
                scrollHeight: c[t] || b[t],
                clientLeft: e.clientLeft,
                clientTop: e.clientTop
            }
        },
        /*
         获取元素基本信息
         @eg
         $.getBound("test")
         */
        getBound: function(el, a) {
            var l = 0,
                w = 0,
                h = 0,
                t = 0,
                p = document.getElementById(el) || el,
                o = $.documentSize(),
                s = 'getBoundingClientRect',
                r;
            if (p) {
                a = document.getElementById(a);
                w = p.offsetWidth;
                h = p.offsetHeight;
                if (p[s] && !a) {
                    r = p[s]();
                    l = r.left + o.scrollLeft - o.clientLeft;
                    t = r.top + o.scrollTop - o.clientTop
                } else
                    for (; p && p != a; l += p.offsetLeft || 0, t += p.offsetTop || 0, p = p.offsetParent) {}
            }
            return {
                x: l,
                y: t,
                w: w,
                h: h
            }
        },
        /*
         常用方法
         */
        Lang: {
            /*
             是否是字符串
             @eg
             $.Lang.isString("test");
             */
            isString: function(obj) {
                return $.getType(obj) == 'string';
            },
            /*
             是否是方法
             @eg
             $.Lang.isMethod("test");
             */
            isMethod: function(obj) {
                return $.getType(obj) == 'function';
            },
            /*
             是否是字符串
             @eg
             $.Lang.isArray("test");
             */
            isArray: function(obj) {
                return $.getType(obj) == 'array';
            },
            isUndefined: function(obj) {
                return $.getType(obj) == 'undefined';
            },
            isNumber: function(obj) {
                return $.getType(obj) == 'number';
            },
            isObject: function(obj) {
                return $.getType(obj) == 'object';
            },
            isNullObject:function(obj){
                for (var i in obj){
                    return false;
                }
                return true;
            },
            /*
             是否是指定类型
             @eg
             $.Lang.is("test","string");
             */
            is: function(test, aim) {
                var result;
                try {
                    result = (aim == 'string' || $.Lang.isString(aim)) ? $.getType(test) == aim : test instanceof aim
                } catch (e) {}
                return !!result;
            },
            /*
             参数转化为数组
             @eg
             $.Lang.toArray("123,456,789",",");
             */
            toArray: function(args, split) {
                if (!arguments.length) return [];
                if (!args || this.isString(args) || this.isUndefined(args.length)) {
                    return (args + '').split(split ? split : '');
                }
                var result = [];
                for (var i = 0, j = args.length; i < j; i++) {
                    result[i] = args[i];
                }
                return result;
            },
            /*
             数组是否完全相等
             @eg
             $.Lang.arrEqual(["123","456","789"],["123","456","789"]);
             */
            arrEqual: function(a1, a2) {
                var l;
                if ($.Lang.isArray(a1) && $.Lang.isArray(a2) && (l = a1.length) == a2.length) {
                    for (var i = 0; i < l; i++)
                        if (!$.Lang.arrEqual(a1[i], a2[i])) return false;
                    return true
                }
                return a1 === a2;
            },
            /*
             绑定obj对象到fn中
             @eg
             function(){
             var t=this;
             window.setTimeout($.Lang.bind(function(){
             //todo
             //这里就可以访问t对象了
             },t),3000);
             }
             */
            bind: function(fn, obj) {
                var a = $.Lang.toArray(arguments),
                    b = a.splice(0, 2);
                return obj ? function() {
                    fn.apply(obj, a)
                } : fn;
            },
            /*
             转化为Json格式字符串
             */
            toJson: function(s) {
                try {
                    return (new Function("", "return " + s))();
                } catch (e) {
                    return {
                        status: 'tip_error',
                        message: ST.LRes.serverBusy
                    }
                }
            },
            numToIp:function(num){
                if(num<10) return '00'+num;
                if(num<100) return '0'+num;
                return num;
            },
            /*
             * 数值转化为千分格式
             * */
            numToThousands: function(num) {
                if (!/^(\+|-)?(\d+)(\.\d+)?$/.test(num)) {
                    return num;
                }
                var a = RegExp.$1,
                    b = RegExp.$2,
                    c = RegExp.$3;
                // var re = new RegExp("(\\d)(\\d{3})(,|$)").compile();
                var re = new RegExp("(\\d)(\\d{3})(,|$)"); //.compile();
                while (re.test(b)) {
                    b = b.replace(re, "$1,$2$3");
                }
                return a + "" + b + "" + c;
            },
            /**
             * [numToMoney 数值转化为千分金钱格式]
             * @param  {[type]} num [数值]
             * @return {[type]}     [千分金钱格式数值]
             */
            numToMoney:function(num){
                var reg = /(\d)(?=(\d{3})+\.)/g; // 数值转化为金钱方式 正则
                num=num+"";
                num=num.indexOf(".")!=-1?(num-0).toFixed(2):(num + '.00');
                return num.replace(reg, '$1,');
            },
            /**
             * [numToMoney 数值转化为银行卡格式]
             * @param  {[type]} num [数值]
             * @return {[type]}     [银行卡格式数值]
             */
            numToBack:function(num){
                // "622909413836531119" ==> "6229 0941 3836 5311 19"
                return (num+"").replace(/(\d{4})/g,'$1 ');
            },
            /**
             * [numToMoney 数值转化为百分比格式]
             * @param  {[type]} num [数值]
             * @return {[type]}     [百分比格式数值]
             */
            numTopercent:function(num,iFix){
                var iFix = iFix||0; // 数值转化为金钱方式 正则
                return (num*100).toFixed(iFix)+"%";
            },
            /**
             * [leapNumber 数字跳跃]
             * @param  {[Elemnt]}  element [元素 可以为空]
             * @param  {[Number or Array]}  num  [类型为数字时表示跳转后的最终数字 eg:1234， 当为数组时[起始数值，最终数值]（起始数值大于最终数值 则数字向下减）eg:[1234,8]]
             * @param  {Boolean} isNumToThousands [是否执行数值转化为千分格式 eg：1234 → 1,234] 默认false,不转换
             * @param  {Boolean} isNumToMoney [是否执行数值转化为金钱方式 eg：1234 → 1,234.00] 默认false,不转换
             * @return {String} [最终值]
             */
            leapNumber: function(element, num, isNumToThousands, isNumToMoney) {
                element = element||false;
                num = num || 0;
                isNumToMoney = isNumToMoney || false;
                isNumToThousands = isNumToThousands || isNumToMoney;
                var startNum = 0; //起始值
                var endNum = 0; //最终值
                var isDrop = false; // 数字是否为递减,默认是递增
                // 如果num类型为数组
                if (_.isArray(num)) {
                    startNum = num[0];
                    endNum = num[1];
                    if (startNum == endNum) {
                        startNum = 0;
                    }
                    // 检测数字是否为递减
                    isDrop = startNum > endNum;
                } else {
                    endNum = num;
                }
                var iTimer = 30; // 帧数 毫秒单位ms
                var iTotalTimer = 600; // 跳跃总消耗时间
                var reg2 = /\.\d+/g; // 数值转化为千分格式  基于reg的改良
                var speed = speed = (endNum - startNum) / iTotalTimer * iTimer; // 数字跳转的差值
                speed = isDrop ? Math.floor(speed) : Math.ceil(speed);
                var toStr = '';
                if(element){
                    var iTunes = setInterval(function() {
                        startNum = startNum + speed;
                        if ((!isDrop && startNum >= endNum) || (isDrop && startNum <= endNum)) {
                            startNum = endNum;
                            clearInterval(iTunes);
                            iTunes = null;
                        }
                        if (isNumToThousands) {
                            toStr = $.Lang.numToMoney(startNum) ;
                            if (!isNumToMoney) {
                                toStr = toStr.replace(reg2, '');
                            }
                        } else {
                            toStr = startNum;
                        }
                        element.innerHTML = toStr;
                    }, iTimer);
                }
                if (isNumToThousands) {
                    toStr = $.Lang.numToMoney(endNum) ;
                    if (!isNumToMoney) {
                        toStr = toStr.replace(reg2, '');
                    }
                } else {
                    toStr = endNum;
                }
                return toStr;
            },
            /**
             * [autoFontSize 自适应字体size]
             * @param  {[Element]} element [元素]
             * @param  {[String]}  text    [文本]
             * @param  {[Number]}  width   [宽度]
             * @return {[Number]}         [字体size]
             */
            autoFontSize: function(element, text,width) {
                var $Element=$(element);
                var maxWidth=$Element.css("max-width");
                var reg=/[\d+\.?]+/;
                var w=0;
                width =parseInt(width||(maxWidth&&(w=maxWidth.match(reg))&&w[0])||$Element.width(),10);
                var font = $(element).css('font');
                // 针对IE 无法获取font问题
                if (!font) {
                    font = $(element).css('fontStyle') + " " + $(element).css('fontVariant') + " " + $(element).css('fontWeight') + " " + $(element).css('fontSize') + " " + $(element).css('fontFamily');
                }
                var iStrWidth = $.Lang.getCurrentStrWidth(text, font);
                var iFontSize = $(element).css('font-size').replace(/px/, '');
                var iAutoFontSize = 0;
                if (iStrWidth > width) {
                    // 最小为10px
                    iAutoFontSize =Math.max(Math.floor(width * iFontSize / iStrWidth),10);
                    $(element).css({
                        'font-size': iAutoFontSize
                    })
                } else {
                    iAutoFontSize = iFontSize;
                }
                return iAutoFontSize;
            },
            /**
             * [getCurrentStrWidth 获取字符串宽度]
             * @param  {[String]} text [字符串]
             * @param  {[Object]} font [字体类型]
             * @return {[type]}      [返回字符串width]
             */
            getCurrentStrWidth: function(text, font) {
                var currentObj = $('<span>').hide().appendTo(document.body);
                $(currentObj).html(text).css('font', font);
                var width = currentObj.width();
                currentObj.remove();
                return width;
            },
            /*
             浏览器判定
             @eg
             $.Lang.Browser().`isIE
             */
            Browser: (function() {
                var bn = (navigator.userAgent.match(/(IE|WebKit|Opera|Gecko)/) || [])[0];
                var bv = navigator.userAgent.replace(/.+(?:ox|ion|sie|ra|me)[\/:\s]([\d\.]+).*$/i, '$1')
                return {
                    isIE: bn == 'IE',
                    isIE6: bn == 'IE' && Number(bv) == 6,
                    isIE7: bn == 'IE' && Number(bv) == 7,
                    isIE8: bn == 'IE' && Number(bv) == 8,
                    isIE9: bn == 'IE' && Number(bv) == 9,
                    name: bn,
                    version: bv,
                    gecko: bn == 'Gecko',
                    webkit: bn == 'WebKit',
                    opera: bn == 'Opera'
                }
            }())
        },
        hideMsg:function(){

        },
        tipMsg:function(msg){
            if($.isObject(msg)){
                for(var i in msg){
                    msg = msg[i];
                    break;
                }
            }
            alert(msg);
        },
        /*
         创建一个类
         @eg

         */
        createClass: function(cls, base) {
            var f = function() {
                this.init.apply(this, arguments)
            };
            var a = {};
            var y = {};
            cls = $.Lang.isMethod(cls) ? (a = function() {}, a.prototype = cls.prototype, new a) : cls || a;
            base = $.Lang.isMethod(base) ? base(a.prototype || cls) : base;
            $.extend(y, cls);
            $.extend(y, base || {});
            y.init = y.$ || y.init || function() {};
            f.prototype = y;
            return f
        }
    });



    /* 对Date的扩展*/
    $.extend(Date.prototype,{
        // 对Date的扩展，将 Date 转化为指定格式的String
        // 月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
        // 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
        // 例子：
        // (new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
        // (new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
        Format:function(fmt){
            var o = {
                "M+" : this.getMonth()+1,                 //月份
                "d+" : this.getDate(),                    //日
                "h+" : this.getHours(),                   //小时
                "m+" : this.getMinutes(),                 //分
                "s+" : this.getSeconds(),                 //秒
                "q+" : Math.floor((this.getMonth()+3)/3), //季度
                "S"  : this.getMilliseconds()             //毫秒
            };
            if(/(y+)/.test(fmt))
                fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
            for(var k in o)
                if(new RegExp("("+ k +")").test(fmt))
                    fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
            return fmt;
        }
    });

    //便捷方法
    $.extend(String.prototype, {
        c: String.prototype.contains,
        e: String.prototype.escape,
        i: String.prototype.encodeURI,
        u: String.prototype.encodeURIComponent,
        f: String.prototype.format,
        t: String.prototype.trim,
        l: String.prototype.byteLen
    });

    //扩展jqueryFn
    $.extend($.fn, {
        evProx: function (obj) {
            for (var eName in obj)
                for (var selector in obj[eName])
                    $(this).on(eName, selector, obj[eName][selector]);
        }
    });

    return ST;
});