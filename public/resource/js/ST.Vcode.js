/*
 验证码插件
 created by gyj

 update:
----------------------------------------------
 2013-06-21 by zhangshu
 （1）添加模板配置

 2013-08-23 by zhangshu
 （1）更改显示容器样式默认值 authCode-> authcode
-----------------------------------------------
*/

ST.Vcode = function (ops) {
  if (!ops) ops = {};
  var s = {
    controlID: 'vcode',
    displayID: '',
    displayCN: 'authcode',
    templ: 'common_vcode',
    iconServer: ST.PATH.ROOT + ST.PATH.VCODE,
    zIndex: 99999
    //,culture: 'en'
    //,maxLength: 4
  };
  this.isShown = false;
  this.init = function () {
    $.extend(s, ops);
    var t = this;
    t.cID = s.controlID;
    t.controlID = $("#" + t.cID);
    t.displayID = $("#" + s.displayID);
    t.controlID.attr("autocomplete", 'off');
    if (t.displayID.length > 0) {
      t.displayID.addClass(s.displayCN);
      t.show();
    } else {
      t.controlID.bind("focus.vcode", function () {
        t.show();
      });
    }
    delete this.init;
    return this;
  };
  this.show = function () {
    var t = this, d = t.displayID, b = $.getBound(t.cID), c = $("#PVCode_Img_" + t.cID);
    t.isShown = true;
    if (d.size() == 0) {
      d = $('<div id="' + "PVCode_" + t.cID + '" class="' + s.displayCN + '" style="position:relative; z-index:"' + s.zIndex + '></div>').appendTo("body");
      t.displayID = d;
    }
    if (c.size() == 0) {
      this.render();
      d.bind("mousedown.vcode", function (e) {
        $.stopEvent(e);
        t.scode();
      });
    }
    d.bind("mousedown.vcode", function (e) {
      $.stopEvent(e);
      t.scode();
    });
    if (!s.displayID) {
      $(document).unbind("mousedown.vcode").bind("mousedown.vcode", function (e) {
        t.hide(e);
      });
      t.setpos(b.x, b.y - b.h);
    }
    d.show();
    t.scode();
  };
  this.setpos = function (x, y) {
    var t = this;
    if (t.isShown) {
      t.displayID.css({left: x + 'px', top: y + 'px', position: 'absolute'});
    }
  };
  this.hide = function (e) {
    var t = this;
    t.controlID.unbind("mousedown.vcode");
    $(document).unbind("mousedown.vcode");
    t.displayID.hide();
    t.isShown = false;
  };
  this.scode = function () {
    var t = this, img, c = $('#PVCode_Img_' + t.cID);
    if (c.size() > 0) {
      img = s.iconServer + '?rand=' + new Date().getTime();
      t.render(img);
    }
  };
  this.render = function (imgsrc) {
    var t = this, dsid = s.displayID || "PVCode_" + t.cID;
    ST.JTE.fetch(s.templ || 'common_vcode').toFill(dsid, {cID: t.cID, imgsrc: imgsrc});
  };
  return this.init();
};