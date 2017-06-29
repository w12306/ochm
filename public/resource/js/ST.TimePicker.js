/*
 时间选择插件
 created & last edited by ZS 2013-8-15
 var JQ = require('jquery')；//1.7+
 var ST = require('ST');
 var Spinner = require('ST.Spinner');

 update 2013-8-14
 (1)优化UE：去掉左右键操作（可考虑根据坐标判断是否切换当前输入框）
 (2)修复bug

 update 2013-8-15
 (1)修复bug
 * */

ST.TimePicker = function (id, config) {
  //私有成员
  var _private = {
    currentTime: {'H': 0, 'M': 0, 'S': 0}
  };
  return {
    //依赖列表
    _require: ['ST.Spinner'],
    //事件命名空间
    _evNamespace: '.st_timepicker',
    //事件回调方法
    _evHandler: {
      click: function (e) {
        e.stopPropagation();
        $(this).focus().select();
      },
      focus: function (e) {
        var t = e.data.t, thefocus = e.data.curFocus;
        t._curFocus = thefocus;//修正当前焦点
      },
      keydown: function (e) {
        var k = e.keyCode, _k = ST.TimePicker.util.keycode;
        switch (k) {
          case _k['LEFT']:
            $(this).prev('input').focus();
            e.preventDefault();
            break;
          case _k['RIGHT']:
            $(this).next('input').focus();
            e.preventDefault();
            break;
        }
      }
    },
    init: function (id, config) {
      var t = this;
      t.config = $.extend({}, ST.TimePicker.defaults);
      $.extend(t.config, config);
      t.Jid = $('#' + id);
      if (t.Jid.length == 0) return t;
      ST.getJsList(t._require, function () {//载入依赖文件
        t._setup();
      }, function () {
        alert(ST.LRes.RequireFail);
      });
      delete t.init;
      return t;
    },
    _setup: function () {
      var t = this, c = t.config, util = ST.TimePicker.util;
      t.minTime = util.getTimeObject(c.minTime);
      t.maxTime = util.getTimeObject(c.maxTime);
      t.increment = util.getTimeObject(c.increment);
      if (t.Jid[0].tagName.contains(/^(a|input|textarea|button)$/i)) {
        var cid = id + '_st_timepicker';
        t.Cid = $('#' + cid);
        if (t.Cid.size() == 0) {
          //构建容器
          t.Cid = $('<div></div>').attr('id', cid).css({
            display: 'none'
          });
          if (c.showOn == 'init') {
            t.Jid.after(t.Cid);
          } else {
            t.Cid.css({
              position: 'relative',
              zIndex: c.zIndex
            }).appendTo('body');
          }
        }
      } else {
        t.Cid = t.Jid;
      }
      t.Cid.addClass(c.className);
      if (c.showOn == 'init') {
        t.show();
      } else {
        t.Jid.on(c.showOn + t._evNamespace, function (e) {
          t.show();
        });
      }
      t.setValue(c.defaultTime);
      return t;
    },
    _initEvents: function () {
      var t = this, c = t.config;
      //绑定事件
      $.each(t.$Element, function (k, v) {
        if (t.$Element[k].size() > 0) {
          t.$Element[k]
            //.on('keydown' + t._evNamespace, {t: t}, t._evHandler.keydown)
            .on('click' + t._evNamespace, {t: t}, t._evHandler.click)
            .on('focus' + t._evNamespace, {t: t, curFocus: k}, t._evHandler.focus);
        }
      });
      t.Cid.evProx({
        click: {
          'a': function (e) {
            e.stopPropagation();
            var cmd = $(this).data("cmd"), pars = $(this).data("pars");
            t[cmd] && t[cmd](e, pars);
          }
        }
      });
      t.Cid.on('mousedown' + t._evNamespace, $.stopEvent);
      if (c.showOn != 'init') {
        $(document).on("click" + t._evNamespace, function () {
          t.hide();
        });
        t.Jid.on('click' + t._evNamespace, function (e) {
          e.stopPropagation();
        });
      }
      return t;
    },
    //创建小部件（微调器）
    _createWidget: function (type, min, max) {
      var t = this, c = t.config;
      if (t.$Element[type].size() == 0) return t;
      if (!t._isWidgetCreated) t._isWidgetCreated = {};
      if (t._isWidgetCreated[type]) return t;
      //设置临界值
      var _min = t.minTime[type], _max = t.maxTime[type], _cur = t.getTime()[type];
      t.minTime[type] = Math.max(_min, min);
      t.maxTime[type] = Math.min(_max, max);
      if (_cur > _max) _private.currentTime[type] = _max;
      if (_cur < _min) _private.currentTime[type] = _min;
      _cur = t.getTime()[type];
      //初始化Spinner
      t.$Widget[type] = new ST.Spinner(id + '_st_timepicker_' + type, {
        _default: _cur,
        min: t.minTime[type],
        max: t.maxTime[type],
        increment: t.increment[type],
        template: ''
      });
      //重写格式化方法
      t.$Widget[type].format = function (n) {
        return ST.TimePicker.util.getPrefix(n) + n;
      };
      //重写去格式化方法
      t.$Widget[type].deformat = function (v) {
        var n = parseInt(v, 10);
        if(isNaN(n)) n = this.getNumValue();
        return n;
      };
      //定义接口
      t.$Widget[type].onChange = function(){
        t.setValue();
      };
      t.$Element[type].trigger('blur');
      t._isWidgetCreated[type] = true;
      return t;
    },
    //渲染界面
    renderUI: function (callback) {
      var t = this, c = t.config;
      if (t.Cid.size() == 0) return t;
      t.Cid.html(
        ST.JTE.fetch(c.template).getFilled({controlId: id, config: c})//ST_REF
      );
      window.setTimeout(callback, 50);
      return t;
    },
    //显示
    show: function () {
      var t = this, c = t.config;
      t._isShown = true;
      t.Cid.show();
      if (c.showOn != 'init' && t.Jid !== t.Cid) {
        var pos = t.calPosition();
        t.setPosition(pos.x, pos.y);
      }
      if (!t._isrenderred) {
        t._isrenderred = true;
        t.renderUI(function () {
          t.$Element = {
            'H': $('#' + id + '_st_timepicker_H'),
            'M': $('#' + id + '_st_timepicker_M'),
            'S': $('#' + id + '_st_timepicker_S')
          };
          t.$Widget = {};
          //初始化秒
          if (c.showSeconds) {
            t._createWidget('S', 0, 59);
            t._curFocus = 'S';
          }
          //初始化分
          if (c.showMinutes) {
            t._createWidget('M', 0, 59);
            t._curFocus = 'M';
          }
          //初始化时
          if (c.showHours) {
            t._createWidget('H', 0, 23);
            t._curFocus = 'H';
          }
          if (!t._curFocus) return false;
          t.setValue();
          t._initEvents();
        });
      } else {
        t._isrenderred = true;
      }
      return t;
    },
    //隐藏
    hide: function () {
      var t = this;
      t.Cid.hide();
      t._isShown = false;
      return t;
    },
    //增加
    add: function () {
      var t = this;
      t.$Element[t._curFocus].trigger('focus' + t._evNamespace);
      t.$Widget[t._curFocus].add();
      return t;
    },
    //减少
    discount: function () {
      var t = this;
      t.$Element[t._curFocus].trigger('focus' + t._evNamespace);
      t.$Widget[t._curFocus].discount();
      return t;
    },
    //计算坐标
    calPosition: function () {
      var t = this, b = $.getBound(t.Jid[0]);
      return {
        x: b.x,
        y: b.y + b.h
      }
    },
    //设置坐标
    setPosition: function (x, y) {
      var t = this;
      t.Cid.css({left: x + 'px', top: y + 'px', position: 'absolute'});
      return t;
    },
    //设置焦点
    setFocus: function (type) {
      var t = this;
      if (!type) type = 'H';
      var element = t.$Element[type];
      element.size() > 0 && element.trigger('focus' + t._evNamespace);
      return t;
    },
    //格式化
    format: function (time) {
      var t = this, c = t.config, util = ST.TimePicker.util, timeObject = util.getTimeObject(time);
      var h = timeObject['H'], m = timeObject['M'], s = timeObject['S'], timeString = c.timeFormat, encode_string;
      encode_string = util.encode(c.timeFormat);
      if (c.showHours) {
        var _h = 'H';
        if (/HH/.test(encode_string)) {
          _h = 'HH';
          h = util.getPrefix(h) + h;
        }
        timeString = timeString.replace(_h, h);
      }
      if (c.showMinutes) {
        var _m = 'm';
        if (/mm/.test(encode_string)) {
          _m = 'mm';
          m = util.getPrefix(m) + m;
        }
        timeString = timeString.replace(_m, m);
      }
      if (c.showSeconds) {
        var _s = 's';
        if (/ss/.test(encode_string)) {
          _s = 'ss';
          s = util.getPrefix(s) + s;
        }
        timeString = timeString.replace(_s, s);
      }
      return timeString;
    },
    //获得时间
    getTime: function () {
      return _private.currentTime;
    },
    //设置时间
    setTime: function (type, time) {
      var t = this;
      if (!type) {
        _private.currentTime = ST.TimePicker.util.getTimeObject(time);
      } else {
        _private.currentTime[type] = time;
      }
      t.onChange && t.onChange();
      return t;
    },
    //设置值
    setValue: function (time) {
      var t = this;
      if (!time) {
        var h = 0, m = 0, s = 0;
        if (t.$Widget['H']) {
          h = t.$Widget['H'].getNumValue();
        }
        if (t.$Widget['M']) {
          m = t.$Widget['M'].getNumValue();
        }
        if (t.$Widget['S']) {
          s = t.$Widget['S'].getNumValue();
        }
        time = [];
        time.push(h);
        time.push(m);
        time.push(s);
      }
      t.setTime('', time);
      t._fillValue(time);
      return t;
    },
    //填充值
    _fillValue: function (time) {
      var t = this, tagName = t.Jid[0].tagName, timeString;
      if (!time) time = t.getTime();
      timeString = t.format(time);
      if (/a|button/i.test(tagName)) {
        t.Jid.html(timeString);
      }
      if (/input|textarea/i.test(tagName)) {
        t.Jid.val(timeString);
      }
      return t;
    },
    //接口
    onChange: '' //值改变时触发
  }.init(id, config);
};

