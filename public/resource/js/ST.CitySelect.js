/*
 城市选择插件
 created by zhangshu 2013-04-09

 update log:
 -----------------------------------------
 2014-05-16
 (1) 全选性能优化
 (2) 修正onChange多次触发bug
 (3) 兼容之前版本

 2013-08-21
 (1) 修正onClistFilled接口触发时机

 2013-08-15
 (1) 修复选中数据bug

 2013-08-06
 (1) 优化城市数据选择器
 (2) bug修复

 2013-07-22
 (1) 统一插件配置方式（id,ops）
 (2) 删除默认值INPUT
 (3) 程序优化
 -----------------------------------------
 */
ST.CitySelect = function (id, ops) {
    //默认配置
    var _config = {
        dataSource: ST.cityData,                        //数据源
        idkey: 'id',                                    //标识键名
        template: 'common_cityselect',                  //模板(为空则为页面输出)
        template_plist: 'common_cityselect_plist',      //省份列表模板
        template_clist: 'common_cityselect_clist',      //城市列表模板
        className: 'dataSelect dataSelect-listing dataSelect-area', //外观
        action:{
            p:"",                                         //获取省数据
            c:""                                          //获取市数据
        },
        selected: [],                                    //已选值
        readonly: false                                 //是否只读
    };
    return {
        /*
         初始化
         */
        init: function (id, ops) {
            var t = this;
            t.config = _config;
            $.extend(t.config, ops);
            var c = t.config;
            t.Jid = $('#' + id);
            if (t.Jid.length == 0) return t;
            if(!c.dataSource && c.action.p){
                ST.getJSON(c.action.p,{},function(j){
                    c.dataSource = j.data;
                    t._setup();
                });
            }else{
                t._setup();
            }
            delete t.init;
            return t;
        },
        /*
         初始化设置
         */
        _setup: function () {
            var t = this, c = t.config;
            //定义数据选择器
            t.$DataSelector = new ST.CityDataSelector({
                dataSource: c.dataSource,
                idkey: c.idkey
            });
            //缓存元素
            t.$elements = {
                'plist': $('#' + id + '_plist'),
                'clist': $('#' + id + '_clist')
            };
            //缓存数据
            t.datalist = {
                'plist': t._dataFormat('plist'),
                'clist': t._dataFormat('clist')
            };
            //初始化选中值
            t.selected = c.selected || [];
            //构建UI
            if (c.template) t._renderUI();
            //填充省份列表
            t.fillPlist();
            //初始化事件
            if (c.readonly) {
                t.disable();
            } else {
                t._initEvents();
            }
        },
        /*
         初始化事件
         */
        _initEvents: function () {
            var t = this;
            var _evHandler = function (e, flag) {
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
                //fixing 2014-05-16 前版本城市列表全选
                if(/_clist_checkAll$/.test(this.id)){
                    cmd = 'selectAll'
                }
                //fixing end
                t[cmd] && t[cmd].call(t, e, oPars, flag);
            };
            t.Jid
                .on('click', '[data-cmd]', _evHandler)
                .on('click', '[data-pid]', $.proxy(t.isCheck,t));
            return t;
        },
        /*
         构建界面
         */
        _renderUI: function () {
            var t = this, c = t.config;
            if (!c.template) return t;
            t.Jid.html(ST.JTE.fetch(c.template).getFilled({
                controlId: id, config: c
            })).attr('class', c.className);
            return t;
        },
        /*
         填充省份列表
         */
        fillPlist: function () {
            var t = this, c = t.config;
            var filteredPVS = t.getFilteredPVS();
            //载入UI
            ST.JTE.fetch(c.template_plist).toFill(id + '_plist', {
                controlId: id, config: c, data: t.datalist['plist'], selected: filteredPVS
            });
            //判断是否载入clist
            filteredPVS.length>0 && t._fillClist(filteredPVS);
            //检测选中状态
//            window.setTimeout(function () {
//                var $pvs = t.getAreaNodes();
//                $pvs.each(function () {
//                    t._isCheck($(this).attr('id'));
//                });
//            }, 50);
            return t;
        },
        /*
         填充城市列表
         */
        fillClist: function (e) {
            var t = this,c= t.config, pids = t.getCheckedPVS();//筛选Dom数据
            //待做缓存优化
            if(c.action.c){
                ST.getJSON(c.action.c.format(pids),{},function(j){
                 /*
                    var j={
                        data:{
                            "14":[
                                {"name":"呼和浩特市","id":"217"},
                                {"name":"包头市","id":"218"},
                                {"name":"乌海市","id":"219"}
                            ],
                            "16":[
                                {"name":"呼和浩特市","id":"217"},
                                {"name":"包头市","id":"218"},
                                {"name":"乌海市","id":"219"}
                            ]
                        }
                    };
                    */
                    c.dataSource["clist"]  = j.data;
                    t.datalist['clist'] = t._dataFormat('clist');
                    t._fillClist(pids);
                });
            }else{
                t._fillClist(pids);
            }
            t._fillClist(pids);
            return t;
        },
        _fillClist:function(pids){
            var t = this, c = t.config,_data,_selected;
            _data = $.grep(t.datalist['clist'], function (v) {
                return $.inArray(v[c.idkey],pids) > -1;
            }),
            _selected = $.grep(t.selected, function (v) {
                var p = t.getPvsByCid(v);
                if(p){
                    return $.inArray(p[c.idkey],pids) > -1;
                }
                return false;
            });
            //重置selected
            t.selected = _selected;
            //载入UI
            ST.JTE.fetch(c.template_clist).toFill(id + '_clist', {
                controlId: id, config: c, data: _data, selected: _selected
            });

            //检测选中状态
            window.setTimeout(function () {
                var $pvs = t.getPvsNodes('clist');
                $pvs.each(function () {
                    t._isCheck($(this).attr('id'));
                });
                t.onClistFilled && t.onClistFilled();
            }, 50);
            return t;
        },
        /*
         数据格式化
         @pars
         type 类型('plist'|'clist')
         @return
         格式化后的数据 (Array [{id:'',name:'',data:[{id:'',name:''}……]}……])
         */
        _dataFormat: function (type) {
            var t = this, c = t.config;
            if (!type) type = 'plist';
            var data = c.dataSource, dlist = data[type], _data = [], j = 0;
            if(!dlist) return _data;
            $.each(dlist, function (k, v) {
                var d = type == 'plist' ? t.getAreaById(k) : t.getPvsById(k);
                if ( d ){
                    _data[j] = $.extend(d, {
                        data: v || []
                    });
                    j++;
                }
            });
            return _data;
        },
        /*
         获取已筛选的省
         @return Array
         */
        getFilteredPVS: function () {
            var t = this, cids = t.selected;
            if (cids.length == 0) return [];

            var c = t.config, pids = [], _p = null;
            $.each(cids, function (i,cid) {
                var p = t.getPvsByCid(cid);
                if(p && p!=_p){
                    pids.push(p[c.idkey]);
                    _p = p;
                }
            });
            return pids;
        },
        /*
         获取DOM中已选中的省
         @return Array
         */
        getCheckedPVS: function () {
            var t = this, c = t.config,
                $p = t.getPvsNodes('plist'),
                result = [];
            $p.each(function () {
                var $this = $(this);
                if ($this.attr('checked')) {
                    result.push($this.attr('data-' + c.idkey));
                }
            });
            return result;
        },
        /*
         选择城市：用于clist
         @pars
         e 事件参数
         pars 方法参数
         */
        selectCity: function (e) {
            var t = this;
            t.onCityCheck && t.onCityCheck(e);
            t.isCheck(e);
            t._updateValue();
            return t;
        },
        /*
         选择省份：用于clist
         @pars
         e 事件参数
         */
        selectPVS: function (e) {
            var t = this,
                $this = $(e.currentTarget),
                flag = !!$this.attr('checked');
            t._checkAll($this.attr('id'),flag);
            t.onPvsCheck && t.onPvsCheck(e);
            t.isCheck(e);
            t._updateValue();
            return t;
        },
        /*
         全选：用于clist
         @pars
         e 事件参数
         */
        selectAll: function (e) {
            var t = this,
                $this = $(e.currentTarget),
                flag = !!$this.attr('checked');
            t._checkAll($this.attr('id'),flag);
            t._updateValue();
            return t;
        },
        /*
         设置选中城市值：用于clist
         @pars
         value 选中城市值
         is_remove 是否删除
         */
        _updateValue:function(){
            var t = this, c = t.config,
                $c = t.getCityNodes().filter(':checked');
            t.selected = $c.map(function(){
                return $(this).attr('data-'+ c.idkey);
            }).get();
            t.onChange && t.onChange.call(t);
            return t;
        },
        /*
         获取已选城市值
         @return Array
         */
        getSelected: function () {
            return this.selected;
        },
        /*
         清空已选城市
         */
        clearSelected: function () {
            var t = this;
            t.$elements['clist'].find(':checkbox').checked(false);
            t.selected = [];
        },
        /*
         全选/取消全选
         @pars
         e 事件参数
         */
        checkAll: function (e) {
            var t = this, element = e.target;
            if (!element) return t;
            var $this = $(element), id = $this.attr('id');
            t._checkAll(id, !!$this.attr('checked'));
            return t;
        },
        _checkAll: function (id, flag) {
            var t = this, children = t.getChildNodes(id);
            children.each(function () {
                var $this = $(this);
                $this.attr('checked', flag);
                if ($this.is('[data-cmd="checkAll"],[data-cmd="selectPVS"]')) {
                    var _id = $this.attr('id');
                    t._checkAll(_id, flag);
                }
            });
            return t;
        },
        /*
         是否改变选项勾选状态
         @pars
         id 选项id
         @return
         true|false（Boolean）
         */
        isCheck: function (e) {
            var pid = $(e.currentTarget).attr('data-pid');
            pid && this._isCheck(pid);
        },
        _isCheck: function (id) {
            var t = this;
            if (id == undefined || id == null) return false;//判断选项是否存在
            var $this = $('#' + id), c = t.getChildNodes(id),
                checked = !!$this.attr('checked'),
                flag = (c.length == c.filter(':checked').length) ;
            if (checked != flag) {
                $this.attr('checked', flag);
                var pid = $this.attr('data-pid');
                t._isCheck(pid);//继续查找父选项
                return true;
            }
            return false;
        },
        /*
         获取大区选项：用于plist
         @return
         对应plist大区选项(JQuery Object)
         */
        getAreaNodes: function () {
            return $('#' + id + '_plist').find('.fn_' + id + '_a');
        },
        /*
         获取省份选项
         @pars
         type  类型('plist'|'clist')
         @return
         对应省份选项(JQuery Object)
         */
        getPvsNodes: function (type) {
            return $('#' + id + '_' + type).find('.fn_' + id + '_p');
        },
        /*
         获取城市选项：用于clist
         @return
         对应clist城市选项(JQuery Object)
         */
        getCityNodes: function () {
            return $('#' + id + '_clist').find('.fn_' + id + '_c');
        },
        /*
         获取子选项
         @pars
         id 选项id
         @return
         对应的全部子选项(JQuery Object)
         */
        getChildNodes: function (id) {
            return this.Jid.find('[data-pid][data-pid=' + id + ']');
        },
        /*
         由大区id获取对应的大区
         @pars
         id 大区id
         @return
         城市（Object {id:'',name:''}）
         */
        getAreaById: function (id) {
            return this.$DataSelector.getAreaById(id);
        },
        /*
         由省份id获取对应的省
         @pars
         id 省份id
         @return
         省份（Object {id:'',name:''}）
         */
        getPvsById: function (id) {
            return this.$DataSelector.getPvsById(id);
        },
        /*
         由城市id获取对应的城市
         @pars
         id 城市id
         @return
         城市（Object {id:'',name:''}）
         */
        getCityById: function (id) {
            return this.$DataSelector.getCityById(id);
        },
        /*
         由城市id获取对应的省
         @pars
         pid 城市id
         @return
         省份（Object {id:'',name:''}）
         */
        getPvsByCid: function (cid) {
            return this.$DataSelector.getPvsByCid(cid);
        },
        /*
         由省份id获取对应的城市列表
         @pars
         pid 省份id
         @return
         城市列表（Array [{id:'',name:''}……]）
         */
        getClistByPid: function (pid) {
            return this.$DataSelector.getClistByPid(pid);
        },
        /*
         启用
         */
        enable: function () {
            var t = this;
            t.Jid.removeClass('disabled');
            t.Jid.find(':checkbox').attr('disabled', false);
            t._initEvents();
            return t;
        },
        /*
         禁用
         */
        disable: function () {
            var t = this;
            t.Jid.off('click change').addClass('disabled');
            t.Jid.find(':checkbox').attr('disabled', true);
            return t;
        },
        onChange: '',     //城市选中值发生改变时
        onCityCheck: '',  //点选城市时触发
        onPvsCheck: '',   //点选省份时触发
        onClistFilled: '' //城市列表填充后触发
    }.init(id, ops)
};

