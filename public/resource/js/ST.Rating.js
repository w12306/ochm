/*
 星级评分插件
 created & last edited by ZS 2013-8-8
 2013-8-8 update: 整理代码

 todo:部分功能待实现
 */

/*
 var ST = require('ST');
 */
ST.Rating = function (id, ops) {
  //默认配置
  var _config = {
    number: 5,                  //显示星星数
    total: 10,                  //总分
    score: '',                  //当前得分
    size: 23,                   //星星尺寸（单位px）
    half: false,                //是否允许半星
    //decimal: 0.1,             //评分精度 todo
    //readOnly:false,           //是否只读 todo
    //showCancelBtn:false,      //是否显示取消按钮 todo
    className: 'rating',       //外观
    animate: false,            //是否使用动画效果
    duration: 300,             //动画时长
    template: 'common_rating'  //模板
  };
  //私有成员
  var _private = {
    currentScore: ''
  };
  return{
    //事件命名空间
    _evNamespace: '.st_rating',
    //事件回调方法
    _evHandler: {
      'mouseover': function (e) {
        var t = e.data.t;
        t._curPos = t.$ratingArea.offset().left;//计算当前位置
        t.onMouseover && t.onMouseover();
      },
      'mousemove': function (e) {
        var t = e.data.t, pos, score;
        pos = t.calPosition(e);
        score = t.calScoreByPos(pos);
        t.$hoverArea.css('width', pos);
        t.$textArea.html(score);
      },
      'mouseout': function (e) {
        var t = e.data.t, _score = t.getScore();
        t.$hoverArea.css('width', 0);
        t.$textArea.html(_score);
        t.onMouseout && t.onMouseout();
      },
      'click': function (e) {
        var t = e.data.t, pos, score;
        pos = t.calPosition(e);
        score = t.calScoreByPos(pos);
        t.setPosition(pos);
        t._setScore(score);
      }
    },
    //初始化
    init: function (id, conf) {
      var t = this;
      t.config = $.extend(_config, conf || {});
      t.controlId = id;
      t.Jid = $('#' + id);
      t._setup();
      delete t.init;
      return t;
    },
    _setup: function () {
      var t = this, c = t.config, id = t.controlId;
      //构建界面
      t.Jid.addClass(c.className);
      t.renderUI();
      //缓存元素
      t.$textArea = $('#' + id + '_text_area');
      t.$ratingArea = $('#' + id + '_rating_area');
      t.$hoverArea = $('#' + id + '_hover_area');
      t.$activeArea = $('#' + id + '_active_area');
      //静态信息
      t._constInfo = {
        width: parseInt(c.size, 10) / (c.half ? 2 : 1),//最小宽度
        score: (parseInt(c.total, 10) / parseInt(c.number, 10)) / (c.half ? 2 : 1),//最小分值
        number: parseInt(parseInt(c.number, 10) * (c.half ? 2 : 1), 10)//值的可能个数（=星星数/精度)
      };
      //设置初始分值
      t.setScore(c.score);
      //初始化事件
      t._initEvents();
      return t;
    },
    _initEvents: function () {
      var t = this;
      t.$ratingArea
        .on('mouseover' + t._evNamespace, {t: t}, t._evHandler.mouseover)
        .on('mousemove' + t._evNamespace, {t: t}, t._evHandler.mousemove)
        .on('mouseout' + t._evNamespace, {t: t}, t._evHandler.mouseout)
        .on('click' + t._evNamespace, {t: t}, t._evHandler.click);
      return t;
    },
    //渲染界面
    renderUI: function () {
      var t = this, c = t.config;
      if(c.template){
        var _score = t.getScore();
        ST.JTE.fetch(c.template).toFill(id, {controlId: id, config: c, score: _score});
      }
      return t;
    },
    /*
     计算位置
     @pars
     e - 事件对象
     @return
     当前位置
     */
    calPosition: function (e) {
      var t = this, x, w;
      x = Math.ceil((e.pageX - t._curPos) / t._constInfo.width);
      w = x * t._constInfo.width;
      return w;
    },
    //设置位置
    setPosition: function (pos) {
      var t = this, c = t.config;
      if (c.animate) {
        t.$activeArea.stop().animate({
          'width': pos + 'px'
        }, c.duration);
      } else {
        t.$activeArea.css('width', pos + 'px');
      }
      return t;
    },
    //由分值计算位置
    calPosByScore: function (score) {
      var t = this;
      score = parseInt(score, 10) || 0;
      return  score / t._constInfo.score * t._constInfo.width;
    },
    //由位置计算分值
    calScoreByPos: function (pos) {
      var t = this;
      return  pos / t._constInfo.width * t._constInfo.score;
    },
    //设置分值
    _setScore:function(score){
      var t = this;
      _private.currentScore = score;
      t.$textArea.html(score);
      t.onSelected && t.onSelected();
    },
    setScore: function (score) {
      var t = this, pos = t.calPosByScore(score);
      t.setPosition(pos);
      t._setScore(score);
    },
    //获取分值
    getScore: function () {
      return _private.currentScore;
    },
    //接口
    onMouseover: '',
    onMouseout: '',
    onSelected: ''
  }.init(id, ops);
};