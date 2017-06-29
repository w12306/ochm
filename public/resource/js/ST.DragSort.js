/*
 区域拖拽效果
 */
ST.DragSort = function (ops) {
  return{
    enable: false,
    //相关默认配置
    init: function (o) {
      var t = this,
        setting = {
          id: "", //拖拽区域的ID
          ad: false//动画开关
        };
      for (var n in setting) {
        o[n] = o[n] || setting[n];//获取参数
      }
      t.setting = o;
      t.container = $(o.id);
      t.items = $(o.id).find(".dragC");
      var d = t.items.eq(0), dc;
      if (t.container.size() == 0 || d.size() == 0) return;//如果获取不到元素退出操作
      t.dc = {
        width: d.outerWidth(true),
        height: d.outerHeight(true)
      }
      t.getNums();
      t.initEvent();
      t.enable = true;
      delete t.init;
      return t;
    },
    initEvent: function () {
      var t = this, o = t.setting, li = t.items, dc = t.dc,
        container = t.container, preIdx, opLi = null;
      $(o.id + ' .dragable').drag("start",function (ev, dd) {
        if (!t.enable) return false;
        //设置限制拖动范围
        preIdx = null;
        dd.limit = container.offset();
        dd.limit.bottom = dd.limit.top + container.outerHeight() - $(this).outerHeight();
        dd.limit.right = dd.limit.left + container.outerWidth() - $(this).outerWidth();
        t.onDragEnd && t.onDragEnd(); //开始拖拽
        return $(this).clone().css({
          "position": "absolute",
          "opacity": .75,
          "border": "2px dotted #ff0000",
          "cursor": "move"
        }).appendTo(document.body);

      }).drag(function (ev, dd) {
          $(dd.proxy).css({
            top: Math.min(dd.limit.bottom, Math.max(dd.limit.top, dd.offsetY)),
            left: Math.min(dd.limit.right, Math.max(dd.limit.left, dd.offsetX))
          });
          var idx = t.getIdxByPos($(dd.proxy));//获取拖动到的id
          if (preIdx != null) {
            if (preIdx != idx) {
              li.eq(preIdx).removeClass("dragin");
              preIdx = idx;
              li.eq(idx).addClass("dragin");
            }
          } else {
            preIdx = idx;
            opLi = idx;
            li.eq(idx).addClass("dragin");
          }
        }).drag("end", function (ev, dd) {
          li.eq(preIdx).removeClass("dragin");
          var proxy = $(dd.proxy).offset();//记录拖动到的位置
          var idx = t.getIdxByPos($(dd.proxy)); //获取拖动到的id
          if (idx > li.length - 1) {
            preIdx = opLi = null;
            $(dd.proxy).remove();
            return;
          }
          var pos = t.getPosByIdx(idx);
          $(dd.proxy).css({
            top: $(this).offset().top,
            left: $(this).offset().left
          });//重置初始位置
          //开始动画
          var fn = function () {
            $(dd.proxy).remove();
            curLi = li.eq(idx);
            var p = $(dd.target)[0].parentNode;
            curLi.children().appendTo(p);
            $(dd.target).appendTo(curLi);
            t.onDragEnd && t.onDragEnd({from: opLi, to: idx});
            preIdx = opLi = null;
          }
          if (o.ad) {
            $(dd.proxy).animate({
              top: pos.top,
              left: pos.left
            }, 420, fn);
          } else {
            fn();
          }
        });
      $(window).bind('resize.dragable', function () {
        t.getNums();
      });
    },
    /*
     根据坐标获取索引
     */
    getIdxByPos: function (offset) {
      var t = this, o = t.setting, dc = t.dc;
      var a = t.container.offset();
      var b = {
        x: offset.offset().left - a.left,
        y: offset.offset().top - a.top
      };
      var col = Math.floor(b.x / dc.width);
      var row = Math.floor(b.y / dc.height);
      return row * o.cols + col;
    },
    /*
     根据索引获取坐标
     */
    getPosByIdx: function (idx) {
      var t = this, o = t.setting, dc = t.dc, a = t.container.offset(), n, x, y;
      x = idx % o.cols;
      y = Math.floor(idx / o.cols);
      return {
        top: y * (dc.height) + a.top,
        left: x * (dc.width) + a.left
      }
    },
    /*
     获取行列数
     */
    _getNums: function () {
      var t = this, cWidth, num, cols, rows;
      cWidth = t.container.outerWidth(true);
      num = t.items.length;
      cols = Math.floor(cWidth / t.dc.width);
      rows = Math.ceil(num / cols);
      return {
        rows: rows,
        cols: cols
      };
    },
    getNums: function () {
      var t = this;
      $.extend(t.setting, t._getNums());
    },
    /*
     销毁
     */
    dispose: function () {
      var t = this,
        s = t.setting;
      $(o.id + ' .dragable').unbind("");
    },
    onDragStart: "",
    onDragEnd: ""
  }.init(ops)
};