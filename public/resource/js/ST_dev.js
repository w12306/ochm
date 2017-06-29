/*
 盛天对象ST
 init       页面初始化
 getTmp     获取页面模板
 setMethod  设置页面交互行为 [需要扩展ST.todoList]
 hasObject  是否包含js对象  @eg  ST.hasObject(ST.Form)  return bool  
 getJs		获取JS对象 仅会加载一次JS对象 @eg ST.getJs("ST.Form",function(){ST.Form.test();});   p1 JS文件同对象名 p2 回执方法  用于按需加载
 JTE        javascript前端模板引擎
 tipMsg     简单提示信息
 Msgbox     消息框
 alert      提示框
 confirm    确认提示框
 ddlist     下拉菜单 select
 */
$.extend(ST, {
  ready: false,
  userIsLogined: false,
  debug: true,
  vcode: {},           //验证码组
  include: [],         //需要包含的js文件
  jsTemplates: [],		//需要包含的js模板
  todoList: function () {
  }, //需要添加的行为列表
  jtCount: [],    //需要包含的模板数组 用于确认JS模板加载完成
  jsCount: [],
  _d: new Date().getTime(),
  init: function () {
    if (ST.debug) $.log("DomReady:" + (new Date().getTime() - ST._d)); //输出打开dom加载时间

    //设置队列
    $("body").queue([]);

    //加载JS模板
    $.each(this.jsTemplates, function (i, v) {
      ST.jtCount.push(v);
      ST.getTmp({v: v, cb: function () {
        ST.jtCount.remove(v);
        ST.checkReady();
      }});
    });
    //加载JS
    $.each(this.include, function (i, v) {
      ST.jsCount.push(v);
      ST.getJs(v, function () {
        ST.jsCount.remove(v);
        ST.checkReady();
      });
    });

    if (ST.jtCount.length > 0 || ST.jsCount.length > 0) {
      ST.timer = window.setInterval(function () {
        ST.setMethod()
      }, 200);
    } else {
      ST.ready = true;
      ST.setMethod()
    }
  },
  emptyFn: function () {
  },//空方法
  debugErro: function (e, xhr, opt) {
    ST.tipMsg({error: opt + ",地址:" + e.url + "状态: " + e.status + " " + e.statusText}, 5000, true);
  },
  /*
   计算位置
   */
  _posCalculate: function (em, em1, pos, align) {
    if (!em.size()) throw new Error("em not found!");
    var x, y, l, t, a = $.getBound(em[0]), b = {w: em1.innerWidth(), h: em1.innerHeight()}, c = $.documentSize(), pos = Number(pos || 3), align = Number(align || 3), dir = pos;
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
   获取jsTemplate 
   o{
   v:string          //模板地址
   cb:function       //加载完成后的回调函数
   }
   @eg ST.getTmp({v:"test"});
   */
  getTmp: function (o) {
    var t = this;
    if (!t.__tmpCache) t.__tmpCache = {};
    if (!t.__tmp) {
      if (!$("#ST_temp").size()) {
        $("<div id='ST_temp'>").appendTo("body");
      }
      ;
      t.__tmp = $("#ST_temp");
    }
    if (t.__tmpCache[o.v]) return;
    $("body").queue(function () {
      $.get(ST.PATH.JSTMP + o.v + ".html",function (data) {
        $("body").dequeue();
        t.__tmp.append(data);
        o.cb && o.cb();
      }).error(function (e, xhr, opt) {
          $("body").dequeue();
          e.url = ST.PATH.JSTMP + o.v + ".html";
          e.data = null;
          ST.debug && ST.debugErro(e, xhr, opt);
        });
    });
  },
    /*
     设置页面hash
     */
    setHash:function(a,b){
        if(location.hash.match(new RegExp("(?:#|&)" + a + "=(.*?)(?=&|$)"))){
            location.hash = location.hash.replace(new RegExp("(?:#|&)" + a + "=(.*?)(?=&|$)"),a+"=" + b);
        }else{
            location.hash += location.hash?'&'+a+"="+b:a+"="+b;
        }
    },
    /*
     获取页面hash
     */
    getHash:function(a){
        var hash=location.hash.replace("#","");
        return (hash.match(new RegExp("(?:^|&)" + a + "=(.*?)(?=&|$)")) || ['', null])[1];
    },
  /*
   为页面初始化方法，执行todoList
   */
  setMethod: function () {
    if (!this.ready) {
      return
    } else {
      window.clearInterval(ST.timer);
      if (ST.debug) $.log("resourceReady:" + (new Date().getTime() - ST._d)); //输出打开资源加载时间
      //获取用户信息
      try {
        var userInfo = ST.Cookie.get("userInfo");
        if (!window.localData) window.localData = {};
        if (userInfo) {
          userInfo = eval("(" + decodeURIComponent(userInfo) + ")");
          window.localData.userInfo = userInfo;
        }
        ST.loginCB && ST.loginCB();
      } catch (e) {

      }
      ;
      ST.todo = ST.__todo;
      if (ST.TODOLIST) {
        for (var i = 0, l = ST.TODOLIST.length, dl; i < l, dl = ST.TODOLIST[i]; i++) {
          ST[dl.method] && ST[dl.method](dl.pars);
        }
      }
      ST.todoList();
      ST.lazyLoader();
      if (ST.debug) $.log("jsReady:" + (new Date().getTime() - ST._d)); //输出打开JS执行时间
    }
  },
  /*
   检测页面是否都以准备就绪,主要包含 js以及jstemplate的加载
   */
  checkReady: function () {
    if (ST.jtCount.length == 0 && ST.jsCount.length == 0) ST.ready = true;
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
  doLastMethod: function () {
    if (!ST.__lastfn) return;
    ST[ST.__lastfn] && ST[ST.__lastfn].apply(ST, ST.__lastarg);
  },
  /*
   顺序执行方法
   @desc
   func 需要顺序执行的方法数组
   @eg
   ST._ProcessFun([fun1,fun2]);
   */
  _ProcessFun: function (func) {
    var ms = 20;
    setTimeout(function () {
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
  lazyLoader: function (a) {
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
      timer,
      listener,
      o = {
        tag: a.tag || "img",
        attr: a.attr || "lazy_src",
        lazy_method: a.lazy_method ||
          function () {
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
                window.setTimeout(function () {
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
              window.setTimeout(function () {
                loadNext();
              }, 17);
            }
          }
      }
    var els = $(o.tag + "[lazy_method]," + o.tag + "[lazy_src]"),
      lazy_method = o.lazy_method;
    l = els.length;
    var loadNext = function () {
      idx++;
      lazy_method();
    };
    var listenerHandle = function () {
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
    $(window).bind("scroll.laz", function () {
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
   快速构造一个tipMsg
   @desc
   msg:(string,object)    	   提示信息*
   hideDelay:(num)      自动消失的时间 (单位毫秒)
   mask:(bool)          是否显示遮罩
   @eg
   ST.tipMsg("弹出提示",3000,1); 
   弹出一个具备背景遮罩的"弹出提示框"3秒后消失隐藏
   */
  tipMsg: function (msg, hideDelay, mask) {
    if (!this.$tip) this.$tip = new $.Widget.Tip();
    this.$tip.show(msg, mask);
    if (hideDelay != 0) this.$tip.hideAfter(hideDelay);
    return this.$tip;
  },
  hideMsg: function (flag) {
    if (this.$tip) this.$tip.hide(flag);
  },
    /*
     快速构造一个noticeMsg
     @desc
        ops 详见noticebox show方法参数说明
     @eg
     ST.noticeMsg({title:"标题",content:"<p>内容</p><p>内容</p>",hideDelay:5000,mask:1});
     弹出一个具备背景遮罩的"多行HTML提示框" 5秒后自动隐藏
     */
  noticeMsg:function(ops){
    if(!this.$notice) this.$notice = new $.Widget.Noticebox();
    this.$notice.show(ops);
    return this.$notice;
  },
  /*
   快速构造一个msgbox
   @desc
   ops:(object)            //msgbox的参数选择,详见$.Widget.Msgbox
   {
   title             //标题
   content           //内容
   url               //链接
   havaSpace         //是否具有空白
   hideBottom        //隐藏底部按钮
   }
   btns:(array)            //按钮数组 数组内容为对象 {text:"确定",fun:function(){}}
   w:(num)                 //宽度
   h:(num)                 //高度
   mk:(bool)               //遮罩 默认为falsh
   @eg
   ST.msgbox({url:"http://www.baidu.com"},[],400,300); 
   //一个链接到百度的弹出框
   ST.msgbox({title:"确认提示",content"确认要这样做吗?"},[{text:ST.LRes.ok,fun:okfn}],400,300,true);
   //弹出一个备背景遮罩的"弹出确认框"
   */
  msgbox: function (ops, btns, w, h, mk) {
    var mb = new $.Widget.Msgbox();
    mb.show(ops, btns, w, h, mk);
    return mb;
  },
  /*
   快速构造一个下拉容器
   o
   {
   id:
   data:
   template:o.template ||"common_dc",
   selFn:
   pos: ||1
   align: ||2
   hold: ||false
   trigger:click ||click
   }
   */
  dc: function (o) {
    var t = this, dc = new $.Widget.DropContainer(o.id, o.trigger || "click");
    dc.template = o.template || "common_dc",
      dc.setting.hold = o.hold || false;
    dc.setting.pos = o.pos || 3;
    dc.setting.align = o.align || 2;
    dc.onSelected = function (m, e) {
      $.Lang.isMethod(o.selFn) && o.selFn(m, e);
    };
    o.data && dc.changeData(o.data);
    return dc;
  },
    /*
     快速构造一个提示
     o
     {
     id:
     data:
     template: ||"common_tooltip",
     trigger: 'click' || "mouseover",
     hold:false
     pos: ||1
     align: ||2
     hasdir: ||0
     }
     */
    toolTip: function (o) {
        var t = this, tt;
        if (o.id) {
            tt = new $.Widget.DropContainer(o.id, o.trigger || "mouseover");
        } else {
            o.id = $.getUid();
            $("<div id='" + o.id + "'>").css("display", "none").appendTo("body");
            tt = new $.Widget.DropContainer(o.id,  o.trigger || "mouseover");
        }
        tt.template = o.template|| "common_tooltip";
        tt.setting.pos = o.pos || 1;
        tt.setting.align = o.align || 2;
        tt.setting.hold = o.hold || 0;
        tt.setting.hasdir = o.hasdir || 0;
        tt.changePos = function (node) {
            tt.Jid = $(node);
            tt.show();
        };
        tt.changeData(o.data || $("#" + o.id).data("tooltip"));

        return tt;
    },
  /*
   构造一个alert
   @desc
   c:string                //内容* 
   tle:string              //标题
   okfn:function           //ok按钮的执行方法
   w:(num)                 //宽度
   h:(num)                 //高度
   @eg
   ST.alert("我是提示框!"); 
   */
  alert: function (c, tle, okfn, w, h) {
    var t = this, mb = t.msgbox({content: c, title: tle || ST.LRes.tip, haveSpace: true}, [
      {text: ST.LRes.ok, fun: okfn}
    ], w, h, true);
    return mb;
  },
  /*
   构造一个confirm
   @desc
   c:string                //内容*
   tle:string              //标题
   okfn:function           //ok按钮的执行方法
   clfn:function           //取消按钮的执行方法
   w:(num)                 //宽度
   h:(num)                 //高度
   @eg
   ST.confirm("我是确认提示框!"); 
   */
  confirm: function (c, tle, okfn, clfn, w, h) {
    var t = this;
    mb = t.msgbox({content: c, title: tle || ST.LRes.delTip, haveSpace: true}, [
      {text: ST.LRes.ok, fun: okfn},
      {text: ST.LRes.cancle, fun: clfn}
    ], w, h, true);
    t.$mb = mb;
    return mb;
  },
  /*
   快速构造一个下拉框
   @desc
   id:string                //用于构造下拉框的ID *
   data:Array               //用于构造下拉框的数据 *  @eg [{text:"一",value:"1"}]
   selFn:function           //选中后的回调  *  return (o,e)
   h:num                    //下拉显示的数量   默认为6
   @eg
   var types=[{text:"一",value:1},{text:"二",value:2},{text:"三",value:3}];
   var dd = ST.ddList('sortType', types,function(o){
   //ST.tipMsg("你选择了"+o.text);//o.text  o.value
   });
   */
  ddList: function (id, data, selFn, h) {
    var t = this, ddl = new $.Widget.DropDownList(id, h);
    ddl.onselected = function (o, e) {
      $.Lang.isMethod(selFn) && selFn(o, e);
    };
    ddl.changeData(data);
    return ddl;
  },
  /*
   是否存在制定的对象
   @desc
   n:string                //对象名* @eg   ST.Verify
   @eg
   ST.hasObject("ST.Verify"); 
   检查是否存在 ST.Verify对象 常用于 对象检查，异步加载JS
   */
  hasObject: function (n) {
    var w = window, t = this;
    $.each($.Lang.toArray(n, '.'), function (i, v) {
      v.replace(/_\w*$/,"");
      if (!w[v]) {
        w = null;
        return false;
      }
      w = w[v];
    });
    return w;
  },
  /*
  	获取JS列表 顺序加载
  */
	getJsList:function(a,succ,fail){
		var t=this,arr=[],n;
		if(!$.Lang.isArray(a)) return false;
		var fn=function(){
			if(a.length){
				n=a.shift();
				ST.getJs(n,function(){
					fn();
				},function(){
					arr.push(n);
					fn();
				})			
			}else{
				if(a.length) {
					fail&&fail(arr);
				}else{
					succ&&succ();
				}
			}
		}
		fn();
		
	},
    /*
     获取JS对象 仅会加载一次JS对象
     @desc
     n:string                //对象名* @ps   这里文件名同对象名
     cb:function             //加载成后回调
     @eg
     ST.hasObject("ST.Verify");
     检查是否存在 ST.Verify对象 常用于 对象检查，异步加载JS
     */
    getJs:function(n,succ,fail){
        var t=this,a;
        a=n.c(/^http:\/\//);
        if(!a && t.hasObject(n)){
            succ&&succ();
        }else{
            n = (a?n:ST.PATH.JS+n)+".js";
            $.ajax({
                url:n,
                dataType: 'script',
                cache:true,
                success: function(){
                    succ&&succ();
                },
                error:function(e,xhr,opt){
                    e.url=n;
                    e.data=null;
                    ST.debug&&ST.debugErro(e,xhr,opt);
                    fail&&fail();
                }
            });
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
  serObj: function (f) {
    var a = {};
    $(f).find("*[name]").each(function () {
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
  serForm: function (f, s) {
    var data = {}, a = [], b, t;
    if (s) a = {};
    $(f).find("*[name]").each(function () {
      if (this.type && this.type.c(/radio|checkbox/i) && !this.checked) return true;
      b = $(this).val();
      if ($.Lang.isArray(b)) {
        t = this;
        $.each(b, function (i, v) {
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
  postForm: function (o) {
    var t = this, f = $(o.f), datatype;
    if (f.size() == 0) return;
    datatype = f.attr("isJsonP") ? "jsonp" : ""
    ST.getJSON(f.attr('action') || location.href, t.serForm(o.f), function (json) {
      if (json) {
        o.succ && o.succ(json);
      }
    }, function (e) {
      o.error && o.error(e);
      ST.CurVode && ST.CurVode.scode();//刷新验证码
    }, "post", datatype);
    return false;
  },
  /*
   获取错误描述信息
   @e {Object} 错误代码或消息
   */
  getED: function (e, l) {
    if ($.getType(e) == 'error') e = e.message;
    e = e + '';
    l = ST.LRes;
    return (l[e] || e);
  },
    //hack方法 ,勿用,仅支持一级
    O2S:function(o){
        var a=[];
        for(var i in o){
            a.push(i+"="+o[i]);
        }
        return a.join("&");
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
  getJSON: function (url, data, sfn, errfn, method, datatype) {
      var _data;
      if($.Lang.isObject(data)){
          _data = $.extend(data,ST.AJAXDATA);
      }else if($.Lang.isString(data)){
          var str=ST.O2S(ST.AJAXDATA);
          _data = data?data+"&"+str:str;
      }
    $("body").queue(function () {
      $.ajax({
        type: method || "get",
        dataType: datatype || 'json',
        contentType: 'application/x-www-form-urlencoded;charset=utf-8',
        url: url,
        data: _data || "",
        error: function (e, xhr, opt) {
          ST.hideMsg();
          $("body").dequeue();
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
        success: function (j) {
          ST.hideMsg();
          $("body").dequeue();
          if (!j) ST.debug && ST.tipMsg("no value has returned!")
          var s = j.status || j.state, flag = false;
          switch (s.toLowerCase()) {
            case "notice_login":
              ST.login(j.message || "", "", function () {
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
            case "tip":
              ST.tipMsg(j.info || j.message || ST.LRes.opFailed, 2000);
              ;
              break;
            case "tip_alert":
              ST.alert(j.info || j.message || ST.LRes.opFailed);
              break;
            case "vcode":
              ST._tipVcode && ST._tipVcode(j);
              break;
            case "tip_success":
              ST.tipMsg({success: j.info || j.message || ST.LRes.opSuccess}, 2000);
              flag = true;
              break;
            case "tip_error":
              ST.tipMsg({error: j.info || j.message || ST.LRes.opFailed}, 2000, true);
              break;
            case "notice_success":
              ST.noticeMsg({type:"success",title:ST.LRes.opSuccess,content:j.info || j.message});
                flag = true;
                break;
            case "notice_error":
              ST.noticeMsg({type:"error",title:ST.LRes.opFailed,content:j.info || j.message});
            break;
            case "notice_confirm":
              ST.confirm(j.info || j.message || ST.LRes.opFailed, ST.LRes.delTip, [
                {text: ST.LRes.ok, fun: function () {
                  sfn && sfn(j)
                }},
                {text: ST.LRes.cancle, fun: function () {
                }}
              ]);
              break;
          }
          if (flag) {
            sfn && sfn(j);
          } else {
            errfn && errfn(j);
          }
        }
      });
    });
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
  reload: function (time, url) {
    window.setTimeout(function () {
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
  limit: function (cid, lmt, sid, flag) {
    var v = $("#" + cid).val(), l = v.l();
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
  limitLn: function (cid, lmt, sid, flag) {
    var t = this, fn = function () {
      t.limit(cid, lmt, sid, flag);
    };
    document.getElementById(cid).onpropertychange = fn;
    document.getElementById(cid).oninput = fn;
    fn();
  },
  /*
   添加一个区域公告
   @desc
   msg 公告信息
   el  指定的ID位置  默认为body
   @eg
   ST.notice("我是一个公告!","#form1");
   在form1头部加入了一条公告信息
   */
  notice: function (msg, el) {
    el = el || $("body");
    var sid = $.getUid(), notice = ST.JTE.fetch("common_notice").getFilled({
      sid: sid,
      msg: msg
    });
    $(el).prepend(notice);
    notice = $("#" + sid)
    notice.find(".infclose").bind("click", function () {
      notice.fadeOut();
    });
    return notice;
  },
  /*
   登录后的回调方法
   */
  __saveloginState: false, //保存登陆状态
  _loginCB: function (j) {
    if (j && j.data && j.data.userInfo) {
      ST.hideMsg();
      //保存cookie
      if (ST.__saveloginState)
        ST.Cookie.set("userInfo", j.data.userInfo, 336); //336小时

      if (!window.localData) window.localData = {};
      window.localData.userInfo = j.data.userInfo;      //保存用户信息
      if (ST.loginCB) ST.loginCB();             //保存页面登陆回调方法 

      if ($.Lang.isMethod(ST.continueMethod)) {
        ST.continueMethod();
        ST.continueMethod = ""; //注销继续执行的方法
      }
      ST.loginmb && ST.loginmb.hide();
    } else {
      //ST.hideMsg();
      ST.loginmb && ST.loginmb.hide();
      window.setTimeout(function () {
        ST.reload();
      }, 1000);
    }
  },
  login: function (msg, needcode, cb) {
    if (!ST.loginmb) {
      ST.loginmb = new $.Widget.Msgbox();
    }
    //保存登陆回调方法
    if (cb) ST.continueMethod = cb;
    ST.loginmb.show({
      title: "登录",
      content: ST.JTE.fetch("common_login").getFilled({msg: msg, needCode: needcode || false})
    }, [], 330, 200, true);
    ST.loginmb.onclose = function () {
      if (ST.continueMethod)  ST.continueMethod = ""; //注销继续执行的方法
      ST.CurVode && ST.CurVode.hide();    //隐藏验证码
    };
    if (needcode) {
      ST.showLoginVcode(); //显示验证码
    }
    ST.Verify.addVform("loginForm");
  },
  showLoginVcode: function () {
    ST.getJs("ST.Vcode", function () {
      $("#login_vcode_area").css("visibility", "visible");
      $("#loginForm_vcode").attr({
        placeholder: "请输入验证码",
        opt: "rq ml",
        ml: "4-4",
        emsg: "请输入验证码 请输入4位验证码"
      });
      ST.CurVode = new ST.Vcode({controlID: "loginForm_vcode"});
    });
  },
  logOut: function () {
    ST.Cookie.del("userInfo");
    this.getJSON(ST.PATH.ROOT + ST.PATH.LOGOUT, "", function (j) {
      ST.tipMsg({success: j.info || j.message || ST.LRes.logOut}, 3000, !0);
      window.setTimeout(function () {
        if (j.url) {
          location.href = j.url;
        } else {
          ST.reload();
        }
      }, 2000);
    });
  },
  /*
   滚动到制定区域
   @desc
   el 		滚动到的元素
   s       滚动时间 //默认800号码 小于50毫秒内则使用scrollIntoView
   @eg
   ST.Scrollto("id")
   */
  Scrollto: function (el, s) {
    var em = document.getElementById(el);
    if (!em) return;
    if (s && (s | 0) < 50) {
      em.scrollIntoView();//滚动到可视区域
      $("#bottom_top").hide();
      return false;
    }
    var z = this;
    z.o = s || 800;
    z.p = $.getBound(el);
    z.s = $.documentSize();
    z.clear = function () {
      clearInterval(z.timer);
      z.timer = null
    };
    z.t = (new Date).getTime();
    $("#bottom_top").hide();
    z.step = function () {
      var t = (new Date).getTime();
      var p = (t - z.t) / z.o;
      if (t >= z.o + z.t) {
        z.clear();
        setTimeout(function () {
          z.scroll(z.p.y);
        }, 13);
      } else {
        st = ((-Math.cos(p * Math.PI) / 2) + 0.5) * (z.p.y - z.s.scrollTop) + z.s.scrollTop;
        z.scroll(st);
      }
    };
    z.scroll = function (t) {
      window.scrollTo(0, t)
    };
    z.timer = setInterval(function () {
      z.step();
    }, 13);
  },
  inputFocus: function (inputObj) {
    if (inputObj.get(0)) {
      inputObj.focus();
      ST.setCursorPosition(inputObj.get(0), inputObj.val().length);
    }
  },
  setCursorPosition: function (obj, pos) {
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
  //对象转化为字符串
  objTostr: function (o) {
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
        if (!!document.all && !/^\n?function\s*toString\(\)\s*\{\n?\s*\[native code\]\n?\s*\}\n?\s*$/.test(o.toString)) {
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
  AddFavorite: function (url, title) {
    try {
      window.external.addFavorite(url, title);
    }
    catch (e) {
      try {
        window.sidebar.addPanel(title, url, "");
      }
      catch (e) {
        alert("加入收藏失败，请使用Ctrl+D进行添加");
      }
    }
  },
  /*设为首页*/
  SetHome: function (obj, url) {
    try {
      obj.style.behavior = 'url(#default#homepage)';
      obj.setHomePage(url);
    }
    catch (e) {
      if (window.netscape) {
        try {
          netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        }
        catch (e) {
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
    encodeHtml: function (s) {
      s = (s != undefined) ? s : this;
      return (typeof s != "string") ? s : s.replace(this.REGX_HTML_ENCODE, function ($0) {
        var c = $0.charCodeAt(0), r = ["&#"];
        c = (c == 0x20) ? 0xA0 : c;
        r.push(c);
        r.push(";");
        return r.join("");
      });
    },
    decodeHtml: function (s) {
      var HTML_DECODE = this.HTML_DECODE,
        REGX_NUM = this.REGX_ENTITY_NUM;
      s = (s != undefined) ? s : this;
      return (typeof s != "string") ? s : s.replace(this.REGX_HTML_DECODE, function ($0) {
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
  JTE: (function () {
    var w, y, _y, p = function (f, k, j) {
        for (j = 0; k--;) if (f.charAt(k) == '\\') j++;
        else break;
        return j % 2
      }, cache = {},
      q = function (f) {
        return f.replace(/\\/g, '\\\\').replace(/\"/g, '\\"');
      },
      v = function (a, b) {
        a = a.substr(0, b).replace(/\\{2}/g, '').replace(/\\[\w\W]/g, '');
        return (a.match(/\[/g) || []).length == (a.match(/]/g) || []).length
      },
      n = function (f, k, j) {
        for (var m = -1,
               s = f.length,
               i = [], g, h, l; m++ < s - 1;) {
          h = f.charAt(m);
          if (h == '/' && !g && !p(f, m) && v(f, m)) l = !l;
          else if ((h == '\'' || h == '"') && !l && !g) g = h;
          else if (h == g && !l && !p(f, m)) g = null;
          g || i.push(h)
        }
        if (j) return g;
        i = i.join('');
        if (k) return i;
        return (i.match(/{/g) || []).length == (i.match(/}/g) || []).length
      },
      z = function (a, s) {
        var c,
          e = [],
          d,
          o,
          r = function (f) {
            //o=c;
            for (; ;) if (n(d)) {
              e.push(f ? 'Pft$.push(' + d + ');' : d.replace(/^call\b/, '') + (d.contains(/;$/) ? '' : ';'));
              break
            } else {
              c = a.indexOf('}', c + 1);
              if (c == -1) throw new Error('error near:' + d);
              d = a.slice(1, c)
            }
          };
        for (e.push('var Pft$=[];with(' + s.key + '){'); ;) {
          c = a.indexOf('{');
          if (c != -1) {
            d = a.slice(0, c).replace(/\+{/, '');
            d.length && e.push('Pft$.push("' + q(d) + '");');
            if (c > 0) o = a.charAt(c - 1);
            if (o == '\\' && p(a, c)) {
              e.push('Pft$.push("{");');
              a = a.substr(c + 1);
              continue
            }
            a = a.substr(c);
            c = a.indexOf('}');
            if (c == -1) break;
            else {
              for (d = a.slice(1, c).trim(); ;) if (n(d, 0, 1)) {
                c = a.indexOf('}', c + 1);
                if (c == -1) break;
                d = a.slice(1, c).trim()
              } else break;
              if (d) if (d.contains(/^(?:for|if|while|try)\b/)) e.push(d + '{');
              else if (d.contains(/^\/(?:for|if|while|try)\b/)) e.push('}');
              else if (d.contains(/^(?:else|catch|finally)\b/)) e.push('}' + d + '{');
              else d.contains(/^(?:continue|break|return|throw|var|call)\b/) || n(d, 1).contains(/[^=!><]=[^=]/) ? r() : r(1);
              a = a.substr(c + 1)
            }
          } else break;

        }
        a && e.push('Pft$.push("' + q(a) + '");');
        e.push('}return Pft$.join("")');
        return Function(s.key, e.join(''));
      },
      x = function (a, b, e, r) {
        e = $.Lang.isArray(a) ? {
          array: a
        } : a;
        try {
          r = cache[_y] ? cache[_y] : cache[_y] = z(y || w, b); //.replace(/\\([{}])/g, '$1');	
          return r(a);
        } catch (t) {
          return ST.debug ? b.onError(t, []) : "数据格式错误!";
        }
      };
    return {
      using: function (a, c) {
        w = ($("#" + a).size() != 0) ? $("#" + a).html() : a;
        w = (w + '').replace(/\s+/g, ' ');
        if (c) this.fetch(c);
        return this
      },
      getString: function () {
        return y || w
      },
      fetch: function (a, c) {
        if (!w)this.using('ST_temp');
        if (w) c = w.match(new RegExp('{' + a + '}([\\s\\S]*?){/' + a + '}'));
        if (!c) throw new Error('no tpl blk:' + a);
        y = c[1];
        _y = a;
        return this
      },
      delTemp: function () {
        y = '';
        w = '';
        return this
      },
      getFilled: function (a) {
        a = a || {};
        return x(a, this)
      },
      toFill: function (a, b, c) {
        c = this;
        $.each($.Lang.isArray(a) ? a : [a], function (i, v) {
          v = (v.jquery) ? v : $("#" + v);
          if (v.size() != 0) $(v).html(c.getFilled(b));
        });
        //do initComponent
        return c;
      },
      onError: function (a, b) {
        //ST.tipMsg("操作异常,请刷新重试!");
        return ST.debug ? ('error:' + (a.message || a) + ';') : "";
      },
      key: 'context'
    }
  }())
});


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
  trim: function (reg) {
    return this.replace(reg || /^[\s\xa0\u3000\uFEFF]+|[\s\xa0\u3000\uFEFF]+$/g, '');
  },
  /*
   获取文字字节长度
   @eg
   "中文aaab".byteLen();
   */
  byteLen: function () {
    return this.replace(/[^\x00-\xff]/g, '**').length;
  },
  /*
   是否包含某个字符串|正则|字符
   @eg
   "我得aaab".contains("c");
   */
  contains: function (str) {
    var r = RegExp;
    var p = str;
    if (!$.Lang.is(str, r))p = new r((p + '').replace(/([?|.{}\\()+\-*\/^$\[\]])/g, '\\$1'));
    return p.test(this);
  },
  escape: function (flag) {
    return window[(flag ? 'un' : '') + 'escape'](this);
  },
  encodeURI: function (flag) {
    return window[(flag ? 'de' : 'en') + 'codeURI'](this)
  },
  encodeURIComponent: function (flag) {
    return window[(flag ? 'de' : 'en') + 'codeURIComponent'](this)
  },
  /*
   格式化字符串
   @eg "哈哈哈{0}1{1}".format("呵呵","gaga");
   */
  format: function () {
    var s = this, a = arguments;
    if (s)
      s = s.replace(/{(\d+)}/g, function (b, c) {
        return a[c]
      });
    return s
  },
  /*
   增强的replace功能
   */
  r: function (p, v, s, b) {
    s = this;
    b = $.Lang.isArray(v);
    if ($.Lang.isArray(p)) while (p.length) s = s.replace(p.shift(), b && v.length ? v.shift() : '');
    else s = s.replace(p, $.Lang.isUndefined(v) ? '' : v);
    return s
  },
  /*
   用0补全位数：
   @eg:
   ST.prefixInteger(5,2);  // 05
   */
  prefixInteger: function (num, length) {
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
  getDateFromFormat: function (format) {
    var date, result = {year: 0, month: 0, day: 0};//当然这里可以默认1970-1-1日
    if (date = this) {
      format.replace(/y+|Y+|M+|d+|D+/g, function (m, a, b, c) {//这里只做了年月日  加时分秒也是可以的
        date.substring(a).replace(/\d+/, function (d) {
          c = parseInt(d, 10)
        });
        if (/y+/i.test(m) && !result.year)result.year = c;
        if (/M+/.test(m) && !result.month)result.month = c;
        if (/d+/i.test(m) && !result.day)result.day = c;
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
  getJson: function (jsonName, jsonValue) {
    if (!this)
      return false;
    var tm = $.grep(this, function (n, i) {
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
  fSelect: (function () {
    var __proto = this;
    var __tmpl = function (_list) {
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
      /@/g, '_e.',	// 用 @ 访问子元素属性
      /<>/g, '!=',	// 可以用 <> 代替 !=
      /AND/gi, '&&',	// 可以用 AND 代替 &&
      /OR/gi, '||',	// 可以用 OR 代替 ||
      /NOT/gi, '!',	// 可以用 NOT 代替 !
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
    return function (exp) {
      if (!exp)
        return [];
      var fn = __cache[exp];
      try {
        if (!fn) {
          var code = __interpret(exp);			//解释表达式
          code = __tmpl.replace('$C', code);		//应用到模版
          fn = __cache[exp] = __compile(code);	//实例化函数
        }
        return fn(this);							//查询当前对象
      }
      catch (e) {
        return [];
      }
    }
  })(),
  /*
   在Array数组中插入 指定的位置idx 插入对象object 
   @eg
   [{id:1,name:"一"},{id:2,name:"二"}].insert({id:3,name:""},1)
   */
  insert: function (obj, idx) {
    var arr = this;
    if ($.Lang.isArray(arr)) arr.splice(idx == 0 ? 0 : idx || arr.length, 0, obj);
    return arr;
  },
  /*
   在Array数组中 获取指定name,val的位置
   @eg
   [{id:1,name:"一"},{id:2,name:"二"}].getJsonIndex("id",1); 
   */
  getJsonIndex: function (jsonName, jsonValue) {
    var arr = this, i = -1;
    $.each(arr, function (idx, v) {
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
  removeJson: function (jsonName, jsonValue) {
    var arr = this, result, i = -1;
    if (this.length == 0) return i;
    $.each(arr, function (idx, v) {
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
  getIndex: function (obj) {
    var arr = this, i = -1;
    $.each(arr, function (idx, v) {
      if ($.Lang.arrEqual(v, obj)) {
        i = idx;
        return false;
      }
    });
    return i
  },
  /*
   在Array数组中移出指定对象
   @eg
   ["a","b"].remove("a"); 
   */
  remove: function (obj) {
    var arr = this, i = this.getIndex(obj);
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
  log: function () {
    window.console && console.log(arguments[0]);
  },
  /*
   获取唯一的时间戳
   @eg
   $.getUid();
   @return
   "ST1293219328193"
   */
  getUid: function () {
    return 'ST' + ($._d++);
  },
  /*
   获取对象类型
   @eg
   var a={a:"123"};
   $.getType(a);
   */
  getType: function (obj) {
    var type;
    return (type = typeof(obj)) == 'object' ? obj == null && 'null' || Object.prototype.toString.call(obj).slice(8, -1).toLowerCase() : type;
  },
  /*
   替换HTML标记 转化为text 
   @eg
   $.toText("<span>test</span>");
   */
  toText: function (s) {
    return(s + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/\s/g, '&nbsp;').replace(/'/g, '&#039;').replace(/"/g, '&quot;');
  },
  /*	
   根据模块生成一个 URL地址 
   @eg
   $.genUrl("diancan");
   */
  genUrl: function (a, b) {
    var Path = ST.PATH;
    return Path.ROOT + '/' + a.replace(/[&=]/g, Path.P) + (Path.U ? Path.P + Path.U : '') + (Path.SUFFIX ? Path.SUFFIX : '');
  },
  /*	
   取消事件冒泡，阻止默认事件
   @eg
   $.stopEvent(e);
   */
  stopEvent: function (e) {
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
  isMouseLeaveOrEnter: function (e, handler) {
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
  documentSize: function (d) {
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
  getBound: function (el, a) {
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
      } else for (; p && p != a; l += p.offsetLeft || 0, t += p.offsetTop || 0, p = p.offsetParent) {
      }
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
    isString: function (obj) {
      return $.getType(obj) == 'string';
    },
    /*	
     是否是方法
     @eg
     $.Lang.isMethod("test");
     */
    isMethod: function (obj) {
      return $.getType(obj) == 'function';
    },
    /*	
     是否是字符串
     @eg
     $.Lang.isArray("test");
     */
    isArray: function (obj) {
      return $.getType(obj) == 'array';
    },
    isUndefined: function (obj) {
      return $.getType(obj) == 'undefined';
    },
    isNumber: function (obj) {
      return $.getType(obj) == 'number';
    },
    isObject: function (obj) {
      return $.getType(obj) == 'object';
    },
    /*	
     是否是指定类型
     @eg
     $.Lang.is("test","string");
     */
    is: function (test, aim) {
      var result;
      try {
        result = (aim == 'string' || $.Lang.isString(aim)) ? $.getType(test) == aim : test instanceof aim
      } catch (e) {
      }
      return !!result;
    },
    /*	
     参数转化为数组
     @eg
     $.Lang.toArray("123,456,789",",");
     */
    toArray: function (args, split) {
      if (!arguments.length)return[];
      if (!args || this.isString(args) || this.isUndefined(args.length)) {
        return(args + '').split(split ? split : '');
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
    arrEqual: function (a1, a2) {
      var l;
      if ($.Lang.isArray(a1) && $.Lang.isArray(a2) && (l = a1.length) == a2.length) {
        for (var i = 0; i < l; i++)if (!$.Lang.arrEqual(a1[i], a2[i]))return false;
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
    bind: function (fn, obj) {
      var a = $.Lang.toArray(arguments), b = a.splice(0, 2);
      return obj ? function () {
        fn.apply(obj, a)
      } : fn;
    },
    /*
     转化为Json格式字符串
     */
    toJson: function (s) {
      try {
        return (new Function("", "return " + s))();
      } catch (e) {
        return{status: 'tip_error', message: ST.LRes.serverBusy}
      }
    },
    /*	
     浏览器判定
     @eg
     $.Lang.Browser().`isIE
     */
    Browser: (function () {
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
  /*	
   创建一个类
   @eg

   */
  createClass: function (cls, base) {
    var f = function () {
      this.init.apply(this, arguments)
    };
    var a = {};
    var y = {};
    cls = $.Lang.isMethod(cls) ? (a = function () {
    }, a.prototype = cls.prototype, new a) : cls || a;
    base = $.Lang.isMethod(base) ? base(a.prototype || cls) : base;
    $.extend(y, cls);
    $.extend(y, base || {});
    y.init = y.$ || y.init || function () {
    };
    f.prototype = y;
    return f
  }
});

/*
 widget相关扩展
 DropContainer:
 @methods
 {
 show
 hide
 changeData
 }
 事件接口
 onselected   //选中
 onOver       //移上容器
 onOut        //移出容器
 -----------------
 Control:
 @methods
 {
 show
 hide
 showMask
 hideMask
 setZIdx
 setOpacity
 getZIdx
 hideAfter
 }
 @eg 
 无
 */
$.extend({
   Coms:$.createClass({},{
        isEnable:true,      //是否禁用
        isShown:true,     //是否显示
        init:function(id,ops){
            var t=this;
            t.Jid = $("#"+id); //持有节点
            //命令模式
            t.Jid.bind("click",function(e){
                if(!t.isEnable) return;
                var em=e.target,a=this,cmd;
                //包含a标签
                if(em&&em.tagName.contains(/^(a|input|button)$/i)||(em=em.parentNode,em.tagName.contains(/^(a|input|button)$/i))){
                    cmd = $(em).data("cmd");
                    pars = $(em).data("pars");
                    t[cmd] && t[cmd](pars);
                }
            });
        },
        enable:function(){
            this.isEnable=true;
        },
        disable:function(){
            this.isEnable=false;
        },
        show:function(){
            this.Jid.show();
            this.isShown = true;
        },
        hide:function(){
            this.Jid.hide();
            this.isShown = false;
        }
  }),
   /*
  //插件基类 依赖于多个组件,管理多个组件的方法(未完成)
  Plugin:$.createClass({},{
      //依赖列表
      require:[],
      //组件列表
      coms:[],
      init:function(){
          var t = this;
          ST.getJsList(t.require,function(){
              t.initPage();
          },function(){
              alert(ST.LRes.RequireFail);
          });
          //需检测依赖,后面补充
          return t;
      },
      //添加组件
      addCom:
      //删除组件
      delCom:
      //销毁插件
      dispose:
  }),
  */
  Widget: {
    DropContainer: $.createClass({}, {
      isShown: false,
      template: "common_dc",
      //配置不能为对象 
      init: function (id, trigger) {
        var t = this;
        t.setting = {
          pos: 3,    		//位置 
          align: 2,  		//对齐
          hold: false,		//固定显示
          hasdir: false    //是否有三角
        };
        t.cid = id;
        t.Jid = $("#" + id);
        t._hide = $.Lang.bind(t.hide, t);
        t._show = $.Lang.bind(t.show, t);
        t.trigger = trigger;
        switch (trigger) {
          case "click":
            t.Jid.bind(trigger || 'click', t._show);
            break;
          case "mouseover":
            t.Jid.bind("mouseover", function () {
              t.timer && window.clearTimeout(t.timer);
              t._show();
            })
            break;
        }
        return t;
      },
        show:function(){
            var t=this,id=t.cid+'_dcnt',d,s=t.setting;
            t.isShown=true;
            t.timer && window.clearTimeout(t.timer);
            if(d=$("#"+id),d.size()>0){
                if(!t.isInitEvent){
                    t._initEvent();
                }
                d.show();
            }else{
                t._sdivId=id;
                d=$("<div id="+id+">").css({position:'absolute',zIndex:t._$zIdx||20000,left:"-2000px",top:"0px"}).appendTo("body");
                t._fillData();
                t._initEvent();
                t._initPos();
            }
            if(!s.hold) $(document).unbind("mousedown.dc"+ t.cid).bind("mousedown.dc"+ t.cid,function(e){
                t.timer && window.clearTimeout(t.timer);
                t.timer = window.setTimeout(function(){
                    $(document).unbind("mousedown.dc"+ t.cid);
                    t._hide();
                },200);
            });
            $(window).bind("resize.dc"+ t.cid,$.Lang.bind(t._initPos,t));
        },
        _initEvent:function(){
            var t=this,d,s=t.setting;
            d=$("#"+t.cid+'_dcnt');
            d.unbind().bind('mousedown',$.stopEvent).bind('click',function(e){
                $.stopEvent();
                t.onSelected&&t.selected(e);
            });
            t.Jid.unbind("mouseout");//移除事件绑定
            //绑定关闭按钮
            if(t.trigger=="mouseover"){
                d.bind("mouseover",function(e){
                    if($.isMouseLeaveOrEnter(e,this)){
                        t.timer&&window.clearTimeout(t.timer);
                    }
                });
                //是否固定显示
                if(!s.hold){
                    t.Jid.bind("mouseout",function(){
                        t.timer&&window.clearTimeout(t.timer);
                        t.timer=window.setTimeout(t._hide,300);
                    });
                    d.bind("mouseout",function(e){
                        if($.isMouseLeaveOrEnter(e,this)){
                            t.timer=window.setTimeout(t._hide,100);
                            $(document).unbind("mousedown.dc")
                        }
                    });
                }
            }
            if(d=$("#st_tipbox_"+t.cid+"_close"),d.size()>0){
                d.unbind("click").bind("click",t._hide);
                $(document).unbind("mousedown.dc");
                $(document)[(s.hold)?"unbind":"bind"]("mousedown.dc",t._hide);
            }
            t.isInitEvent=true;
        },
        _initPos:function(){
            var t=this,s=t.setting,pos,d=$("#"+t.cid+'_dcnt'),a;
            if(t.Jid.size()<1 || !t.isFlllData) return;
            var pos=ST._posCalculate(t.Jid,d,s.pos,s.align);
            if(t.setting.hasdir){
                a=$("#st_tipbox_"+t.cid+"_dir");
                if(!a.hasClass("common_tipbox-dir"+pos.dir)) a.addClass("common_tipbox-dir"+pos.dir);
                switch(pos.dir){
                    case 1:
                    case 3:
                        a.css("left",pos.l);
                    break;
                    case 2:
                    case 4:
                        a.css("top",pos.t);
                    break;
                }
            }
            d.css({left:pos.x+'px',top:pos.y+'px'});
            //d.css({left:pos.x+'px',top:pos.y+'px',width:pos.w+'px'})
        },
        _fillData:function(){
            var t=this,id=t.cid+'_dcnt',s=t.setting;
            if($("#"+id).size()>0){
                ST.JTE.fetch(t.template).toFill(id,{controlId:t.cid,setting:s,data:t._data});
                t.isFlllData=true;
            }
        },
        selected:function(e){
            var em=e.target,a=this,args={cancel:false};
            //包含a标签
            if(em&&em.tagName.contains(/^a$/i)||(em=em.parentNode,em.tagName.contains(/^a$/i))){
                args.text=em.innerHTML;
                args.value=(em.getAttribute('value')+'').encodeURIComponent(true);
                args.node=em;
                a.onSelected&&a.onSelected(args,e);
            }
            args.cancel&&a.hide();
        },
        changeData:function(data){
            this._data=data;
            this._fillData();
            this._initPos();
            this._initEvent();
        },
        setZIdx:function(idx){
            this._$zIdx=idx||20000;
            if(this.isShown){
                $("#"+this.cid+'_dcnt').css("zIndex",this._$zIdx);
            }
        },
        getZIdx: function () {
            return this._$zIdx;
        },
        hide:function(){
            this.isShown=false;
            $('#'+this.cid+'_dcnt').hide();
            $(document).unbind("mousedown.dc",this._hide);
            $(window).unbind("resize.dc",$.Lang.bind(this._initPos));
        },
        onSelected:"",//事件接口
        onOver:"",  //事件接口
        onOut:""    //事件接口
    }),
    Control: $.createClass({}, {
      isShown: false,
      init: function (zIdx, opc) {
        this.ctrlId = $.getUid(); //控制ID
        this.setZIdx(zIdx);       //设置层级   z-Index
        this.setOpacity(opc);     //设置透明度 opacity
      },
      show: function () {
        $("#" + this.ctrlId).show();
        this.isShown = true;
      },
      //flag 是否带动画效果 callback 回调方法
      hide: function (flag, callback) {
        var t = this, tmpFn = function () {
          //$(this.ctrlId).remove();
          $("#" + t.ctrlId).hide();
          t.isShown = false;
          $.Lang.isMethod(callback) && callback();
        };
        if (flag) {
          $("#" + this.ctrlId).fadeOut("slow", tmpFn);
        } else {
          tmpFn();
        }
      },
      showMask: function () {
        var maskId = this.ctrlId + '_mask';
        if ($(maskId).size() == 0) {
          var zIdx = this.getZIdx() - 1;
          var div = $("<div>").attr({id: maskId}).css({position: 'absolute', zIndex: zIdx, backgroundColor: 'black', opacity: 0.5, left: 0, top: 0});
          div.appendTo("body");
        }
        if (!this._$maskWRS) {
          this._$maskWRS = function () {
            var ds = $.documentSize();
            $("#" + maskId).css({width: ds.fullWidth + 'px', height: ds.fullHeight + 'px'});
          };
          this._$maskWRS();
          $(window).bind("resize.mask", this._$maskWRS);
        }
      },
      hideMask: function () {
        $("#" + this.ctrlId + '_mask').remove();
        $(window).unbind("resize.mask");
        delete this._$maskWRS;
      },
      setZIdx: function (idx) {
        this._$zIdx = idx || 1000;
        if (this.isShown) {
          $("#" + this.ctrlId).css("zIndex", this._$zIdx);
        }
      },
      setOpacity: function (opc) {
        this._$opc = opc || 1;
        if (this.isShown) {
          $("#" + this.ctrlId).css("opacity", opc);
        }
      },
      getZIdx: function () {
        return this._$zIdx;
      },
      hideAfter: function (time, hideFlag) {
        this._$hideAfter = window.setTimeout($.Lang.bind(this.hide, this, hideFlag || false), time || 3000);
      }
    })
  }
});

/*
 tip msg class 提示信息
 @methods
 setMsg(msg)
 show(msg,useMask,zIdx)
 hide(flag)
 @eg 
 var tip=new $.Widget.Tip();
 tip.show("我是提示");
 */
$.Widget.Tip = $.createClass($.Widget.Control, function (_base) {
  //配置
  var config = {
    temp: "common_tip"
  }
  return{
    init: function (ops) {
      var t = this;
      t.config = $.extend({}, config, ops || {});
      var count = $.Widget.Tip._count += 10;
      _base.init.call(this, count);
    },
    setMsg: function (msg) {
      var t = this, c = t.config;
      if (this.isShown) {
        var tp = "warm";
        if ($.Lang.isObject(msg)) {
          tp = ["warm", "success", "error", "loading"]
          $.each(tp, function (i, v) {
            if (msg[v]) {
              tp = v;
              msg = msg[v];
              return false;
            }
          })
        }
        ST.JTE.fetch(c.temp).toFill(this.ctrlId, {type: tp, msg: msg});
      }
    },
    show: function (msg, useMask, zIdx) {
      var t = this;
      if (zIdx)t.setZIdx(zIdx);
      window.clearTimeout(this._$hideAfter);
      if (!t.isShown) {
        t.isShown = true;
        if (useMask) {
          t.showMask();
        }
        if ($("#" + t.ctrlId).size() == 0) {
          var div = $("<div>").attr({id: t.ctrlId}).css({position: $.Lang.Browser.isIE6 ? 'absolute' : 'fixed', left: '-1000px', top: '-1000px', zIndex: t.getZIdx()});
          div.appendTo("body");
        } else {
          $("#" + t.ctrlId).show();
        }
        t._$wresize = function () {
          var ds = $.documentSize(), divBound = $.getBound(t.ctrlId);
          var x = (ds.viewWidth - divBound.w) / 2, y = (ds.viewHeight - divBound.h) / 2;
          if ($.Lang.Browser.isIE6) {
            x += ds.scrollLeft;
            y += ds.scrollTop;
          }
          $("#" + t.ctrlId).css({left: x + 'px', top: y + 'px'});
        }
        $(window).bind("resize.tip", t._$wresize);
      }
      t.setMsg(msg);
      t._$wresize();
    },
    hide: function (flag) {
      var t = this;
      _base.hide.call(t, flag, function () {
        t.hideMask();
        //$.delEm(t.ctrlId);
        $.Widget.Tip._count -= 10;
        $(window).unbind("resize.tip");
      });
    }
  }
});
$.Widget.Tip._count = 30000;


/*
 msgbox class 消息盒
 @method
 show
 @desc
 ops:(object)            //msgbox的参数选择,详见$.Widget.Msgbox
 {
 title             //标题
 content           //内容
 url               //链接
 havaSpace         //是否具有空白
 hideBottom        //隐藏底部按钮
 hold              //true
 }
 btns:(array)            //按钮数组 数组内容为对象 {text:"确定",fun:function(){}}
 width:(num)                 //宽度
 height:(num)                 //高度
 mask:(bool)               //遮罩 默认为falsh
 getIframe                      //获取窗口内iframe
 setSize                       //设置大小
 toCenter                      //居中显示
 hide                           //隐藏
 runIframeMethod                //执行iframe内部方法 必须为同域
 setBtnText                     //设置按钮的文本

 @e       	 
 var mb=new $.Widget.Msgbox();
 mb.show(ops,btns,w,h,mk);
 mb.toCenter();

 */
$.Widget.Msgbox = $.createClass($.Widget.Control, function (_base) {
  return{
    template: "common_msgbox",
    init: function () {
      var count = $.Widget.Msgbox.$count += 10;
      _base.init.call(this, count);
      this.minWidth = 200;
      this.minHeight = 150;

    },
    show: function (ops, btns, width, height, mask) {
      var t = this, zidx = t.getZIdx(), ifm;
      if (!$.Lang.isArray(btns))btns = [];
      t._$useMask = !!mask;

      //t.stopAniHide();
      if (!t.isShown) {
        t.isShown = true;
        if (mask)t.showMask();
        if ($("#" + t.ctrlId).size() == 0) {
          var div = $("<div>").attr({id: t.ctrlId}).css({position: ((!ops.hold) ? ($.Lang.Browser.isIE6 ? 'absolute' : 'fixed') : "absolute"), left: -screen.width + 'px', zIndex: t.getZIdx(), '_zoom': 1, width: width || t.minWidth, "height": height || t.minHeight});
          div.appendTo("body");
        } else {
          t._$zIdx = $.Widget.Msgbox.$count + 5;
          t.setZIdx(t._$zIdx);
          $("#" + t.ctrlId).show();
        }
        t.minWidth  = width||t.minWidth;
        t.minHeight = height||t.minHeight;
        t._$btnLen = btns.length;
        ST.JTE.fetch(t.template).toFill(t.ctrlId, {ops: ops, btns: btns, controlId: t.ctrlId});
        if (ops.url) {
          ifm = t.getIframe();
        } else if(ops.action){
          t.changeAjaxContent(ops.action);
        }else{
          t.autoSize();
        }
        for (var i = 0; i < t._$btnLen; i++) {
          t['__mbtne_' + i] = (function (z) {
            return function (e) {
              $.stopEvent(e);
              e = {};
              try {
                $.Lang.isMethod(btns[z].fun) && btns[z].fun(t, e)
              }
              catch (ex) {
                alert(ex)
              }
              if (!e.cancle)t.hide(true);
            }
          }(i));
          $('#st_msgbox_' + t.ctrlId + '_btn_' + i).bind('click', t['__mbtne_' + i]);
        }
        //使用jquery拖拽插件
        var a = $("#" + t.ctrlId), b = parseInt($("#st_msgbox_outer_" + t.ctrlId).css("padding-left"), 10);
        if (a.drag)
          $('#st_msgbox_tlecnt_' + t.ctrlId).drag("start",function (ev, dd) {
            $(document).bind("selectstart.msgbox", function () {
              return false
            });
            return a.clone().html("")
              .css({
                "display": "",
                "position": "absolute",
                "opacity": .75,
                "cursor": "move",
                "width": a.width() - 2,
                "height": a.height() - 2,
                "border": "2px dotted #333"
              })
              .appendTo(document.body);
          }).drag(function (ev, dd) {
              $(dd.proxy).css({
                top: dd.offsetY - 2 - b,
                left: dd.offsetX - 2 - b
              });
            }).drag("end", function (ev, dd) {
              $(document).unbind("selectstart.msgbox");
              var ds = $.documentSize();
              $(dd.proxy).remove();
              $("#" + t.ctrlId)
                .css({
                  top: ($.Lang.Browser.isIE6) ? dd.offsetY - 2 - b : dd.offsetY - 2 - b - ds.scrollTop,
                  left: ($.Lang.Browser.isIE6) ? dd.offsetX - 2 - b : dd.offsetX - 2 - b - ds.scrollLeft
                })
            });
        t._$submit = function (e) {
          t['__mbtne_0'] && t['__mbtne_0']();
          $.stopEvent(e);
        };
        t._$hideIfmMsg = function () {
          $("#" + t.ctrlId + '_tip').remove();
          $(ifm).css({display: ''});
        };
        t._$mbclose = function (e) {
          t.hide(true);
        };
        //根据屏幕尺寸缩放,移动
        if (!ops.hold) {
          t._$wresize = function () {
            var ds = $.documentSize(), divBound = $.getBound(t.ctrlId);
            var x = (ds.viewWidth - divBound.w) / 2, y = (ds.viewHeight - divBound.h) / 2;
            if ($.Lang.Browser.isIE6) {
              x += ds.scrollLeft;
              y += ds.scrollTop;
            }
            $("#" + t.ctrlId).css({left: x + 'px', top: y + 'px'});
          }
          $(window).bind("resize.msgbox", t._$wresize);
        }
        $('#st_msgbox_' + t.ctrlId + '_close').bind('click', t._$mbclose);
        $('#st_msgbox_' + t.ctrlId + '_close').bind('mousedown', function (e) {
          e.stopPropagation()
        });
        $('#st_msgbox_tlecnt_' + t.ctrlId).css({MozUserSelect: 'none', KhtmlUserSelect: 'none', 'UserSelect': 'none'});

        ifm = t.getIframe();
        if (ifm)$(ifm).bind('load', t._$hideIfmMsg);
        ops.url && t.setSize(width, height);
        $('st_msgbox_cnt_' + t.ctrlId).find("form").bind("submit", t._$submit);

      }
      t.toCenter();
      //获取焦点
      if (t._$btnLen) $('#st_msgbox_' + t.ctrlId + '_btn_' + (t._$btnLen - 1)).focus();
    },
    changeText:function(c){
      var t = this;
      $("#st_msgbox_cnt_" + t.ctrlId).html(c);
    },
    changeAjaxContent:function(a){
       var t=this;
       t.changeText(ST.LRes.loadingTip);
        t.autoSize();
        window.setTimeout(function(){
            ST.getJSON(a,{},function(j){
                t.changeContent(j.data);
            },function(){
                t.changeText("加载失败!");
            });
        },2000);

    },
    changeContent: function (c) {
      var t = this;
      $("#st_msgbox_cnt_" + t.ctrlId).html(c);
      t.minWidth = 200;
      t.minHeight = 150;
      t.autoSize();
    },
    getIframe: function () {
      var ifms = $("#" + this.ctrlId).find('iframe');
      if (ifms.length) {
        return ifms[0];
      }
      return null;
    },
    autoSize: function () {
      var t = this,
          cnt=$("#st_msgbox_outer_" + t.ctrlId),
          height = cnt.height(),
          width = cnt.width();
      //测量最小高宽设置,手动设置高宽权重教大
      height = (height < t.minHeight) ? t.minHeight : height;
      width = (width < t.minWidth) ? t.minWidth : width;
      t.setSize(width, height);
    },
    setSize: function (w, h) {
          var t = this, h1, h2, h3, fix, div,cnt,outer,style;
          w = w || t.minWidth;
          h = h || t.minHeight;
          if (t.isShown) {
              div = $("#" + t.ctrlId);
              outer = $('#st_msgbox_outer_'+t.ctrlId);
              cnt = $('#st_msgbox_inner_'+t.ctrlId);
              h1=$('#st_msgbox_tlecnt_'+t.ctrlId).outerHeight(true) || 0;
              h2=$('#st_msgbox_btns_'+t.ctrlId).outerHeight(true) || 0;
              h3 = cnt.outerHeight(true) || 0;
              fix = h3-cnt.height();
              h3 = Math.max(h-h2-h1,h3);//设置的最小高度
              if(h3>600) h3=600; //内容最大高度600px;
              style = {height:(h3-fix)+'px'};
              if(!t.getIframe()) $.extend(style,{"overflow-y":"auto"});
              $('#st_msgbox_cnt_'+t.ctrlId).css(style);//cet不允许设置border
              cnt.css({height:h3-fix+'px'});
              outer.css({width:w+'px',height:(h1+h2+h3)+'px'});
              div.css({
                  height: outer.outerHeight(true),
                  width: outer.outerWidth(true)
              });
          }
          t.toCenter();
    },
    toCenter: function () {
      var t = this, h1, ds, h2, b1;
      if (t.isShown) {
        h1 = $('#st_msgbox_tlecnt_' + t.ctrlId).outerHeight(true) || 0,
          h2 = $('#st_msgbox_btns_' + t.ctrlId).outerHeight(true);
        ds = $.documentSize();
        b1 = $.getBound(t.ctrlId);
        var x = (ds.viewWidth - b1.w) / 2, y = (ds.viewHeight - b1.h) / 2;
        if ($.Lang.Browser.isIE6) {
          x += ds.scrollLeft;
          y += ds.scrollTop;
        }
        $("#" + t.ctrlId).css({left: x + 'px', top: y + 'px'});
      }
    },
    _hide: function (flag) {
      var t = this;
      _base.hide.call(t, false, function () {
        for (var i = 0; i < t._$btnLen; i++) {
          $('st_msgbox_' + t.ctrlId + '_btn_' + i).unbind('click');
          delete t['__mbtne_' + i];
        }
        $("#" + t.ctrlId).unbind();
        $('#st_msgbox_tlecnt_' + t.ctrlId).unbind('mousedown');
        $('#st_msgbox_' + t.ctrlId + '_close').unbind();
        $('#st_msgbox_cnt_' + t.ctrlId).find("form").unbind("submit");
        delete t._$mmove;
        delete t._$btnLen;
        delete t._$submit;
        delete t._$mbclose;
        delete t._$dragMask;
        t.hideMask();
        $("#" + t.ctrlId).remove();
      });
    },
    hide: function (flag) {
      var t = this, e = {};
      t.onclose && t.onclose(e);
      if (e.cancle) return;
      t._hide(flag);
    },
    runIframeMethod: function (method) {
      var ifm = this.getIframe(), w, f = true, lw = null;
      if (!ifm)return ifm;
      w = ifm.contentWindow;
      try {
        $.each($.Lang.toArray(method, '.'), function (i, v) {
          if (w[v]) {
            lw = w;
            w = w[v];
          } else {
            f = false;
            throw "break";
          }
        });
        if (f) {
          return w.apply(lw, $.Lang.toArray(arguments));
        }
        return null;
      } catch (e) {
        return null;
      }
    },
    setBtnText: function (idx, text) {
      $('#st_msgbox_' + this.ctrlId + '_btn_' + idx).val(text).html(text);
    },
    display: function (t) {
      t = this;
      if (t.isShown) {
        t.isShown = false;
        $("#" + t.ctrlId).css({display: 'none'});
        if (t._$useMask) t.hideMask();
      } else {
        t.isShown = true;
        $("#" + t.ctrlId).css({display: ''});
        if (t._$useMask)t.showMask();
        t.toCenter();
		//获取焦点
        if (t._$btnLen) $('#st_msgbox_' + t.ctrlId + '_btn_' + (t._$btnLen - 1)).focus();
      }
    },
    conceal: function (t) {
      t = this;
      t.isShown = !1;
      $("#" + t.ctrlId).css({display: 'none'});
      if (t._$useMask)t.hideMask();
    },
    onclose: null
  }
});
$.Widget.Msgbox.$count = 5000;


/*
 Notice class 提示消息
 */
$.Widget.Noticebox = $.createClass($.Widget.Control, function (_base) {
    return{
        template: "common_noticebox",
        init: function () {
            var count = $.Widget.Noticebox.$count += 10;
            _base.init.call(this, count);
            this.minWidth = 200;
            this.minHeight = 120;
        },
        /*
        * ops {
        *     mask:              bool 是否启用遮罩
        *     hideDelay:         int  自动关闭延迟 毫秒
        *     width:             string 弹窗宽度
        *     height:            string  提示框高度
        *     title:             string   提示框标题
        *     type:              string   提示框类型
        *     content:           string   提示框内容
        * }
        * */
        show: function (ops) {
            var t = this;
            if (!t.isShown) {
                t.isShown = true;
                if (ops.mask) t.showMask();
                if ($("#" + t.ctrlId).size() == 0) {
                    var div = $("<div>").attr({id: t.ctrlId}).css({position:$.Lang.Browser.isIE6 ? 'absolute' : 'fixed', left: -screen.width + 'px', zIndex: t.getZIdx(), width: ops.width || t.minWidth, "min-height": ops.height || t.minHeight, "_height": ops.height || t.minHeight});
                    div.appendTo("body");
                } else {
                    t._$zIdx = $.Widget.Noticebox.$count + 5;
                    t.setSize(ops.width|| t.minWidth,ops.height|| t.minHeight);
                    t.setZIdx(t._$zIdx);
                    $("#" + t.ctrlId).show();
                }
                ST.JTE.fetch(t.template).toFill(t.ctrlId, {controlId: t.ctrlId,ops: ops});
            }
            t.toCenter();
            window.setTimeout($.Lang.bind(t.hide,t),ops.hideDelay||2000);
        },
        setSize:function(w,h){
            var t=this;
            $("#" + t.ctrlId).css({
                width:w,
                height:h
            });
        },
        toCenter: function () {
            var t = this, ds, b1;
            if (t.isShown) {
                ds = $.documentSize();
                b1 = $.getBound(t.ctrlId);
                var x = (ds.viewWidth - b1.w) / 2, y = (ds.viewHeight - b1.h) / 2;
                if ($.Lang.Browser.isIE6) {
                    x += ds.scrollLeft;
                    y += ds.scrollTop;
                }
                $("#" + t.ctrlId).css({left: x + 'px', top: y + 'px'});
            }
        },
        _hide: function (flag) {
            var t = this;
            _base.hide.call(t, false, function () {
                t.hideMask();
                t.isShown = false;
                $("#" + t.ctrlId)[flag?"remove":"hide"]();
            });
        },
        hide: function () {
            var t = this,e={cancel:false};
            t.onclose && t.onclose(e);
            t._hide(e.cancel);
        },
        onclose: null
    }
});
$.Widget.Noticebox.$count = 6000;

/*
 下拉框类
 事件接口 onselected
 @menthods
 scrollToTop
 changeData
 addItem
 getSelectedValue
 selByValue
 selByText
 setZIdx
 @eg
 var types=[{text:"一",value:1},{text:"二",value:2},{text:"三",value:3}];
 var dd = ST.ddList('sortType', types,function(o){

 });
 var a=types.getJson("value",1); //获取ID为1的json
 a.text="A";
 dd.changeData(types);
 dd.addItem({text:"五",value:5});
 */
window.activeDDL = null; //记录当前打开的DDL
$.Widget.DropDownList = $.createClass({}, function () {
  return{
    showNumber: 6,
    itemHeight: 30,
    init: function (id, h) {
      var t = this;
      t.cid = id;
      t.Jid = $("#" + id);
      t.input = t.Jid.find("input").eq(0).filter(function(){
        return this.id!= t.cid + "_val";
      });
      t.valInput = $("#" + t.cid + "_val");
      t.labInput = $("#" + t.cid + "_lab");
      if ($.Lang.isNumber(h))t.showNumber = h;
      t._show = $.Lang.bind(t.show, t);
      t._md = function (e) {
        if (window.activeDDL != t) {
          if (window.activeDDL)window.activeDDL.hide();
          window.activeDDL = t;
        }
        $.stopEvent(e);
      };
      t.Jid.bind('click', t._show).bind('mousedown', t._md);
      t._hide = $.Lang.bind(t.hide, t);
    },
    /*

     */
    _fillData: function () {
      var t = this, id = t.cid + '_ddlcnt', el = $("#" + id);
      if (el.size() > 0) {
        ST.JTE.fetch("common_ddl").toFill(id, {datalist: t.data});
        el.css({height: ((t.data.length > t.showNumber ? t.showNumber : t.data.length) * t.itemHeight + 2) + 'px', overflowY: t.data.length > t.showNumber ? 'auto' : 'hidden'});
      }
    },
    /*

     */
    fillData: function (a) {
      this.data = a;
      this._fillData();
    },
    /*

     */
    scrollToTop: function (f) {
      var t = this, to = $("#" + t.cid + '_ddlcnt')[0];
      if (t.isShown && to) {
        to.scrollTop = 0;
        delete t._needToTop;
      } else if (!f) t._needToTop = true;

    },
    /*

     */
    show: function (e) {
      $.stopEvent(e);
      var t = this, id = t.cid + '_ddlcnt', el = $("#" + id), d;
      if (t.isShown) {
        t.hide();
        return;
      }
      t.isShown = true;
      if (el.size() > 0) {
        el.show();
      } else {
        t._sdivId = id;
        d = $('<div>').attr({id: id}).css({position: 'absolute', zIndex: t._zidx || 3000, left: "-2000px", top: "0px"});
        d[0].className = 'dropdown';
        d.appendTo("body");
        t._fillData();
        d.bind('mousedown', $.stopEvent).bind('click', function (e) {
          $.Lang.bind(t.selected, t, e)();
        });
        el = d;
      }
      d = $.getBound(t.cid);
      el.css({left: d.x + 'px', top: (d.y + d.h - 1) + 'px', width: (d.w-2) + 'px'});
      if (t._needToTop) t.scrollToTop(!0);
      $(document).bind("mousedown.ddlist", t._hide);
    },
    /*

     */
    selected: function (e) {
      //test
      var em = e.target, t = this;
      if (em && em.tagName.contains(/^a$/i)) {
        var args = {};
        args = {text: em.innerHTML, value: em.getAttribute('value')};
        if (t.lv != args.value || t.lt != args.text) {
          t.onselected(args);
          if (!args.cancle) {
            t.setVal(args.value);
            t.setText(args.text);
          }
          t.lv = args.value;
          t.lt = args.text;
        }
      }
      t.hide();
    },
    /*

     */
    hide: function () {
      this.isShown = false;
      $("#" + this.cid + '_ddlcnt').hide();
      $(document).unbind("mousedown.ddlist");
    },
    changeData: function (data) {
      this.data = data;
      this._fillData();
    },
    setText: function (text) {
      text = ST.Code.decodeHtml(text);
      this.lt = text;
      this.input.val(text);
      this.labInput.text(text);
    },
    setVal: function (val) {
      this.lv = val;
      //hack ie7
      if(this.valInput.length && this.valInput[0].tagName=="INPUT"){
        this.valInput.val(val);
      }else{
        this.valInput.text(val);
      }
    },
    addItem: function (data, pos) {
      var t = this;
      t.data.insert(data, pos);
      t.changeData(t.data);
    },
    getSelectedValue: function () {
      return this.lv || "";
    },
    selByValue: function (vle) {
      var t = this, args = {}, flag = -1;
      $.each(t.data, function (idx, v) {
        if (v.value == vle) {
          args = {text: v.text, value: v.value};
          t.onselected(args);
          if (!args.cancle) {
            t.setText(args.text);
            t.setVal(args.value);
          }
          flag = idx;
          return false;
        }
      });
      return flag;
    },
    selByText: function (vle) {
      var t = this, args = {}, flag = -1;
      $.each(t.data, function (idx, v) {
        if (v.text == vle) {
          args = {text: v.text, value: v.value};
          t.onselected(args);
          if (!args.cancle) {
            t.setText(args.text);
            t.setVal(args.value);
          }
          flag = idx;
          return false;
        }
      });
      return flag;
    },
    setZIdx: function (v) {
      var t = this, id = t.cid + '_ddlcnt', el = $("#" + id);
      if (el.size() > 0) {
        el.css({zIndex: v});
      } else {
        t._zidx = v;
      }
    },
    dispose: function () {
      var t = this;
      t.Jid.unbind();
      $("#" + t._sdivId).unbind().remove();
      t.hide();
    }
  }
});

/*
 前台公共分页
 公共分页 前端分页
 id  分页对象ID
 o   相关分页设置
 {
 dataNum:  数据条数
 pageSize: 页大小
 pageDisnum:  页码显示个数
 dynamic:   动态分页
 pageTemp:  分页模板
 pageHash:false,
 pageNumTemp: 分页页码模板
 }
 fun 切换页执行方法
 */
ST.Pager=function(id, o, fun){
    var _id, _cid, _nid,_ae, _curPage = 1, _pageNum = 1, _pageSize = 10 ,_pageDisnum = 3,_dataNum,_pageHash;
    return {
        init: function() {
            var t=this,jid=$("#"+id);
            if (o) {
                _pageSize = o.pageSize || _pageSize;
                _pageNum = Math.floor(o.dataNum / _pageSize) + ((o.dataNum % _pageSize == 0) ? 0 : 1)
                _pageDisnum = o.pageDisnum || _pageDisnum;
                _dataNum= o.dataNum;
                _pageHash = o.pageHash || false;
            };

            _curPage = _pageHash?(parseInt(ST.getHash("page"),10) || _curPage):_curPage;
            //不足一页数据
            if(o.dataNum<=_pageSize){
                jid.hide();
            }else{
                jid.show();
            }

            _id = id;

            ST.JTE.fetch(o.pageTemp||"common_pager").toFill(id, { curPage: _curPage, pageNum: _pageNum,num:_pageDisnum,datanum:_dataNum});



            _cid = jid.find(".curPage");
            _nid = jid.find(".pageNum");
            _ae  = jid.find(".pagerArea");
            _total =  jid.find(".pagertotal");

            _cid.html(_curPage || 1);
            _nid.html(_pageNum || 1);
            _total.html(_dataNum || "-");

            //事件
            jid.find(".btnIndex").bind("click",function(){
                if (_curPage == 1) return;
                _curPage = 1;
                t.update();
                ST.setHash("page",_curPage);
                fun&&fun(_curPage);
            });
            jid.find(".btnPrev").bind("click",function(){
                if (_curPage - 1 < 1) return;
                _curPage--;
                t.update();
                ST.setHash("page",_curPage);
                fun&&fun(_curPage);
            });
            jid.find(".btnNext").bind("click",function(){
                if (_curPage + 1 > _pageNum) return;
                _curPage++;
                t.update();
                ST.setHash("page",_curPage);
                fun&&fun(_curPage);
            });
            jid.find(".btnLast").bind("click",function(){
                if (_curPage == _pageNum) return;
                _curPage = _pageNum;
                t.update();
                ST.setHash("page",_curPage);
                fun&&fun(_curPage);
            });
            if(_ae.size()>0){
                _ae.bind("click",function(e){
                    var em=e.target;
                    if(em&&em.tagName.contains(/^a$/i)||(em=em.parentNode,em.tagName.contains(/^a$/i))){
                        var p=Number($(em).text().t());
                        t.goPage(p);
                    }
                });
            }
            delete this.init;
            return this;
        },

        goPage:function(p){
            var t=this;
            if (_curPage == p) return;
            _curPage = p;
            if (_curPage > _pageNum) _curPage = _pageNum || 1;
            t.update();
            if (_pageHash) ST.setHash("page",_curPage);
            fun&&fun(_curPage);
        },
        //获取数据后调用
        update: function(num) {
            var t=this;
            if (o.dynamic && num != undefined) {
                //动态分页
                _pageNum = Math.floor(num / _pageSize) + ((num % _pageSize == 0) ? 0 : 1);
                $("#"+_id)[_pageNum > 1?"show":"hide"]();
                _nid && _nid.html(_pageNum || 1);
                if (_curPage > _pageNum) _curPage = _pageNum || 1;
            }
            if(_ae.size()>0){
                ST.JTE.fetch(o.pageNumTemp||"common_pager_num").toFill(_ae,{cp:_curPage,pn:_pageNum,num:_pageDisnum});
            }
            _total.html(num);
            _cid && _cid.html(_curPage);
        },
        setPage:function(p){
            _curPage = p;
        },
        cp: function() {
            return _curPage;
        },
        ps: function() {
            return _pageSize;
        }
    }.init();
};
/*

 */
ST.Cookie = {
  get: function (n) {
    var a = document.cookie.match(new RegExp("(^| )" + n + "=([^;]*)(;|$)"));
    return a ? a[2].e(!0) : a;
  },
  set: function (name, value, hour, domain, path) {
    if ($.Lang.isObject(value))
      value = ST.objTostr(value);

    var sc = name + '=' + (value + '').e();
    hour = Number(hour) || 48;
    path = path || '/';
    if ($.Lang.Browser.isIE) path = path.replace(/[^\/]+$/, "");//针对IE
    var date = new Date();
    date.setTime(date.getTime() + hour * 3600 * 1000);
    sc += ';expires=' + date.toGMTString();
    if (domain)sc += ';domain=' + domain;
    sc += ';path=' + path;
    document.cookie = sc;
  },
  del: function (n, domain, path) {
    var exp = new Date(), t = this, v;
    t.set(n, "", -10000, domain, path);
  }
};
/*
 静态数据
 */
ST.TRes = {};
/*
 静态语言资源包
*/
ST.Language = "zh-CHS";
ST.LRes = (function(k){
    return{
        //辅助提示
        postSucc:{'zh-CHS':'提交成功'}[k],
        reqStop:{'zh-CHS':'已取消请求'}[k],
        reqTmout:{'zh-CHS':'请求超时 请稍后再试'}[k],
        e404:{'zh-CHS':'网络已断开'}[k],
        e500:{'zh-CHS':'服务器错误'}[k],
        tip:{'zh-CHS':'提示'}[k],
        ok:{'zh-CHS':'确定'}[k],
        cancle:{'zh-CHS':'取消'}[k],
        delAsk:{'zh-CHS':'您确认删除吗'}[k],
        subAsk:{'zh-CHS':'您确定提交吗？'}[k],
        delTip:{'zh-CHS':'操作确认'}[k],
        opTip:{'zh-CHS':'操作确认'}[k],
        opFailed:{'zh-CHS':'操作失败'}[k],
        opSuccess:{'zh-CHS':'操作成功'}[k],
        logOut:{'zh-CHS':'登出成功'}[k],
        passTip:{'zh-CHS':'游戏，影视，购物，生活，易游一路通行.'}[k],
        serverBusy:{'zh-CHS':'服务器忙 请稍后再试'}[k],
        loadingTip:{'zh-CHS':'<p>正在努力的加载...</p>'}[k],
        //资源错误文本
        NeedTemp:{'zh-CHS':'需要引入通用模板文件！'}[k],
        RequireFail:{'zh-CHS':'依赖文件加载失败！'}[k],
        //常用静态文本
        Require:{'zh-CHS':'必须'}[k],
        Equal:{'zh-CHS':'等于'}[k],
        LessOrEqual:{'zh-CHS':'小于等于'}[k],
        Less:{'zh-CHS':'小于'}[k],
        GreaterOrEqual: {'zh-CHS':'大于等于'}[k],
        Greater: {'zh-CHS':'大于'}[k],
        NotEqual:{'zh-CHS':'不等于'}[k],
        IsVerify:{'zh-CHS':'正在进行验证，请稍候！'}[k],
        IsSubmit:{'zh-CHS':'正在提交，请稍候！'}[k],
        VerifyFail:{'zh-CHS':'验证失败！'}[k]
    }
}(ST.Language));

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
  getBound: function () {
    return $.getBound(this[0]);
  },
  /*
   eg:
   $('.action-box').evProx({
   click: {
   '#btn-add': function(){
   //do something
   },
   //这是是支持jQuery的':last / [attr] / :eq(0)'等方法的
   '#btn-delete': function(){
   //do something
   }
   },
   mouseenter: {
   '#btn-sort': function(){
   //do something
   }
   }
   });
   */
  evProx: function (obj) {
    for (var eName in obj)
      for (var selector in obj[eName])
        $(this).on(eName, selector, obj[eName][selector]);
  }
})