$.extend(ST.TimePicker, {
  //默认选项
  defaults: {
    //默认时间（默认当前时间,,支持Array和Date两种格式）
    defaultTime: new Date(),
    //最小时间(对应时分秒,支持Array和Date两种格式)
    minTime: [0, 0, 0],
    //最大时间(对应时分秒,支持Array和Date两种格式)
    maxTime: [23, 59, 59],
    //增量(对应时分秒)
    increment: [1, 1, 1],
    //时间格式
    timeFormat: 'HH:mm:ss',
    //触发事件（若设为'init',则初始化显示）
    showOn: 'init',
    //外观
    className: 'timepicker',
    //z-index
    zIndex: 1000,
    //是否显示小时
    showHours: true,
    //是否显示分钟
    showMinutes: true,
    //是否显示秒
    showSeconds: true,
    //模板
    template: 'common_timepicker'
  },
  //工具包
  util: {
    //键值
    keycode: {
      'LEFT': 37,
      'RIGHT': 39,
      'TAB': 9
    },
    //正则:
    pattern: {
      regkey: '([.*+?^=!:${}()|[\]/\\])'
    },
    //转义正则关键字
    encode: function (s) {
      return s.replace(new RegExp(this.pattern.regkey, 'g'), '\\$1');
    },
    getPrefix: function (n) {
      if (isNaN(parseInt(n, 10))) return '0';
      return n < 10 ? '0' : '';
    },
    isDate: function (obj) {
      return  (typeof obj == 'object') && obj.constructor === Date;
    },
    getTimeObject: function (v) {
      var dt = {'H': 0, 'M': 0, 'S': 0};
      if ($.isArray(v)) {
        dt['H'] = parseInt(v[0], 10) || 0;
        dt['M'] = parseInt(v[1], 10) || 0;
        dt['S'] = parseInt(v[2], 10) || 0;
      } else if (this.isDate(v)) {
        var h, m, s;
        h = v.getHours();
        m = v.getMinutes();
        s = v.getSeconds();
        dt = {'H': h, 'M': m, 'S': s};
      }
      return dt;
    }
  }
});