/*
 城市数据选择器
 {
 dataSource:'',
 idkey:'id'
 }
 */
ST.CityDataSelector = function (config) {
    return{
        init: function (config) {
            var t = this;
            t.dataSource = config.dataSource;
            t.idkey = config.idkey || 'id';
            delete t.init;
            return t;
        },
        /*
         由大区id获取对应的大区
         @pars
         id 大区id
         @return
         城市（Object {id:'',name:''}）
         */
        getAreaById: function (id) {
            var t = this, alist = t.dataSource.alist, a;
            a = alist.getJson(t.idkey, id);
            return a;
        },
        /*
         由省份id获取对应的省
         @pars
         id 省份id
         @return
         省份（Object {id:'',name:''}）
         */
        getPvsById: function (id) {
            var t = this, plist = t.dataSource.plist, p;
            $.each(plist, function (k, v) {
                if (p = v.getJson(t.idkey, id)) {
                    return false;
                }
            });
            return p;
        },
        /*
         由城市id获取对应的城市
         @pars
         id 城市id
         @return
         城市（Object {id:'',name:''}）
         */
        getCityById: function (id) {
            var t = this, clist = t.dataSource.clist, c;
            $.each(clist, function (k, v) {
                if (c = v.getJson(t.idkey, id)) {
                    return false;
                }
            });
            return c;
        },
        /*
         由城市id获取对应的省
         @pars
         pid 城市id
         @return
         省份（Object {id:'',name:''}）
         */
        getPvsByCid: function (cid) {
            var t = this, clist = t.dataSource.clist, pid , p;
            if (!cid && cid != 0) return p;
            $.each(clist, function (k, v) {
                if (v.getJson(t.idkey, cid)) {
                    pid = k;
                    return false;
                }
            });
            p = t.getPvsById(pid);
            return p;
        },
        /*
         由省份id获取对应的城市列表
         @pars
         pid 省份id
         @return
         城市列表（Array [{id:'',name:''}……]）
         */
        getClistByPid: function (pid) {
            var t = this, clist = t.dataSource.clist, _clist;
            if (!pid && pid != 0) return _clist;
            $.each(clist, function (k, v) {
                if (pid == k) {
                    _clist = v;
                    return false;
                }
            });
            return _clist;
        }
    }.init(config);
};