$.extend(ST.ACTION,{
	"loadSub":"loadSub"
});


$.extend(ST, {
    $Coms: ['dropdown', 'toggleAll', 'dateTimePicker', 'cityList'],
    render: function (scale, $Coms) {
        scale = document.getElementById(scale) || scale;
        if (!scale) scale = document;
        if (!$Coms) $Coms = ST.$Coms;
        for (var i = $Coms.length - 1; i >= 0; i--) {
            ST.todo($Coms[i], scale);
        }
    },
    /*
     * 弹出表单编辑框
     * @param url 地址
     * @param option 弹窗选项
     * {
     *   title 窗口标题
     *   width 窗口宽度
     *   height 窗口高度（不填则自动按照实际大小显示）
     *   mask 是否显示遮罩
     *   onshow 窗口显示后执行的JS方法
     *   onclose 窗口关闭后执行的JS方法
     * }
     *   修改支持本地JS模板
     *   eidt by  gyj
     *   etime 2014/7/17
     */
    editBox: function (url, option) {
        var uid = $.getUid(),//生成随机id
            var_flag = '$detail_box_' + uid;//弹窗变量标识

        //定义获取form方法
        function getform(id) {
            return $('#' + id).find('form').eq(0)[0];
        }

        var fn = function(data){
            //初始化msgbox
            ST[var_flag] = ST.msgbox({
                title: option.title,
                content: data
            }, [
                {text: '保存', fun: function (a, e) {
                    e.cancle = true;
                    var f = getform(ST[var_flag].ctrlId);
                    f && f.onsubmit();
                }},
                {text: '取消', fun: ''}
            ], option.width || 800, option.height || '', option.mask);
            ST[var_flag].onclose = function () {
                delete ST[var_flag];
                option.onclose && option.onclose(uid);
            };
            setTimeout(function () {
                var bid = ST[var_flag].ctrlId, f = getform(bid);
                if (f) {
                    var succFun = $(f).attr('afterSubFun');
                    if (succFun) {
                        ST['afterSubFun_' + uid] = function (j) {
                            ST[var_flag].hide();
                            ST[succFun](j);
                        };
                    } else {
                        ST['afterSubFun_' + uid] = function (j) {
                            ST[var_flag].hide();
                            if (j.data && j.data.url) {
                                location.href = j.data.url;
                            } else {
                                ST.reload();
                            }
                        };
                    }
                    $(f).attr('afterSubFun', 'afterSubFun_' + uid);
                    ST.Verify.addVform(f);
                    ST.render(bid);
                }
                option.onshow && option.onshow(uid);
            }, 50);
        };
        if(option.data){
            fn(option.data);
        }else{

            ST.tipMsg({'loading': '载入中...请稍后'});
            //加载php模板内容
            ST.getTpl( url+(/\?/.test(url)?'&':'?') + 'st_jsid=' + uid, function (data) {
                ST.hideMsg();
                var errorMsg = '获取内容失败！';
                if (!data) {
                    ST.tipMsg({'error': errorMsg});
                    return;
                }
                if (data.status) {
                    var status = (data.status + '').toLowerCase();
                    if (status == 'error') {
                        return;
                    }
                    if (status == 'tip_error') {
                        ST.tipMsg({'error': data.info || data.message || errorMsg});
                        return;
                    }
                    if(status == 'tip_alert'){
                        ST.alert(ST.JTE.fetch("common_exec_temp").getFilled({msg:data.info || data.message}),"操作提示","",400,250);
                        return;
                    }
                }
                fn(data)
            });
        }
    },
    /*
     * 弹出信息详情框
     * @param url 地址
     * @param option 弹窗选项
     * {
     *   title 窗口标题
     *   width 窗口宽度
     *   height 窗口高度（不填则自动按照实际大小显示）
     *   mask 是否显示遮罩
     *   onshow 窗口显示后执行的JS方法
     *   onclose 窗口关闭后执行的JS方法
     * }
     */
    detailBox: function (url, option) {
        var uid = $.getUid(),//生成随机id
            var_flag = '$detail_box_' + uid;//弹窗变量标识
        ST.tipMsg({'loading': '载入中...请稍后'});
        //加载php模板内容
        ST.getTpl(url + '&st_jsid=' + uid, function (data) {
            ST.hideMsg();
            var errorMsg = '获取内容失败！';
            if (!data) {
                ST.tipMsg({'error': errorMsg});
                return;
            }
            if (data.status) {
                var status = (data.status + '').toLowerCase();
                if (status == 'error') {
                    return;
                }
                if (status == 'tip_error') {
                    ST.tipMsg({'error': data.info || data.message || errorMsg});
                    return;
                }
            }
            //初始化msgbox
            ST[var_flag] = ST.msgbox({
                title: option.title,
                content: data
            }, [
                {text: '关闭', fun: ''}
            ], option.width || 800, option.height || '', option.mask);
            ST[var_flag].onclose = function () {
                delete ST[var_flag];
                option.onclose && option.onclose(uid);
            };
            setTimeout(function () {
                var bid = ST[var_flag].ctrlId;
                ST.render(bid);
                option.onshow && option.onshow(uid);
            }, 50);
        });
    },
    //获取PHP模板
    getTpl: function (url, callback) {
        $("body").queue(function () {
            $.get(url,function (data) {
                $("body").dequeue();
                callback && callback(data);
            }).error(function (e, xhr, opt) {
                    $("body").dequeue();
                    e.url = url;
                    e.data = null;
                    ST.debug && ST.debugErro(e, xhr, opt);
            });
        });
    },
    //字符串转对象
    stringToObject: function (str) {
        str = (str || '').toString();
        var s = $.trim(str), obj = {};
        if (s) {
            if (s.substring(0, 1) != "{") {
                s = "{" + s + "}";
            }
            obj = (new Function("return " + s))();
        }
        return obj;
    },
    parseConfig: function (configStr, _default) {
        return $.extend(_default || {}, ST.stringToObject(configStr));
    },
    /*
     全选：封装自ST.ToggleAll
     * */
    $ToggleAll: {},
    toggleAll: function (scale) {
        var $triggers = $('.st-toggleall', $(scale));
        if ($triggers.length == 0) return;
        $triggers.each(function () {
            //判断是否已初始化
            var $this = $(this), id = $this.attr('id');
            if (id && ST.$ToggleAll[id]) {
                return;
            }
            //初始化插件
            try {
                var uid = id || $.getUid();
                var config = ST.parseConfig($this.attr('data-config'));
                !id && $(this).attr('id', uid);
                //加载js文件
                ST.getJs('ST.ToggleAll', function () {
                    ST.$ToggleAll[uid] = new ST.ToggleAll(uid, config);
                });
            } catch (e) {

            }
        });
    },
    /*
     * 虚拟select框：封装自ST.ddList
     * @用法 在select上添加st-dropdown样式，在data-config中配置：
     * {
     *    selected:null,  //选中项
     *    subId:'',     //子选项标识
     *    subConfig:{}, //读取子选项的配置信息（详见ST.loadSubOption）
     *    inBox:false,  //是否在弹窗中，自动为下拉菜单设置适当的z-index属性，若zIndex不为0则以zIndex配置为准
     *    zIndex:0,     //层级，若不为0则为下拉菜单设置z-index属性
     *    onChange:null //模拟select的onChange事件
     * }
     */
    $Dropdown: {},
    dropdown: function (scale) {
        var $select = $('select.st-dropdown', $(scale));
        if ($select.length == 0) return;
        $select.each(function () {
            //判断是否已初始化
            var $this = $(this), id = $this.attr('id');
            if (id && ST.$Dropdown[id]) {
                return;
            }
            //初始化插件
            try {
                $this.hide();
                var uid = id || $.getUid();
                var config = ST.parseConfig($this.attr('data-config'), {
                    selected:null,
                    inBox: false,
                    zIndex: 0,
                    onChange: null
                });
                !id && $this.attr('id', uid);
                //获得数据
                var $options = $this.find('option'), data = [];
                $options.each(function () {
                    data.push({text: $(this).html(), value: $(this).val()});
                });
                //构建UI
                var uiStr =
                    '<div class="dropdown" id="' + uid + '_dropdown">' +
                        '<a class="dropdown-toggle" href="javascript:;">' +
                        '<span class="dropdown-label" id="' + uid + '_dropdown_lab"></span>' +
                        '<span class="dropdown-arrow"><i class="icon i-chevron-down"></i></span>' +
                        '</a>' +
                        '</div>';
                $this.before($(uiStr));
                //初始化ddlist
                ST.$Dropdown[uid] = ST.ddList(uid + '_dropdown', data);
                $this.data('dropdown',ST.$Dropdown[uid]);
                var $t = ST.$Dropdown[uid],selected;
                if(config.selected){
                    selected = config.selected;
                    $this.val(selected);
                }else{
                    selected = $this.val();
                }



                if (config.subId) {
                    $('#'+config.subId).on('imReady.dropdown',function(){
                        var d = data.getJson('value',selected);
                        ST.loadSubOption(d, uid, config.subId, config.subConfig);
                    });
                }
                //设置选中回调方法
                $t.onselected = function (o) {
                    $this.val(o.value);
                    if (ST.Verify && $this.attr('opt')) {
                        ST.Verify.check($this.parents('form:first')[0], $this[0]);
                    }
                    if (config.subId) {
                        ST.loadSubOption(o, uid, config.subId, config.subConfig);
                    }
                    config.onChange && config.onChange.call(this, o);
                };
                //设置选中项
                $t.selByValue(selected);
                //设置层级
                if (config.inBox || config.zIndex) {
                    $t.setZIdx(config.zIndex || $.Widget.Msgbox.$count + 1);
                }
                //设置禁用方法
                $this.one('disabled.dropdown',function(){
                    var $t = $(this).data('dropdown');
                    $t.dispose();
                    $t.Jid.addClass('disabled');
                });
                //缓存配置信息
                $this.data('config.dropdown',config);
                //触发就绪事件
                $this.trigger('imReady.dropdown');
            } catch (e) {
                //$this.show();
            }
        });
    },
    /*
     获取子选项
     @描述 用与扩展父dropdown的onChange回调方法

     @用法：
     (1)（可选）定义父dropdown的onChange回调方法，
     注：也可直接使用ST.loadSubOption作为回调方法
     如：ST.MY_METHOD = function(o){
     ST.loadSubOption(o,父选项id,子选项id,个性化配置项);
     }
     (2)（必须）配置父dropdown的onChange回调方法，
     如 <select class="st-dropdown" data-config="onChange:MY_METHOD" id="option">

     @param o 当前选中项(格式：{text:'',value:''})
     @param pid 父select id 默认值：'option'
     @param cid 子select id 默认值：'suboption'
     @param config 个性化配置项
     {
     nodefault:false,        //无默认项
     url:ST.ACTION.loadSub,  //获取子选项的地址
     key:'id',               //父选项值发送参数key
     params:{}               //额外参数
     }
     */
    loadSubOption: function (o, pid, cid, config) {
        if (!pid) pid = 'option';
        if (!cid) cid = 'suboption';
        var $p = ST.$Dropdown[pid];
        var $c = ST.$Dropdown[cid];
        if (!$p || !$c) return;
        if (!o) {
            o = $p.data[0];
        }
        //获取配置信息
        config = $.extend({
            nodefault: false,        //无默认项
            url: ST.ACTION.loadSub,  //获取子选项的地址
            key: 'id',               //父选项值发送参数key
            params: {}               //额外参数
        }, config || {});


        //定义载入数据的方法
        function load(data) {
            var $cid = $('#' + cid), val = $cid.data('config.dropdown').selected || $cid.val() || data[0].value, htmlStr = '';
            for (var i = 0, l = data.length; i < l; i++) {
                htmlStr += '<option value="' + data[i].value + '">' + data[i].text + '</option>';
            }
            $cid.html(htmlStr);
            $c.changeData(data);
            $c.setText('');
            $c.selByValue(val);
        }


        var _key = 'loadSub_' + cid;

        if (!ST.CACHE[_key] && !config.nodefault) {
            var dftOption = {text: '所有', value: '0'};
            var $option = $('#' + cid).find('option:first');
            if ($option.length > 0) {
                dftOption = {text: $option.html(), value: $option.val()};
            }
            ST.CACHE[_key] = dftOption;
        }


        //读取、缓存并载入数据
        var cachekey = _key + 'v' + o.value, tData = ST.CACHE[cachekey];

        if (tData) {
            load(tData);
        } else {
            var params = {}, loadTips = '载入中...';
            params[config.key] = o.value;
            params = $.extend(config.params, params);
            $c.changeData([
                {text: loadTips, value: ''}
            ]);
            $c.setText(loadTips);
            ST.getJSON(config.url, params, function (j) {
                if (!j) return;
                var data = (j.data || []).slice(0);
                if (!config.nodefault) {
                    data.insert(ST.CACHE[_key], 0);
                }
                load(data);
                ST.CACHE[cachekey] = data;
            }, '', 'GET');
        }
    },
    /*
     * 时间选择：封装自ST.Calendar
     * @用法 在select上添加st-datetimepicker样式，在data-config中配置：
     * {
     *    option:{},       //插件详细配置
     *    endTimeId:null,  //结束时间（配置此项则为范围选择）
     *    inBox:false,  //是否在弹窗中，自动为下拉菜单设置适当的z-index属性
     * }
     */
    $DateTimePicker: {},
    dateTimePicker: function (scale) {
        var $triggers = $('.st-datetimepicker', $(scale));
        if ($triggers.length == 0) return;
        $triggers.each(function () {
            //判断是否已初始化
            var $this = $(this), id = $this.attr('id');
            if (id && ST.$DateTimePicker[id]) {
                return;
            }
            //初始化插件
            try {
                var uid = id || $.getUid();
                var config = ST.parseConfig($this.attr('data-config'), {
                    options: {},
                    endTimeId: null,
                    inBox: false
                });
                var require = ['ST.Calender'];
                var is_range = config.endTimeId && $('#' + config.endTimeId).length;
                !id && $(this).attr('id', uid);
                config.option = config.option || {};
                //文件依赖
                if (config.options && config.options.timePicker) {
                    require = require.concat(['ST.Spinner', 'ST.TimePicker']);
                }
                //设置层级
                if (config.inBox) {
                    config.option.zIndex = $.Widget.Msgbox.$count + 1;
                }
                //加载js文件
                ST.getJsList(require, function () {
                    var options = config.option, $dtp;
                    if (!is_range) {
                        $dtp = new ST.Calender(uid, "", config.beginYear, config.endYear);
                        for (var c in options) {
                            if ($dtp[c] != undefined) {
                                $dtp[c] = options[c];
                            }
                        }
                    } else {
                        $dtp = new ST.addDateRange($.extend(config.options, {
                            bid: uid,
                            eid: config.endTimeId
                        }));
                        for (var c in options) {
                            if ($dtp['b'][c] != undefined) {
                                $dtp['b'][c] = options[c];
                                $dtp['e'][c] = options[c];
                            }
                        }
                    }
                    ST.$DateTimePicker[uid] = $dtp;
                }, function () {
                    alert(ST.LRes.RequireFail);
                });
            } catch (e) {

            }
        });
    },
    /*
     * 省市区县四级联动（单选）：封装自ST.CityList
     * @用法 在select上添加st-citylist样式，在data-config中配置：
     * {
     *    option:{},               //插件详细配置
     *    onChg:'changeChannel'     //当变更后的回调
     *    useStaticData:false,      //是否使用静态数据（若配置此项为true，则取js目录下对应url配置的文件，否则使用ajax获取的方式）
     *    url:'ST.CityData_Sample' //获取省市数据的地址
     * }
     */
    $CityList: {},
    cityList: function (scale) {
        var $el = $('.st-citylist', $(scale));
        if ($el.length == 0) return;
        $el.each(function () {
            //判断是否已初始化
            var $this = $(this), id = $this.attr('id');
            if (id && ST.$CityList[id]) {
                return;
            }
            //定义获取ui的方法
            function getUIStr(dropdownId,name,text) {
                var str='<div class="dropdown mr5" id="' + dropdownId + '">' +
                    '<a class="dropdown-toggle" href="javascript:;">' +
                    '<span class="dropdown-label" id="' + dropdownId + '_lab"></span>' +
                    '<span class="dropdown-arrow"><i class="icon i-chevron-down"></i></span>' +
                    '</a>';
                if(name && text) {
                    str += '<input type="hidden" name="'+name+'" />';
                }
                str+=  '</div>';
                return str;
            }

            var uid = id || $.getUid();
            var config = ST.parseConfig($this.attr('data-config'),{
                useStaticData:true,
                url:ST.ACTION.CITYDATA,
                option:{}
            });
            config.useStaticData && (config.url='ST.CityData');
            var $ui = {
                distr: $this.find('[data-role^="distr"]'),
                city: $this.find('[data-role^="city"]'),
                prov: $this.find('[data-role^="prov"]'),
                area: $this.find('[data-role^="area"]')
            };
            var defaults = {};
            if(!id){
                id = uid;
                $(this).attr('id', uid);
            }
            $.each($ui, function (type, dom) {
                if (dom.length > 0) {
                    var dropdownId = dom.attr('id') || (uid + '_' + type),
                        valId = dropdownId+'_val', name = dom.attr("name"), text = dom.attr("text"),
                        $val = dom.filter('[data-role$="-id"]');
                    dom.eq(0).parent().prepend(getUIStr(dropdownId,name,text));
                    if($val.length>0){
                        $val.attr({'id':valId,'name':name+"_id"});
                    }else{
                        $this.append('<input data-role="'+type+'-id" type="hidden" id="'+valId+' name="'+name+'_id"/>');
                        $ui[type] = $this.find('[data-role^="'+type+'"]');
                    }
                    defaults[type.substring(0,1).replace('d','x')] = dropdownId;
                    //处理默认值，若id或name有值则：nodefault = false
//                    if(config.option.nodefault){
//                        dom.each(function(){
//                            if($(this).val()){
//                                config.option.nodefault = false;
//                                return false;
//                            }
//                        });
//                    }
                }
            });
            //缓存原始值
            var val_a = $ui.area.filter('[data-role$="-id"]').val();
            var val_p = $ui.prov.filter('[data-role$="-id"]').val();
            var val_c = $ui.city.filter('[data-role$="-id"]').val();
            var val_x = $ui.distr.filter('[data-role$="-id"]').val();
            //初始化插件
            try {
                var require = ['ST.CityList'];
                //定义初始化方法
                function init(){
                    //加载js文件
                    ST.getJsList(require, function () {
                        var options = $.extend({data:CityData}, defaults, config.option || {});
                        var $cl = new ST.CityList(options);
                        //处理大区文字域
                        if(options.a){
                            var $text_a = $ui.area.filter('[data-role$="-name"]');
                            if($text_a.length>0){
                                if(!options.nodefault){
                                    $cl.$ao.selByText($text_a.val());
                                }else{
                                    $text_a.val('');
                                }
                            }else if(val_a){
                                $cl.$ao[ST.Regs.number.reg.test(val_a)?"selByValue":"selByText"](val_a);
                            }
                            $cl.onAreaSelect = function(o){
                                $text_a.val(o.text);
                                $ui.area.trigger('blur');
                                ST[options.onAreaSelect || config.onChg] && ST[options.onAreaSelect || config.onChg](id,o);
                                //修改为插件配置方法名
                                //$.isFunction(options.onAreaSelect) && options.onAreaSelect.call(this,arguments);
                            }
                        }
                        //处理省份文字域
                        if(options.p){
                            var $text_p = $ui.prov.filter('[data-role$="-name"]');
                            if($text_p.length>0){
                                if(!options.nodefault){
                                    $cl.$po.selByText($text_p.val());
                                }else{
                                    $text_p.val('');
                                }
                            }else if(val_p){
                                $cl.$po[ST.Regs.number.reg.test(val_p)?"selByValue":"selByText"](val_p);
                            }
                            $cl.onPvsSelect = function(o){
                                $text_p.val(o.text);
                                $ui.prov.trigger('blur');
                                ST[options.onPvsSelect || config.onChg] && ST[options.onPvsSelect  || config.onChg](id,o);
                                // 修改为插件配置方法名
                                //$.isFunction(options.onPvsSelect) && options.onPvsSelect.call(this,arguments);
                            };
                        }
                        //处理城市文字域
                        if(options.c){
                            var $text_c = $ui.city.filter('[data-role$="-name"]');
                            if($text_c.length>0){
                                if(!options.nodefault){
                                    $cl.$co.selByText($text_c.val());
                                }else{
                                    $text_c.val('');
                                }
                            }else if(val_c){
                                $cl.$co[ST.Regs.number.reg.test(val_c)?"selByValue":"selByText"](val_c);
                            }
                            $cl.onCitySelect = function(o){
                                $text_c.val(o.text);
                                $ui.city.trigger('blur');
                                ST[options.onCitySelect || config.onChg] && ST[options.onCitySelect  || config.onChg](id,o);
                                // 修改为插件配置方法名
                                //$.isFunction(options.onCitySelect) && options.onCitySelect.call(this,arguments);
                            };
                        }
                        //处理区县文字域
                        if(options.x){
                            var $text_d = $ui.distr.filter('[data-role$="-name"]');
                            if($text_d.length>0){
                                if(!options.nodefault){
                                    $cl.$xo.selByText($text_d.val());
                                }else{
                                    $text_d.val('');
                                }
                            }else if(val_x){
                                $cl.$xo[ST.Regs.number.reg.test(val_x)?"selByValue":"selByText"](val_x);
                            }
                            $cl.onDistrSelect = function(o){
                                $text_d.val(o.text);
                                $ui.distr.trigger('blur');
                                ST[options.onDistrSelect || config.onChg] && ST[options.onDistrSelect || config.onChg](id,o);
                                // 修改为插件配置方法名
                                //$.isFunction(options.onDistrSelect) && options.onDistrSelect.call(this,arguments);
                            };
                        }
                        ST.$CityList[uid] = $cl;
                        ST[config.onChg] && ST[config.onChg](id);
                    });
                }
                if(config.useStaticData){
                    require.push(config.url);
                    init();
                }else{
                    if(window.CityData){
                        init();
                    }else{
                        ST.getJSON(config.url,'',function(j){
                            if(!j||!j.data) return;
                            window.CityData = j.data;
                            init();
                        },'','GET');
                    }
                }


            } catch (e) {

            }
        });
    }

});
ST.TODOLIST.push({method: "render", pars: undefined});