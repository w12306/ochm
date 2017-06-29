$.extend(ST.ACTION,{
    /*获取渠道商数据的皆苦
     * pars  area 区域
     *       prov 省份
     *       city 城市
     */
   getChannelData:"/libra/api/area/channel.do",
    /*
    * 获取帐号类别数量
    *
    *
    * */
    getChanelNumByType:"/libra/api/channel/daytype.do"
});

$.extend(ST, {
    //引入对应的模板文件
  jsTemplates:['common','libra'],
  /**
   * swfobject上传按钮外观配置
   */
  upbtnConfig: {
    btnimg: ST.PATH.IMAGE + "upbtn_66x30.png",
    btnwidth: "66",
    btnheight: "30",
    btntext: " "
  },
  /**
   * 时间日期范围配置
   */
  CalRange: {
    'beginYear': 2012,  //配置默认开始年份
    'endYear': 2020     //配置默认结束年份
  },
  /**
   * 重写ST库文件的ddlist（具体配置及用法见在线api）
   *
   * @desc 自动选取容器下第一个select作为数据源（2013-8-26新增）
   *
   * @param id    目标容器id
   * @param data  数据（格式:[{text:'',value:''}……]）
   * @param selFn 选中后回调方法
   * @param h     每一项的高度（通常无需配置此项，使用默认值即可）
   *
   * @returns     $.Widget.DropDownList对象
   */
  ddList: function (id, data, selFn, h) {
    var $this = $('#' + id), $dts = $this.find('select:first'), ddl;
    if (!data && $dts.length > 0) {
      var options = $dts.find('option');
      data = [];
      options.each(function () {
        data.push({text: $(this).html(), value: $(this).val()});
      });
    }
    ddl = new $.Widget.DropDownList(id, h);
    ddl.onselected = function (o, e) {
      $.Lang.isMethod(selFn) && selFn(o, e);
    };
    if (data) ddl.changeData(data);
    return ddl;
  },
  /**
   * 两级联动
   * @param options
   */
  ddList2: function (options) {
    var options = options || {};
    var c = {
      pid: options.pid || '_ddlist_p',        //父容器id
      cid: options.cid || '_ddlist_c',        //子容器id
      pdata: options.pdata || ST.ddlData.p,  //父数据
      cdata: options.cdata || ST.ddlData.c, //子数据
      pfn: options.pfn,                      //父回调
      cfn: options.cfn                       //子回调
    };
    var pIpt = $('#' + c.pid + '_val'), cIpt = $('#' + c.cid + '_val');
    //child ddlist
    var ddlist_c = ST.ddList(c.cid, [], function (o) {
      c.cfn && c.cfn(o);
    });
    //parent ddlist
    var ddlist_p = ST.ddList(c.pid, c.pdata,function (o) {
      if (!ST.page_inited) {
        ST.page_inited = true;
      } else {
        cIpt.val('');
      }
      ddlist_c.changeData(c.cdata[o.value] || []);
      if (c.cdata[o.value] && c.cdata[o.value][0]) {
        ddlist_c.selByValue(cIpt.val() || c.cdata[o.value][0].value);
      }
      c.pfn && c.pfn(o);
    }).selByValue(pIpt.val() || c.pdata[0].value);
  },
  /**
   * 执行Ajax操作
   * @param options
   */
  execAJAX: function (options) {
    var config = $.extend({
      url: '',           //服务端请求地址
      method: 'POST',    //提交方法（GET|POST）
      params: {},        //发送到服务端的参数（如：{myparam:1}）
      massage: '',       //提示信息（仅hasconfirm为true时有用）
      hasconfirm: true,  //提交前是否显示确认框
      succFun: '',       //成功后的回调方法
      erroFun: ''        //失败后的回调方法
    }, options);
    var sendRequest = function () {
      ST.tipMsg("正在执行操作,请稍后", 0, 0);
      ST.getJSON(config.url, config.params || '', function (j) {
        config.succFun && config.succFun(j);
      }, function (j) {
        config.erroFun && config.erroFun(j);
      }, config.method);
    };
    if (config.hasconfirm) {
      ST.hideMsg();
      ST.confirm(ST.JTE.fetch('common_exec_temp').getFilled({msg: config.massage || ''}), "操作提示", function () {
        sendRequest();
      }, function () {
        //if canceled
      }, 400);
    } else {
      sendRequest();
    }
  },
  /**
   * 关闭弹窗
   */
  closeFb: function () {
    if (!parent || !parent.ST.$Fb) return;
    parent.ST.$Fb.hide();
  },
  /**
   * 在弹窗页面中刷新父窗口
   */
  reloadFb: function () {
    if (!parent)return;
    parent.ST.reload();
  },
  /**
   * 添加日期范围
   * @param options
   * @returns {b: ST.Calender对象, e: ST.Calender对象}
   */
  addDateRange: function (options) {
    var config = $.extend({
      bid: 'beginTime',                 //开始时间id
      eid: 'endTime',                   //结束时间id
      beginYear: ST.CalRange.beginYear, //最小年份
      endYear: ST.CalRange.endYear,     //最大年份
      isequal: false,                   //是否允许开始与结束时间相同
      timePicker: false,                //是否需要时间选择（需引入ST.Timepicker.js）
      showSeconds: false,                //是否显示秒
      onselected:null                    //选中后回调方法
    }, options);
    var $Input = {
      'b': $('#' + config.bid),
      'e': $('#' + config.eid)
    };
    var $Calender = {
      'b': new ST.Calender(config.bid, "", config.beginYear, config.endYear),
      'e': new ST.Calender(config.eid, "", config.beginYear, config.endYear)
    };
    //处理timePicker
    if (config.timePicker) {
      $Calender['b'].timePicker = $Calender['e'].timePicker = true;
      if (config.showSeconds) {
        $Calender['b'].showSeconds = $Calender['e'].showSeconds = true;
      }
    }
    //处理回调
    var evHandler = function (tp, c) {
      var cdt = config.timePicker ? c.date.split(' ')[0] : c.date, arr = cdt.split('-');
      if (!config.isequal) {
        arr = ST[tp == 'e' ? 'getNextDate' : 'getPrevDate'](arr).slice(0);
        cdt = arr.join('-');
      }
      $Calender[tp][tp == 'e' ? 'minDate' : 'maxDate'] = cdt;
      if ((tp == 'e' && $Calender['e'].curDate < cdt) || (tp == 'b' && $Calender['b'].curDate > cdt)) {
        $Calender[tp].curDate = cdt;
        $Calender[tp].curYear = arr[0];
        $Calender[tp].curMonth = arr[1];
        $Calender[tp].curDay = arr[2];
      }
    };
    $Calender['b'].onselected = function (c) {
      evHandler('e', c);
      $.isFunction(config.onselected) &&
        config.onselected.call(this,[c.date,$Input['e'].val()],'b');
    };
    $Calender['e'].onselected = function (c) {
      evHandler('b', c);
        $.isFunction(config.onselected) &&
            config.onselected.call(this,[$Input['b'].val(), c.date],'e');
    };
    var curDate = {
      'b': $Input['b'].val(),
      'e': $Input['e'].val()
    };
    if (curDate['b']) evHandler('e', {date: curDate['b']});
    if (curDate['e']) evHandler('b', {date: curDate['e']});
    return $Calender;
  },
  getNextDate: function (dt) {
    var _dt = new Date(parseInt(dt[0], 10), parseInt(dt[1], 10) - 1, parseInt(dt[2], 10));
    var time = _dt.getTime() + 86400000;
    _dt.setTime(time);
    return this.getDateArray(_dt);
  },
  getPrevDate: function (dt) {
    var _dt = new Date(parseInt(dt[0], 10), parseInt(dt[1], 10) - 1, parseInt(dt[2], 10));
    var time = _dt.getTime() - 86400000;
    _dt.setTime(time);
    return this.getDateArray(_dt);
  },
  getDateArray: function (dt) {
    var y = dt.getFullYear(), m = dt.getMonth() + 1, d = dt.getDate();
    if (m < 10) m = '0' + m;
    if (d < 10) d = '0' + d;
    return [y + '', m + '', d + ''];
  },
  getToday: function () {
    var dt = new Date(), ty = dt.getFullYear(), tm = dt.getMonth() + 1, td = dt.getDate();
    if (tm < 10) tm = '0' + tm;
    if (td < 10) td = '0' + td;
    return [ty + '', tm + '', td + ''];
  },
  /**
   * 百度编辑器填充方法
   * @desc 在form表单中配置 beforeSubFun="fillEditor"
   */
  fillEditor: function () {
    for (var i = 0, dl; i < ST.EDITORS.length, dl = ST.EDITORS[i]; i++) {
      if (dl["editor"]) {
        $("#" + dl["id"]).val(dl["editor"].getContent());
      }
    }
    return true;
  },
  /**
   * 填充input:file
   * @desc 在input:file中配置  onchange="ST.todo('fileFilling',this);"
   */
  fileFilling: function (file) {
    if (!file) return;
    var $file = $(file), p = $($file).parent(), dsp_input = p.find('input:text:first');
    dsp_input.val($file.val());
    $file.trigger('blur');
  },
  /**
   * 逗号分隔ID（纯数字）
   * @desc  1.规则：（1）任何非数字字符转为半角逗号（2）多个连续的半角逗号转为一个半角逗号；2.配置：在input|textarea中配置  onkeyup="ST.todo('splitBycomma',this)" onpaste="ST.todo('splitBycomma',this,true)"
   */
  splitBycomma:function(node,delay){
    if(!node) return;
    var $this =$(node);
    var fn=function(){
      $this.val($this.val().replace(/[^0-9]/g,',').replace(/,{2,}/,','));
    };
    if(delay){
      setTimeout(fn,50);
    }else{
      fn();
    }
  },
    toggleDom:function(id,bool){
      var a = $("#"+id);
      a.toggle(bool);
    },
    /*
    * 通用获取数据方法
    * */
    //渠道商数据缓存
    ChannelData:{},
    /*
    * 获取渠道商数据
    * ops   obj
     *       p 省份
     *       c 城市
     */
    getChannelData:function(ops,cb){
        var t=this,key,data;
        key = (ops.p||"") + "_" + (ops.c||"");
        if(data= t.ChannelData[key]) {
            cb && cb(data);
        }else{
           ST.getJSON(ST.ACTION.getChannelData+"?rd="+new Date().getTime(),{province:ops.p,city:ops.c},function(j){
               if(j && j.data && !j.data.length)  j.data=[{text:"请选择渠道代理商",value:"0"}];
               t.ChannelData[key]= j.data;
               data=t.ChannelData[key];
               cb && cb(data);
           },function(){
                //done

           });
        }
    },
    /*
     *更换代理商数据及下拉选择框
    * */
    changeChannel:function(id,o){
        var t=this,pars;
        //获取省市数据
        pars  = ST.$CityList[id].getData();
        if(!pars.p && !pars.c) return;
        ST.getChannelData({p:(pars.p?pars.p.text:""),c:(pars.c?pars.c.text:"")},function(data){
            //更换数据
            ST.$Dropdown["js-channel"].changeData(data);
            var selected = $("#js-channel_val").val();
            //选中第一个
            if(selected && data.getJson("value",selected)){
                ST.$Dropdown["js-channel"].selByValue(selected);
            }else if(data.length){
                ST.$Dropdown["js-channel"].selByValue(data[0].value);
            }
        });
    },
    /*
     * 获取渠道商数据
     * ops   obj
     *       p 省份
     *       c 城市
     */
    getChannelData_edit:function(ops,cb){
        var t=this,key,data,cid=$('#cid').val();
        key = (ops.p||"") + "_" + (ops.c||"");
        if(data= t.ChannelData[key]) {
            cb && cb(data);
        }else{
            ST.getJSON(ST.ACTION.getChannelData+"?rd="+new Date().getTime(),{province:ops.p,city:ops.c,channelId:cid},function(j){
                if(j && j.data && !j.data.length)  j.data=[{text:"请选择渠道代理商",value:"0"}];
                t.ChannelData[key]= j.data;
                data=t.ChannelData[key];
                cb && cb(data);
            },function(){
                //done

            });
        }
    },
    /*
     *更换代理商数据及下拉选择框
     * */
    changeChannel_edit:function(id,o){
        var t=this,pars;
        //获取省市数据
        pars  = ST.$CityList[id].getData();
        if(!pars.p && !pars.c) return;
        ST.getChannelData_edit({p:(pars.p?pars.p.text:""),c:(pars.c?pars.c.text:"")},function(data){
            
            //更换数据
            ST.$Dropdown["js-channel"].changeData(data);
            var selected = $("#js-channel_val").val();

            //选中第一个
            if(selected && data.getJson("value",selected)){
                ST.$Dropdown["js-channel"].selByValue(selected);
            }else if(data.length){
                ST.$Dropdown["js-channel"].selByValue(data[0].value);
            }
        });
    },
     /**
     *
     * 页面扩展方法
      */
    addPoint:function(id){
         ST.editBox(ST.ACTION.addPoint + id, {
             title: '充点',
             width:600,
             height: 320
         });
    },
    //充帐户
    addAccount:function(id){
        ST.editBox(ST.ACTION.addAccount + id, {
            title: '充帐号',
            width:600,
            height: 320
        });
    },
    //检查帐户点数     //此处this为downList this
    checkAccountPoint:function(o){
      var d =this.Jid.closest("form");
      var data = ST.serForm(d,true);
      //$.log(this);
      ST.getJSON(ST.ACTION.getChanelNumByType,{cId:data.cId,dayTypeId:data.dayTypeId,rd:new Date().getTime()},function(j){
          d.find(".js-account-tnum").html("剩余"+j.data+"个");
          d.find(".js-account-num").attr("range","1-"+ j.data);
      },function(){
          d.find(".js-account-tnum").html("渠道商库存不足");
          d.find(".js-account-num").attr("range","1-99999");
      });
    },
    manageNetbar:function(id){
        ST.editBox(ST.ACTION.manageNetbar + id, {
            title: '管理',
            width:600,
            height:320
        });
    },
    //转点
    transferPoint:function(id){
        ST.editBox(ST.ACTION.transferPoint + id, {
            title: '转点',
            width:600,
            height: 320
        });
    },
    //转帐号
    transferAccount:function(id){
        ST.editBox(ST.ACTION.transferAccount + id, {
            title: '转帐号',
            width:600,
            height: 320
        });
    },
    //转正   批量转正
    regularize:function(id){
        var arr=[];
        if(id==undefined || id==null){
            var $chk = $('.js-toggleall-target:checked');
            $chk.each(function(){
                arr.push($(this).val());
            });
        }else{
            arr = [id];
        }
        if(arr.length>0){
            ST.execAJAX({
                url:  ST.ACTION.regularize + arr.join(','),       //服务端请求地址
                massage: '确定转正吗？',   //提示信息（仅hasconfirm为true时有用）
                hasconfirm: true,        //提交前是否显示确认框
                method:"get",
                succFun: function (j) {  //成功后的回调方法：刷新页面
                    if (!j) return;
                    if (j.data && j.data.url) {
                        location.href = j.data.url;
                    } else {
                        ST.reload();
                    }
                }
            });
        }
        else{
            ST.tipMsg('请先勾选要删除的设备ID！');
        }
    },
    //禁用
    disable:function(id){
        ST.editBox(ST.ACTION.disable + id, {
            title: '禁用',
            width:600,
            height:400
        });
    },
    //停止扣费
    pause:function(id){
        ST.execAJAX({
            url: ST.ACTION.pause + id,       //服务端请求地址
            massage: '确定停止扣费吗？',   //提示信息（仅hasconfirm为true时有用）
            hasconfirm: true,        //提交前是否显示确认框
            method:"get",
            succFun: function (j) {  //成功后的回调方法：刷新页面
                if (!j) return;
                if (j.data && j.data.url) {
                    location.href = j.data.url;
                } else {
                    ST.reload();
                }
            }
        });
    },
    //解除停止扣费
    enablePause:function(id){
        ST.execAJAX({
            url: ST.ACTION.enablePause + id,       //服务端请求地址
            massage: '确定解除停止扣费吗？',   //提示信息（仅hasconfirm为true时有用）
            hasconfirm: true,        //提交前是否显示确认框
            method:"get",
            succFun: function (j) {  //成功后的回调方法：刷新页面
                if (!j) return;
                if (j.data && j.data.url) {
                    location.href = j.data.url;
                } else {
                    ST.reload();
                }
            }
        });
    },
    //启用
    enable:function(id){
        ST.editBox(ST.ACTION.enable + id, {
            title: '启用',
            width:600,
            height:400
        });
    },
    del: function (id) {
        //   alert('aaaa');
        var arr=[];
        if(id==undefined || id==null){
            var $chk = $('.js-toggleall-target:checked');
            $chk.each(function(){
                arr.push($(this).val());
            });
        }else{
            arr = [id];
        }

        if(arr.length>0){
            ST.execAJAX({
                url: ST.ACTION.del + arr.join(','),       //服务端请求地址
                massage: ST.PHPDATA.delMsg || '确定删除吗？',   //提示信息（仅hasconfirm为true时有用）
                hasconfirm: true,        //提交前是否显示确认框
                method:"get",
                succFun: function (j) {  //成功后的回调方法：刷新页面
                    if (!j) return;
                    if (j.data && j.data.url) {
                        location.href = j.data.url;
                    } else {
                        ST.reload();
                    }
                }
            });
        }
        else{
            ST.tipMsg('请先勾选要删除的设备ID！');
        }
        //更多选项参见common.js execAJAX方法
    },
    modPass:function(){
        var t=this;
        if(!t.$passMb){
            t.$passMb = ST.msgbox({
                title:"帐户管理",
                content:ST.JTE.fetch("common_mod_password").getFilled({data:ST.PHPDATA.userInfo})
            },[{text:"提交",fun:function(c,e){
                e.cancle=true; //取消默认销毁
                $("#js-mod-password-form")[0].onsubmit();
            }},{text:"取消",fun:function(){}}],600,400,true);
            ST.Verify.addVform("js-mod-password-form");
            t.$passMb.onclose=function(e){
                e.cancle=true; //取消默认销毁
                t.$passMb.display();
            }
        }else{
            $("#js-mod-password-form")[0].reset();//重置表单
            $("#js-mod-password-form").find(".inline").remove();
            t.$passMb.display();
        }
    }
});