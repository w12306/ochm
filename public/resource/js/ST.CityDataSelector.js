/*
 城市数据选择器:
 配置：
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
            var t = this, clist = t.dataSource.clist, _clist = null;
            if (!pid && pid != 0) return _clist;
            $.each(clist, function (k, v) {
                if (pid == k) {
                    _clist = v;
                    return false;
                }
            });
            return _clist;
        },
        /*
         由城市id获取对应的区县列表
         @pars
         cid 城市id
         @return
         区县列表（Array [{id:'',name:''}……]）
         */
        getXlistByCid: function (cid) {
            var t = this, xlist = t.dataSource.xlist, _xlist = null;
            if (!cid && cid != 0) return _xlist;
            $.each(xlist, function (k, v) {
                if (cid == k) {
                    _xlist = v;
                    return false;
                }
            });
            return _xlist;
        }
    }.init(config);
};