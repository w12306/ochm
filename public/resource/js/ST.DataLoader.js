/*
  扩展自ST.Pager
*/
ST.DataLoader = function (id, ops) {
  var config = {
    url: '',							          //从服务器获取数据的Url
    method:'get',                   //提交方式get|post
    dataType: 'json',				        //数据类型json|jsonp
    contentType: 'json',            //内容类型json|html
    pagerId: 'st_pager',			      //分页容器Id
    pagerConfig: {},					      //分页相关配置(参考ST.pager)
    pagerKeys: ['page', 'pagesize'],//依次为页码，页数，默认为['page','pagesize']
    enablePager: true,						  //是否分页
    contentTmp: 'data_content',     //内容模板(仅适用于contentType为json)
    tempPars: {},                   //模板参数
    extraPars: {} 							    //页面额外参数，请求数据时一同发送
  };
  return{
    //初始化
    init: function (ops) {
      var t = this;
      t.setting = $.extend({}, config);
      $.extend(t.setting, ops);
      var s = t.setting;
      t.Jid = $('#' + id);
      if (t.Jid.length == 0) return t;
      //载入数据
      t.loadContent();
      delete t.init;
      return t;
    },
    handlePager: function (dn, cp) {//dn：数据条数 cp：当前页数
      var t = this, s = t.setting;
      if (!s.enablePager) return;
      if (!t.$pager) {//初始化分页
        var _pconfig = s.pagerConfig;
        $.extend(_pconfig, {dataNum: dn || 0, dynamic: true});
        t.$pager = new ST.Pager(s.pagerId, _pconfig, function (cp) {
          t.loadContent(cp);
        })
      } else {//更新分页
        t.$pager.setPage(cp || 1);
        t.$pager.update(dn || 0);
      }
    },
    //读取数据
    loadContent: function (cp) {//cp：当前页数
      var t = this, s = t.setting, pars;
      cp = cp || 1;
      pars = t.getPars(cp);
      ST.tipMsg({loading: '数据载入中，请稍后...'}, 0);
      ST.getJSON(s.url, pars, function (j) {
          var dl, dn;
          t._data = j.data;//保存当前页数据
          t.beforeLoad && t.beforeLoad(t._data, cp);
          dn = t._data.datanum || 0;
          dl = t._data.datalist || [];
          ST.hideMsg();
          t.renderUI(dl, cp);
          t.handlePager(dn, cp);
          t.onLoad && t.onLoad(t._data, cp);
        },
        function (e) {
          t.onLoadError && t.onLoadError(e);
        }, s.method, s.dataType);
    },
    //渲染界面
    renderUI: function (dl, cp) {//dl：数据列表 cp：当前页数
      var t = this, s = t.setting;
      cp = cp || 1;
      var content = dl;
      if (!/^html$/i.test(s.contentType)) {
        content = ST.JTE.fetch(s.contentTmp).getFilled({data: dl, cp: cp, pars: s.tempPars});
      }
      t.Jid.html(content);
    },
    //获取参数
    getPars: function (cp) {//cp：当前页数
      var t = this, s = t.setting, pars = s.extraPars || {};
      if (s.enablePager) {
        var ps = s.pagerConfig.pageSize, pkeys = s.pagerKeys || [];
        cp = cp || 1;
        pars[pkeys[0] || 'page'] = cp;
        pars[pkeys[1] || 'pagesize'] = ps;
      }
      return pars;
    },
    clearPars:function(){
        var t = this, s = t.setting;
        s.extraPars={};
    },
    //设置参数
    setPars: function (key, val) {
      var t = this, s = t.setting;
      val = val || '';
      if (key) {
        s.extraPars[key] = val;
      } else {
        s.extraPars = val;
      }
    },
    //设置模板参数
    setTempPars: function (key, val) {
      var t = this, s = t.setting;
      if (key) {
        s.tempPars[key] = val;
      } else {
        s.tempPars = val;
      }
    },
    onLoad: '',//数据载入后的操作
    beforeLoad: '',//数据载入前的操作
    onLoadError: ''//数据载入出错的操作
  }.init(ops);
};