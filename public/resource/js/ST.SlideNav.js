/*
 简单树形菜单
 created & last edited by ZS 2013-8-8
 var JQ = require('jquery')；//1.7+
 var ST = require('ST');
 * */

ST.SlideNav = function (id, config) {
  //私有成员
  var _private = {

  };
  return {
    //事件命名空间
    _evNamespace: '.st_slidenav',
    //初始化
    init: function (id, config) {
      var t = this;
      t.config = $.extend({}, ST.SlideNav.defaults);
      $.extend(t.config, config);
      t.Jid = $('#' + id);
      if (t.Jid.length == 0) return t;
      t._setup();
      delete t.init;
      return t;
    },
    _setup: function () {
      var t = this;
      t._initEvents();
      return t;
    },
    _initEvents: function () {
      var t = this;
      t.Jid.evProx({
        'click': {
          'a': function () {
            var $this = $(this), cmd = $this.data('cmd');
            if (cmd) {
              var pars = $this.data('pars'), oPars = '';
              if (pars) {
                oPars = {};
                pars = pars.split(',');
                for (var i = 0, l = pars.length, d; i < l, d = pars[i]; i++) {
                  d = d.split(':');
                  oPars[d[0]] = d[1];
                }
              }
              if (t[cmd] && $.Lang.isMethod(t[cmd])) {
                t[cmd](this, oPars);
                return false;
              }
              if (ST[cmd] && $.Lang.isMethod(ST[cmd])) {
                ST.todo(cmd, this, oPars);
                return false;
              }
              return false;
            }
          }
        }
      });
      return t;
    },
    slideNav: function (node) {
      var t = this, c = t.config, $this = $(node), p = $this.parent();
      $this.siblings().stop().slideToggle();
      p.toggleClass('menu-on', !p.hasClass('menu-on'));
      if (c.onlyOneNav) {
        p.siblings()
          .toggleClass('menu-on', false)
          .children('ul').each(function () {
            if ($(this).css('display') != 'none') {
              $(this).slideToggle();
            }
          });
      }
      t.onSlide && t.onSlide();
    },
    //接口
    onSlide:''
  }.init(id, config);
};

$.extend(ST.SlideNav, {
  //默认选项
  defaults: {
    onlyOneNav: false//是否一次只允许展开一个
  },
  //工具包
  util: {

  }
});