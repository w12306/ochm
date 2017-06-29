/*
 数字微调器插件
 created by GYJ, last edited by ZS 2013-7-12

 var JQ = require('jquery')；//1.7+
 var MW = require('jquery.mousewheel');//可选
 var ST = require('ST');
 var CT = require('Temp_common');//可选

 update 2013-8-15
 (1)接口优化
 * */

//插件主程序
ST.Spinner = function (id, config) {
  //私有成员
  var _private = {
    //当前数值
    num: 0
  };
  return {
    //事件命名空间
    _evNamespace: '.st_spinner',
    //事件回调方法
    _evHandler: {
      keydown: function (e) {
        var t = e.data.t, k = e.keyCode, _k = ST.Spinner.util.keycode;
        switch (k) {
          case _k['UP']:
            t.add();
            e.preventDefault();
            break;
          case _k['DOWN']:
            t.discount();
            e.preventDefault();
            break;
        }
      },
      mousewheel: function (e, d) {
        var t = e.data.t;
        if (!d) return;
        $(this).trigger('focus');
        d > 0 ? t.add() : t.discount();
        e.preventDefault();//阻止页面滚动
      },
      blur: function (e) {
        var t = e.data.t, v = $(this).val();
        t.setValue(v);
      },
      focus: function (e) {
        $(this).blur();
      }
    },
    //初始化,事件绑定操作
    init: function (id, config) {
      var t = this;
      t.config = $.extend({}, ST.Spinner.defaults);
      $.extend(t.config, config);
      t._setup();
      delete t.init;
      return t;
    },
    _setup: function () {
      var t = this, c = t.config;
      //缓存对象
      t.Jid = $('#' + id);            //值input
      t.cid = id + '_st_spinner';     //插件包裹容器id
      if (t.Jid.size() == 0) return t;
      //渲染界面
      if (c.template) {
        t.renderUI();
      } else {
        t.Cid = $('#' + t.cid);
      }
      //初始化值
      if (t.Jid.val()) {
        _private.num = parseFloat(t.deformat(t.Jid.val()));
      } else {
        _private.num = parseFloat(c._default);
      }
      t.Jid.val(t.format(_private.num));
      //初始化状态
      c.disabled ? t.disable() : t.enable();
      //初始化事件
      t._initEvents();
      return t;
    },
    _initEvents: function () {
      var t = this;
      //使用事件代理
      t.Cid.evProx({
        click: {
          'a': function () {
            //包含a标签
            var cmd = $(this).data("cmd"), pars = $(this).data("pars");
            t[cmd] && t[cmd](pars);
          }
        }
      });
      return t;
    },
    //渲染界面
    renderUI: function () {
      var t = this, c = t.config;
      if (c.width) t.Jid.attr('class', c.width);
      t.Jid.after(ST.JTE.fetch(c.template).getFilled({id: id, c: c}));
      t.Cid = $('#' + t.cid);
      var wrap = $('#' + t.cid + '_wrap');
      if (t.Cid.size() > 0) {
        if (wrap.size() == 0) wrap = t.Cid;
        wrap.append(t.Jid);
      }
    },
    //增加 @pars incr-增量
    add: function (incr) {
      var t = this, util = ST.Spinner.util, n = _private.num;
      incr = incr || t.config.increment;
      n = n + incr;
      n = util.decimal(n, util.getDigits(incr));
      t.setValueByNum(n);
      return t;
    },
    //减少 @pars incr-增量
    discount: function (incr) {
      var t = this, util = ST.Spinner.util, n = _private.num;
      incr = incr || t.config.increment;
      n = n - incr;
      n = util.decimal(n, util.getDigits(incr));
      t.setValueByNum(n);
      return t;
    },
    //格式化
    format: function (n) {
      var t = this, c = t.config;
      return c.prefix + n + c.suffix;
    },
    //去格式
    deformat: function (v) {
      var t = this, c = t.config, util = ST.Spinner.util,
        pre = util.encode(c.prefix),
        suf = util.encode(c.suffix),
        d = util.getDigits(c.increment);
      return util.decimal(v.replace(new RegExp('^' + pre + '|' + suf + '$', 'g'), ''), d);
    },
    //获得数值 @return 当前数值
    getNumValue: function () {
      return _private.num;
    },
    //获得值 @return 当前显示值
    getValue: function () {
      var t = this;
      return t.format(_private.num);
    },
    //设置数值
    setValueByNum: function (n) {
      var t = this, c = t.config;
      n = parseFloat(n);
      if (isNaN(n)) n = _private.num;
      if (n > c.max) n = parseFloat(c.max);
      if (n < c.min) n = parseFloat(c.min);
      t._setValue(t.format(n));
      if (n != _private.num) {
        _private.num = n;
        t.onChange && t.onChange();
      }
      return t;
    },
    //设置值：内部方法
    _setValue: function (v) {
      var t = this;
      t.Jid.val(v);
      t.Jid.trigger('input propertychange');
      return t;
    },
    //设置值：公开方法
    setValue: function (v) {
      var t = this;
      t.setValueByNum(t.deformat(v));
      return t;
    },
    //启用
    enable: function () {
      var t = this, c = t.config;
      if (t.Cid) t.Cid.removeClass('disabled');
      t.Jid.attr('disabled', false);
      if (c.keyboard) {
        t.Jid.on('keydown' + t._evNamespace, {t: t}, t._evHandler.keydown);
      }
      if (c.mousewheel) {
        t.Jid.on('mousewheel' + t._evNamespace, {t: t}, t._evHandler.mousewheel);
      }
      if (c.inputable) {
        t.Jid.on('blur' + t._evNamespace, {t: t}, t._evHandler.blur);
      } else {
        t.Jid.attr("readOnly", true).on("focus" + t._evNamespace, {t: t}, t._evHandler.focus);
      }
      return t;
    },
    //禁用
    disable: function () {
      var t = this;
      if (t.Cid) t.Cid.addClass('disabled');
      t.Jid.attr('disabled', true);
      return t;
    },
    //接口
    onChange: '' //值改变时触发
  }.init(id, config)
};
$.extend(ST.Spinner, {
  defaults: {
    //默认值
    _default: 0,
    //最小
    min: 0,
    //最大
    max: 100,
    //增量
    increment: 1,
    //前缀
    prefix: '',
    //后缀
    suffix: '',
    //模板
    template: 'common_spinner',
    //组件宽度
    width: 'input-mini',
    //是否可输入
    inputable: true,
    //是否禁用
    disabled: false,
    //是否支持鼠标滚动调节
    mousewheel: false,
    //是否支持键盘调节
    keyboard: true
  },
  util: {
    //键值
    keycode: {
      'UP': 38,
      'DOWN': 40
    },
    //正则:
    pattern: {
      number: '-?((0|[1-9]\\d*)|(\\d+\.\\d+))',
      regkey: '([.*+?^=!:${}()|[\]/\\])'
    },
    //转义正则关键字
    encode: function (s) {
      return s.replace(new RegExp(this.pattern.regkey, 'g'), '\\$1');
    },
    //浮点数格式化
    decimal: function (n, d) {
      var v = Math.pow(10, parseFloat(d));
      return v == 0 || isNaN(v) || isNaN(n) ? 0 : Math.round(n * v) / v;
    },
    //获取小数位数
    getDigits: function (n) {
      if (isNaN(parseFloat(n))) return 0;
      n += '';
      if (n.split('.').length == 1) return 0;
      return n.split('.')[1].length;
    }
  }
});