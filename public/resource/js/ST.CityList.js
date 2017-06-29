/*区省市县四级扩展*/
ST.CityList = function (ops) {
  //静态数据 实际数据需要从服务端获取
  var CityData = {alist: "", plist: "", clist: "", xlist: ""};
  //静态实例
  var _instance = {
    //获取大区数据
    getArea: function () {
      var t = this,
        a = [],
        s = t.config;
      if (s.nodefault) a.push({text: s.aText, value: ''});
      if (t.data.alist) $.each(t.data.alist, function (i, v) {
        a.push({text: v.name, value: v.id});
      });
      return a;
    },
    //获取大区数据根据省id
    getAreaByPvs: function (c) {
      var t = this, p;
      if($.isArray(t.data.alist)){
        if (c) {
          $.each(t.data.plist, function (i, v) {
            if (v.getJson("id", c)) {
              p = i;
              return false;
            }
          });
        }
        p = t.data.alist.getJson("id", p);
        if (!p) p = t.data.alist[0];
        //对应
        return {
          text: p.name,
          value: p.id
        };
      }else{
        return null;
      }
    },
    //获取省份数据根据城市id
    getPvsByCity: function (c) {
      var t = this, p, j = 0;
      if (c) {
        $.each(t.data.clist, function (i, v) {
          if (v.getJson("id", c)) {
            p = i;
            return false;
          }
        });
      }
      if ($.isArray(t.data.plist)) {
        p = t.data.plist.getJson("id", p);
      } else {
        var a;
        for (var i in t.data.plist) {
          if (!j) j = i;
          if (a = t.data.plist[i].getJson("id", p)) {
            p = a;
            break;
          }
        }
      }
      if (!p) p = t.data.plist[j];
      //对应
      return {
        text: p.name,
        value: p.id
      };
    },
    //获取城市根据区域id
    getCityByDistr: function (c) {
      var t = this, p = '', j = 0;
      if (c) {
        $.each(t.data.xlist, function (i, v) {
          if (v.getJson("id", c)) {
            p = i;
            return false;
          }
        });
      }
      if ($.isArray(t.data.clist)) {
        p = t.data.clist.getJson("id", p);
      } else {
        var a;
        for (var i in t.data.clist) {
          if (!j) j = i;
          if (a = t.data.clist[i].getJson("id", p)) {
            p = a;
            break;
          }
        }

      }
      if (!p) p = t.data.clist[j];
      //对应
      return {
        text: p.name,
        value: p.id
      };
    },
    //获得省份数据根据大区id
    getPvs: function (c) {
      var t = this,
        a = [],
        s = t.config;
      if (s.nodefault) a.push({text: s.pText, value: ''});
      var doPush = function (_d) {
        var arr = [];
        if (_d) {
          $.each(_d, function (i, v) {
            if ($.isArray(_d)) {
              arr.push({text: v.name, value: v.id});
            } else {
              arr = arr.concat(doPush(_d[i]));
            }
          });
        }
        return arr;
      }
      if (c && t.data.plist) return a = a.concat(doPush(t.data.plist[c]));
      if (!s.a) return a = a.concat(doPush(t.data.plist));
      return a;
    },
    //获取城市数据根据省份id
    getCity: function (c) {
      var t = this,
        a = [],
        s = t.config;
      if (s.nodefault) a.push({text: s.cText, value: ''});
      var doPush = function (_d) {
        var arr = [];
        if (_d) {
          $.each(_d, function (i, v) {
            if ($.isArray(_d)) {
              arr.push({text: v.name, value: v.id});
            } else {
              arr = arr.concat(doPush(_d[i]));
            }
          });
        }
        return arr;
      }
      if (c && t.data.clist) return a = a.concat(doPush(t.data.clist[c]));
      if (!s.p) return a = a.concat(doPush(t.data.clist));
      return a;
    },
    //获取地区数据根据城市id
    getDistr: function (c) {
      var t = this,
        a = [],
        s = t.config;
      if (s.nodefault) a.push({text: s.xText, value: ''});
      if (c && t.data.xlist) {
        if (t.data.xlist[c]) $.each(t.data.xlist[c], function (i, v) {
          a.push({text: v.name, value: v.id});
        });
      }
      return a;
    }
  }
  return {
    //变更省份内容
    setPvsData: function (data) {
      var t = this, s = t.config;
      t.$po.changeData(data);
      var cf = t.$po.data[0] || {text: s.pText, value: ''};
      t.$po.setText(cf.text);
      t.$po.scrollToTop();
      t.pid.val(cf.value);
      t._p = cf;
      if (t.config.c) {
        t.setCityData(_instance.getCity.call(t, cf.value));
      } else {
        t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
      }
    },
    //变更城市内容
    setCityData: function (data) {
      var t = this, s = t.config;
      t.$co.changeData(data);
      var cf = t.$co.data[0] || {text: s.cText, value: ''};
      t.$co.setText(cf.text);
      t.$co.scrollToTop();
      t.cid.val(cf.value);
      t._c = cf;
      if (t.config.x) {
        t.setDistrData(_instance.getDistr.call(t, cf.value));
      } else {
        t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
      }
    },
    //变更区域内容
    setDistrData: function (data) {
      var t = this, s = t.config;
      t.$xo.changeData(data);
      var cf = t.$xo.data[0] || {text: s.xText, value: ''};
      t.$xo.setText(cf.text);
      t.$xo.scrollToTop();
      t.xid.val(cf.value);
      t._x = cf;
      t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
    },
    //初始化方法
    init: function (ops) {
      var t = this;
      t.config = {
        a: ops.a,   //大区选择div
        p: ops.p,   //省份选择div
        c: ops.c,   //城市选择div
        x: ops.x,   //县选择div
        aText: ops.aText || "请选择大区",
        pText: ops.pText || "请选择省份",
        cText: ops.cText || "请选择城市",
        xText: ops.xText || "请选择区县",
        data: ops.data,
        nodefault: ops.nodefault || false, //无默认值
        onChange: ops.onChange || "",
        onAreaSelect: ops.onAreaSelect || "",
        onPvsSelect: ops.onPvsSelect || "",
        onCitySelect: ops.onCitySelect || "",
        onDistrSelect: ops.onDistrSelect || ""
      };
      //注册事件
      t.onChange = t.config.onChange;
      t.onAreaSelect = t.config.onAreaSelect;
      t.onPvsSelect = t.config.onPvsSelect;
      t.onCitySelect = t.config.onCitySelect;
      t.onDistrSelect = t.config.onDistrSelect;
      var c = t.config, an, pn, cn, bp = !1, cname,
        aid = $("#" + c.a + "_val"),
        pid = $("#" + c.p + "_val"),
        cid = $("#" + c.c + "_val"),
        xid = $("#" + c.x + "_val");
      t.aid = aid;
      t.pid = pid
      t.cid = cid;
      t.xid = xid;
      if (c.a && !aid.length) throw new Error("aid has not found!");
      if (c.p && !pid.length) throw new Error("pid has not found!");
      if (c.c && !cid.length) throw new Error("cid has not found!");
      if (c.x && !xid.length) throw new Error("xid has not found!");
      t.data = $.extend({},CityData,c.data||{});
      t._instance = _instance;
      //初始化省份下拉框
      if (c.p) {
        t.$po = ST.ddList(c.p, _instance.getPvs.call(t, an), function (e) {
          t._p = e;
          pid.val(e.value);
          if (c.c) {
            t.setCityData(_instance.getCity.call(t, e.value));//变更城市数据
          } else {
            t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
          }

          t.onPvsSelect && t.onPvsSelect(e.value!=''?e:{text:'',value:''});
        });
      }
      if (c.c) {
        t.$co = ST.ddList(c.c, _instance.getCity.call(t, pn), function (e) {
          t._c = e;
          cid.val(e.value);
          if (c.x) {
            t.setDistrData(_instance.getDistr.call(t, e.value));//变更区域数据
          } else {
            t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
          }
          t.onCitySelect && t.onCitySelect(e.value!=''?e:{text:'',value:''});
        });
      }
      if (c.a) {
        t.$ao = ST.ddList(c.a, _instance.getArea.call(t), function (e) {
          t._a = e;
          aid.val(e.value);
          if (c.p) {
            t.setPvsData(_instance.getPvs.call(t, e.value));//变更省份数据
          } else {
            t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
          }
          t.onAreaSelect && t.onAreaSelect(e.value!=''?e:{text:'',value:''});
        });
      }
      if (c.x) {
        t.$xo = ST.ddList(c.x, _instance.getDistr.call(t, cn), function (e) {
          t._x = e;
          xid.val(e.value);
          t.onChange && t.onChange({a: t.aid.val() || '', p: t.pid.val() || '', c: t.cid.val() || '', x: t.xid.val() || ''});
          t.onDistrSelect && t.onDistrSelect(e.value!=''?e:{text:'',value:''});
        });
      }
      if (!c.nodefault) {
        var cv, cd, pv, pd, av, ad, xv = xid.val();
        if (xid.length && xid.val() != '') {
          cd = _instance.getCityByDistr.call(t, xid.val());//根据区域获取城市
        }
        if ((cv = cid.val()) || cd) {
          pd = _instance.getPvsByCity.call(t, cd ? cd.value : cv);//根据城市获取省份
        }
        if ((pv = pid.val()) || pd) {
          ad = _instance.getAreaByPvs.call(t, pd ? pd.value : pv);//根据省份获取大区
        }
        if ((av = aid.val()) || ad) {
          ad = ad || {value: av};
        }
        if (pd) pv = pd.value;
        if (cd) cv = cd.value;
        //初始化大区选中值
        if (aid.length && t.$ao.data.length > 0) {
          if (!ad) ad = t.$ao.data[0];
          t.$ao.selByValue(ad.value);
        }
        //初始化省份选中值
        if (pid.length && (!aid.length || pv)) {
          if (!pv) pv = t.$po.data[0].value;
          t.$po.selByValue(pv);
        }
        //初始化城市选中值
        if (cid.length && (!pid.length || cv)) {
          if (!cv) cv = t.$co.data[0].value;
          t.$co.selByValue(cv);
        }
        //初始化县区选中值
        if (xv) {
          t.$xo.selByValue(xv);
        }
      } else {
        if (c.a) {
          t.$ao.setText(c.aText);
          aid.val("");
        }
        if (c.p) {
          t.$po.setText(c.pText);
          pid.val("");
        }
        if (c.c) {
          t.$co.setText(c.cText);
          cid.val("");
        }
        if (c.x) {
          t.$xo.setText(c.xText);
          xid.val("");
        }
      }
      delete t.init;
      return t;
    },
    //隐藏控件
    hide: function (t) {
      t = this;
      if (t.$ao)t.$ao.hide();
      if (t.$po)t.$po.hide();
      if (t.$co)t.$co.hide();
      if (t.$xo)t.$xo.hide();
    },
    //销毁控件
    dispose: function (t) {
      t = this;
      if (t.$ao)t.$ao.dispose();
      if (t.$po)t.$po.dispose();
      if (t.$co)t.$co.dispose();
      if (t.$xo)t.$xo.dispose();
    },
    getData:function(){
        var t=this;
        return  {a: t._a || '', p: t._p || '', c: t._c || '', x: t._x || ''};
    },
    //注册变更方法
    onChange: "",
    onAreaSelect: "",
    onPvsSelect: "",
    onCitySelect: "",
    onDistrSelect: ""
  }.init(ops)
}
