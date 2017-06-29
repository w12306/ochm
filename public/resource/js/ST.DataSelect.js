/*
 数据选择插件：ST.DataSelect
 created & last edited by ZS 2013-08-19
 require ST.DataLoader

 update 2013-08-19
 (1)细节修改
 update 2013-12-25
 (1)新增searchform方法，弥补搜索无法传参的不足（hack方法，待改进）
 (2)新增postParams（发送参数）和tempParams（模板参数）配置（hack方法，待改进）
 */
ST.DataSelect = function (id, ops) {
  var _config = {
    url: '',                         //服务器通信 地址
    method: 'get',                   //提交方式get|post
    dataType: 'json',				 //数据类型json|jsonp
    postParams:{},                  //发送的额外参数
    tempParams:{},                  //额外的模板参数
    pagesize: 10,                    //每页显示数据条数（若有分页）
    //以下是键名配置
    idkey: 'id',                     //标识符：用来标记选中数据
    searchkey: 'name',               //搜索key   支持[]格式
    pagerkey: ['page', 'pagesize'],  //[当前页，页数]
    //以下是模板配置
    template: 'common_dataselect',                  //主模板(为空则为页面输出)
    template_datalist: 'common_dataselect_datalist',//数据列表模板
    template_selected: 'common_dataselect_selected',//已选数据模板
    template_pager: 'common_dataselect_pager',      //分页模板（若有分页）
    //以下是模板外观配置
    theme: 'dataSelect-listing',       //主题 dataSelect-listing | dataSelect-table(预留) | dataSelect-image(预留)
    skin: '',                          //皮肤（todo：预留）
    size: '',                          //尺寸 dataSelect-small | dataSelect-large
    //以下是选中数据的配置
    selected: [],                    //选中数据id集
    selectedClass: 'active',         //选中样式
    //以下是开关配置
    enableMulti: false,             //是否允许多选
    enableSearch: false,            //是否允许搜索
    enablePager: false              //是否允许分页
  };
  return{
    //依赖列表
    require: ['ST.DataLoader'],
    //初始化
    init: function (id, ops) {
      var t = this;
      t.config = _config;
      $.extend(t.config, ops);
      t.Jid = $('#' + id);
      if (t.Jid.length == 0) return t;
      //加载依赖文件
      ST.getJsList(t.require, function () {
        t._setup();
      }, function () {
        alert(ST.LRes.RequireFail);
      });
      delete t.init;
      return t;
    },
    _setup: function () {
      var t = this, c = t.config;
      if (c.template) t._renderUI();
      t.$elements = {
        list: $('#' + id + '_st_dataselect_list'),//数据列表
        selected: $('#' + id + '_st_dataselect_selected'),//已选列表
        searchSel: $('#' + id + '_st_dataselect_searchSel'),//扩展搜索选择
        searchIpt: $('#' + id + '_st_dataselect_searchIpt'),//搜索框
        searchForm: $('#' + id + '_st_dataselect_searchForm')//搜索表单
      };
      t._initEvents();
      t.fillToSelect();
      return t;
    },
    _initEvents: function () {
      var t = this, c = t.config;
      //绑定事件
      var _evHandler = function (e) {
        var $this = $(this), cmd = $this.data('cmd');
        if (!cmd) return;
        var pars = $this.data('pars'), oPars = {};
        if (pars) {
          pars = pars.split(',');
          for (var i = 0, l = pars.length, d; i < l, d = pars[i]; i++) {
            d = d.split(':');
            oPars[d[0]] = d[1];
          }
        }
        t[cmd] && t[cmd].call(t, e, oPars);
      };
      t.Jid.evProx({
        click: {
          'a,:button': _evHandler
        },
        change: {
          ':checkbox': _evHandler
        }
      });
      t.$elements.searchIpt.on('keydown.dataselect', function (e) {
        if (e.keyCode == 13) {
          t.search();
          e.preventDefault();
        }
      });
    },
    /*
     构建界面
     */
    _renderUI: function () {
      var t = this, c = t.config;
      if (!c.template) return t;
      var _class = ['dataSelect'];
      if (c.theme) _class.push(c.theme);
      if (c.skin) _class.push(c.skin);
      if (c.size) _class.push(c.size);
      t.Jid.html(ST.JTE.fetch(c.template).getFilled({
        controlId: id, config: c
      })).attr('class', _class.join(' '));
      return t;
    },
    fillToSelect: function () {
      var t = this, c = t.config;
      var _loaderConfig = {}, cid = id + '_st_dataselect_list';
      $.extend(_loaderConfig, {
        url: c.url,
        method: c.method,
        pagerId: id + '_st_dataselect_pager',
        pagerKeys: c.pagerkey,
        enablePager: c.enablePager,
        pagerConfig: {
          pageSize: c.pagesize,
          pageDisnum: 2,
          pageTemp: c.template_pager,
          pageNumTemp: c.template_pager + '_num'
        },
        contentTmp: c.template_datalist,
        tempPars: {controlId: id, config: t.config},
        extraPars: c.postParams||{}
      });
      if (c.enablePager) {//分页时查询发送已选数据
        //_loaderConfig.extraPars[c.idkey] = c.selected.join(',');
      }
      t.$Loader = new ST.DataLoader(cid, _loaderConfig);
      t.$Loader.beforeLoad = function (d,cp) {
        var that = this;
        that.setTempPars('selected', t.selected ? t.getSelectedSet(c.idkey) : c.selected);
        that.setTempPars('idkey', c.idkey);
        t.beforeLoad && t.beforeLoad(d, cp);
      };
      t.$Loader.onLoad = function (d, cp) {
        t._cdata = t._dataFormator(d.datalist);
        if (!t.selected) {//查询已选数据
          t.selected = d.selected || [];
          if (!d.selected && !c.enablePager && c.selected.length > 0) {//非分页时前端查询
            for (var i = 0, l = t._cdata.length, dt; i < l, dt = t._cdata[i]; i++) {
              if (c.selected.getIndex(dt[c.idkey]) > -1) t.selected.push(dt);
            }
          }
        }
        t.fillSelected();
        t.onLoad && t.onLoad(d, cp);
      };
    },
    fillSelected: function () {
      var t = this, c = t.config, $seled = t.$elements.selected;
      if ($seled.size() > 0) {
        $seled.html(ST.JTE.fetch(c.template_selected).getFilled({data: t.selected, controlId: id, config: c}));
      }
      return t;
    },
    _dataFormator: function (d) {
      var t = this, _d;
      d = d || [];
      t.dataFormator && (_d = t.dataFormator(d));
      return _d || d;
    },
    getSelected: function(){
      var t = this;
      return t.selected;
    },
    getSelectedSet: function (key) {
      var t = this, seled = t.selected || [], seledset = key ? [] : {};
      for (var i = 0, l = seled.length, d; i < l, d = seled[i]; i++) {
        if (key) {
          seledset.push(d[key]);
        } else {
          $.each(d, function (k) {
            if (!seledset[k]) seledset[k] = [];
            seledset[k].push(d[k]);
          });
        }
      }
      return seledset;
    },
    delAllSelected:function(e){
        var t = this, c = t.config;
        //清空已选
        t.selected = [];
        t.Jid.find('[data-' + c.idkey + ']').toggleClass(c.selectedClass,false);
        t.fillSelected();
        t.onChange && t.onChange(e);
    },
    toggle: function (e, pars) {
      var t = this, c = t.config;
      id = pars[c.idkey];
      var ids = t.getSelectedSet(c.idkey), cdata = t._cdata || [], flag, cseled;
      flag = !!(ids.getIndex(id) > -1);
      cseled = cdata.getJson(c.idkey, id);
      if (c.enableMulti) {
        if (flag) {
          t.selected.removeJson(c.idkey, id);
        } else {
          t.selected.insert(cseled, t.selected.length);
        }
      } else {
        t.selected = flag && cseled ? [] : [cseled];
      }
      if (!c.enableMulti) {
        var items = t.Jid.find('.' + c.selectedClass + '[data-' + c.idkey + '][data-' + c.idkey + '!=' + id + ']');
        items.removeClass(c.selectedClass);
      }
      var item = t.Jid.find('[data-' + c.idkey + '][data-' + c.idkey + '=' + id + ']');
      item.toggleClass(c.selectedClass);
      t.fillSelected();
      t.onChange && t.onChange(e);
    },
    search: function () {
      var t = this, c = t.config,$sel= t.$elements.searchSel, $input = t.$elements.searchIpt, key, val;
      if (!t.$Loader || $input.size() == 0) return;
      key = $sel.val() || c.searchkey; //扩展添加key选择
      val = $input.val();
      t.$Loader.clearPars();
      t.$Loader.setPars(key, val);
      t.$Loader.loadContent();
    },
    searchForm:function(){
        var t = this, $form = t.$elements.searchForm, $elm,key,val;
        if (!t.$Loader || $form.size() == 0) return;
        $elm = $form.find('[name]');
        t.$Loader.clearPars();
        $elm.each(function(){
            var $this = $(this);
            key = $this.attr('name');
            val = $this.val();
            t.$Loader.setPars(key, val);
        });
        t.$Loader.loadContent();
    },
    onChange: '',//(e) 数据变化时执行的操作 e:事件对象
    onLoad: '',//(d,p) 数据载入时执行的操作 d:载入后的数据{datanum:'',datalist:[],selected:[]} p：当前页
    dataFormator: ''//(d) 数据格式化接口 d：数据列表[]
  }.init(id, ops);
};