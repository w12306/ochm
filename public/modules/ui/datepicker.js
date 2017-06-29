/*
 * Module Name: datepicker
 * Module Require: jquery, underscore, ui/popup
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/datepicker/js/datepicker");
});

define("src/plugin/datepicker/js/datepicker.defaults", [ "../tpl/datepicker.container.html", "../tpl/datepicker.date.html", "../tpl/datepicker.month.html", "../tpl/datepicker.year.html" ], function(require, exports, module) {
    var container = require("../tpl/datepicker.container.html");
    var date = require("../tpl/datepicker.date.html");
    var month = require("../tpl/datepicker.month.html");
    var year = require("../tpl/datepicker.year.html");
    return function(_) {
        return {
            //语言包
            lang: {
                today: "今天",
                clear: "清空",
                close: "关闭",
                week: [ "日", "一", "二", "三", "四", "五", "六" ],
                separator: "至",
                emptydate: "未选择"
            },
            //指定容器
            container: null,
            //模板
            tpl: _.template(container),
            tpl_date: _.template(date),
            tpl_month: _.template(month),
            tpl_year: _.template(year),
            tpl_icon: '<a href="javascript:;"><i class="icon icon-calendar"></i></a>',
            //日期格式
            yearFormat: "YYYY",
            monthFormat: "YYYY-MM",
            dateFormat: "YYYY-MM-DD",
            timeFormat: function() {
                return this.option("showSecond") ? "HH:mm:ss" : "HH:mm";
            },
            //时间日期分隔符
            separator: " ",
            //视图标题格式化
            dateViewTitleFormatter: function(year, month) {
                return year + "年" + month + "月";
            },
            monthViewTitleFormatter: function(year) {
                return year + "年";
            },
            yearViewTitleFormatter: function(startYear, endYear) {
                return startYear + "年-" + endYear + "年";
            },
            //今天
            today: new Date(),
            //选中日期
            date: null,
            //最小日期
            minDate: new Date().getFullYear() - 10 + "-01-01 00:00:00",
            //最大日期
            maxDate: new Date().getFullYear() + 10 + "-12-31 23:59:59",
            //一次显示多个日历（默认单选显示1个，多选显示2个）
            displayCount: "auto",
            //对齐方式
            align: "left bottom",
            //单个面板宽度
            panelWidth: 218,
            //选中样式
            selectedCls: function() {
                return this.option("multiple") ? "selected" : "current";
            },
            todayCls: "today",
            activeCls: "selected",
            //禁用样式
            disabledCls: "disabled",
            //鼠标滑过样式
            hoverCls: "hover",
            //其他（年/月）样式
            otherCls: "other",
            //是否显示在行内
            inline: false,
            //选择类型
            toSelect: "date",
            //是否范围选择
            rangeSelect: false,
            //最少可选日期数（仅适用于范围选择）
            minSelectCount: 2,
            //最多可选日期数（仅适用于范围选择）
            maxSelectCount: null,
            //范围选择分隔符
            rangeSeparator: ",",
            //日期范围开始样式
            rangeStartCls: "start",
            //日期范围结束样式
            rangeEndCls: "end",
            //是否使用时间选择
            timepicker: false,
            //是否显示秒
            showSecond: false,
            //显示今天按钮
            showToday: true,
            //显示清空按钮
            showClear: true,
            //是否显示图标
            showIcon: false,
            //是否使用动画效果
            animation: true,
            //选中前触发（返回false则不执行选中）
            onBeforeSelect: null,
            //当选中值变化时触发
            onChanged: null,
            //当日历弹出时触发
            onPopupOpened: null,
            //当日历关闭时触发
            onPopupClosed: null,
            //当设置日期出错时触发
            onInvalid: null
        };
    };
});

define("src/plugin/datepicker/tpl/datepicker.container.html", [], '<%var count = this.displayCount();%>\n<%var inline = this.inline();%>\n<%var hasTimepicker = this.hasTimepicker();%>\n<%var width = this.option(\'panelWidth\');%>\n<%var lang = this.option(\'lang\');%>\n<div class="datepicker" style="width: <%=count*width%>px;height:<%=hasTimepicker?\'300\':\'257\'%>px">\n    <div class="datepicker-hd">\n        <a role="prev" class="datepicker-prev" href="javascript:;"><i class="icon"></i></a>\n        <div class="datepicker-title-wrap">\n        <%for(var i=0;i< count;i++){%>\n            <%if(count!=1){%>\n            <%if(i==0){%>\n            <a role="title" data-index="<%=i%>" class="datepicker-title datepicker-title-first" href="javascript:;"></a>\n            <%}else if(i==count-1){%>\n            <a role="title" data-index="<%=i%>" class="datepicker-title datepicker-title-last" href="javascript:;"></a>\n            <%}else{%>\n            <a role="title" data-index="<%=i%>" class="datepicker-title datepicker-title-other" href="javascript:;"></a>\n            <%}%>\n            <%}else{%>\n            <a role="title" data-index="<%=i%>" class="datepicker-title" href="javascript:;"></a>\n            <%}%>\n        <%}%>\n        </div>\n        <a role="next" class="datepicker-next" href="javascript:;"><i class="icon"></i></a>\n    </div>\n    <div class="datepicker-bd">\n        <div role="view" class="datepicker-bd-inner" style="width: <%=count*(width+1)%>px;">\n        <%for(var i=0;i< count;i++){%>\n            <div role="date-view" data-index="<%=i%>" class="datepicker-day" style="position:absolute;top:0;left:<%=i*width%>px;"></div>\n            <div role="month-view" data-index="<%=i%>" class="datepicker-month" style="position:absolute;top:0;left:<%=i*width%>px;"></div>\n            <div role="year-view" data-index="<%=i%>" class="datepicker-year" style="position:absolute;top:0;left:<%=i*width%>px;"></div>\n        <%}%>\n        </div>\n    </div>\n    <div class="datepicker-ft">\n        <div role="result" class="datepicker-result">\n            <%if(this.option(\'rangeSelect\')){%>\n            <input role="startdate-val" class="input datepicker-startdate" type="text" value="<%=data.start.date%>" readonly/>\n            <%if(hasTimepicker){%>\n            <input role="starttime" type="hidden" value="<%=data.start.time%>"/>\n            <%}%>\n            <span class="datepicker-joint">至</span>\n            <input role="enddate-val" class="input datepicker-enddate" type="text" value="<%=data.end.date%>" readonly/>\n            <%if(hasTimepicker){%>\n            <input role="endtime" type="hidden" value="<%=data.end.time%>"/>\n            <%}%>\n            <%}else{%>\n            <input role="date-val" class="input datepicker-currentdate" type="text"  value="<%=data.date%>" readonly/>\n            <%if(hasTimepicker){%>\n            <input role="time" type="hidden" value="<%=data.time%>"/>\n            <%}%>\n            <%}%>\n        </div>\n        <div role="shortcut" class="datepicker-btns">\n            <%if(this.option(\'showToday\')){%>\n            <button role="gotoday" class="btn btn-blue datepicker-btn"><%=lang.today%></button>\n            <%}%>\n            <%if(this.option(\'showClear\')){%>\n            <button role="clear" class="btn btn-white datepicker-btn"><%=lang.clear%></button>\n            <%}%>\n        </div>\n    </div>\n</div>\n\n');

define("src/plugin/datepicker/tpl/datepicker.date.html", [], '<%var lang = this.option(\'lang\');%>\n<table>\n    <thead>\n    <tr>\n        <%_.each(lang.week,function(text){%>\n        <th><%=text%></th>\n        <%});%>\n    </tr>\n    </thead>\n    <tbody>\n    <%for(var i = 0, l = data.length; i < l; i++){%>\n    <tr>\n    <%for(var j = 0, n = data[i].length; j < n; j++){%>\n        <%var d = data[i][j];%>\n        <%if(d){%>\n        <td role="<%=d.disabled?\'disabled\':\'date\'%>"\n            data-year="<%=d.year%>"\n            data-month="<%=d.month%>"\n            data-date="<%=d.date%>"\n            <%=d.isToday?\' data-today="true"\':\'\'%>\n            class="<%=d.cls%>">\n            <a href="javascript:;"><%=d.date%></a>\n        </td>\n        <%}else{%>\n        <td>&nbsp;</td>\n        <%}%>\n    <%}%>\n    </tr>\n    <%}%>\n    </tbody>\n</table>');

define("src/plugin/datepicker/tpl/datepicker.month.html", [], '<table>\n    <tbody>\n    <%for(var i = 0, l = data.length; i < l; i++){%>\n    <tr>\n    <%for(var j = 0, n = data[i].length; j< n ;j++){%>\n        <%var d = data[i][j];%>\n        <%if(d){%>\n        <td role="<%=d.disabled?\'disabled\':\'date\'%>" data-year="<%=d.year%>" data-month="<%=d.month%>" class="<%=d.cls%>">\n            <a href="javascript:;"><%=d.month+1%>月</a>\n        </td>\n        <%}else{%>\n        <td>&nbsp;</td>\n        <%}%>\n    <%}%>\n    </tr>\n    <%}%>\n    </tbody>\n</table>');

define("src/plugin/datepicker/tpl/datepicker.year.html", [], '<table>\n    <tbody>\n    <%for(var i = 0; i < data.length; i++){%>\n    <tr>\n        <%for(var j = 0, n = data[i].length; j < n; j++){%>\n        <%var d = data[i][j];%>\n        <%if(d){%>\n        <td role="<%=d.disabled?\'disabled\':\'date\'%>" data-year="<%=d.year%>" class="<%=d.cls%>">\n            <a href="javascript:;"><%=d.year%></a>\n        </td>\n        <%}else{%>\n        <td>&nbsp;</td>\n        <%}%>\n        <%}%>\n    </tr>\n    <%}%>\n    </tbody>\n</table>');

define("src/plugin/datepicker/js/datepicker", [ "underscore", "lib/moment", "ui/popup", "./timepicker", "./timepicker.defaults", "./datepicker.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    if (!moment) var moment = require("lib/moment");
    var Popup = require("ui/popup");
    var Timepicker = require("./timepicker");
    var defaults = require("./datepicker.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "datepicker";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    var TOGGLE = "input,textarea,button,a,i,img";
    var VALUEFIELD = "input,textarea,button";
    var TEXTFIELD = "input:text,textarea";
    // 全局变量
    var $document = $(document);
    // 定义插件类
    function Datepicker(element, options) {
        this.el = element;
        this.$el = $(element);
        this.defaults = Datepicker.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 定义默认选项
    Datepicker.defaults = defaults;
    // 设置默认选项
    Datepicker.setDefaults = function(options) {
        $.extend(Datepicker.defaults, options);
    };
    // 工具箱
    var util = Datepicker.util = {
        //获取时分秒清零后的moment对象
        momentDate: function(_moment) {
            return moment(_moment).hour(0).minute(0).second(0).millisecond(0);
        },
        //扩展moment().range方法范围合法性判断
        momentRange: function(a, b) {
            a = moment(a);
            b = moment(b);
            var start = moment.min(a, b);
            var end = moment.max(a, b);
            return moment().range(start, end);
        },
        //获得日期范围（不计时分秒）
        momentDateRange: function(a, b) {
            a = util.momentDate(a);
            b = util.momentDate(b);
            var start = moment.min(a, b);
            var end = moment.max(a, b);
            return moment().range(start, end);
        },
        //判断日期是否相同（不计时分秒）
        isDateSame: function(a, b) {
            var date_a = util.momentDate(a);
            var date_b = util.momentDate(b);
            return date_a.isSame(date_b);
        },
        //判断日期【a】是否早于日期【b】（不计时分秒）
        isDateBefore: function(a, b) {
            var date_a = util.momentDate(a);
            var date_b = util.momentDate(b);
            return date_a.isBefore(date_b);
        },
        //判断日期【a】是否晚于日期【b】（不计时分秒）
        isDateAfter: function(a, b) {
            var date_a = util.momentDate(a);
            var date_b = util.momentDate(b);
            return date_a.isAfter(date_b);
        },
        //判断日期范围【a】与日期范围【b】有交集
        isRangeOverlaps: function(a, b) {
            return a.start.isSame(b.end) || a.overlaps(b);
        },
        //判断时长是否相等
        isDurationEq: function(a, b) {
            var sa = moment.duration(a).as("s");
            var sb = moment.duration(b).as("s");
            return sa == sb;
        },
        //判断时长【a】是否小于【b】
        isDurationLt: function(a, b) {
            var sa = moment.duration(a).as("s");
            var sb = moment.duration(b).as("s");
            return sa < sb;
        },
        //判断时长【a】是否大于【b】
        isDurationGt: function(a, b) {
            var sa = moment.duration(a).as("s");
            var sb = moment.duration(b).as("s");
            return sa > sb;
        },
        //获得时刻object
        momentObj: function(_moment) {
            _moment = moment(_moment);
            return {
                year: _moment.year(),
                month: _moment.month(),
                date: _moment.date(),
                hour: _moment.hour(),
                minute: _moment.minute(),
                second: _moment.second()
            };
        },
        //获得日期object
        dateObj: function(_moment) {
            _moment = moment(_moment);
            return {
                year: _moment.year(),
                month: _moment.month(),
                date: _moment.date()
            };
        },
        //获得时间object
        timeObj: function(_moment) {
            _moment = moment(_moment);
            return {
                hour: _moment.hour(),
                minute: _moment.minute(),
                second: _moment.second()
            };
        }
    };
    // 扩展插件原型
    $.extend(Datepicker.prototype, {
        _init: function() {
            var min = moment(this.option("minDate"));
            var max = moment(this.option("maxDate"));
            var today = util.momentDate(this.option("today"));
            var val = this.$el.is(VALUEFIELD) ? this.$el.val() : this.$el.data("val");
            var date = val || this.option("date");
            var _moment = this.val2Moment(date);
            this.__inline = this.option("inline") && this.$el.not(TOGGLE);
            this.__view = this.option("toSelect");
            this.__count = parseInt(this.option("displayCount"), 10) || 1;
            this.__today = today.isValid() ? today.unix() : null;
            this.__min = min.isValid() ? min.unix() : null;
            this.__max = max.isValid() ? max.unix() : null;
            if (_moment && this.isMomentValid(_moment)) {
                _moment = _moment.unix();
            } else {
                _moment = null;
            }
            this.__moment = _moment;
            this.resetStartDate();
            //初始化事件
            if (!this.__inline) {
                if (this.option("showIcon")) {
                    $(this.option("tpl_icon")).insertAfter(this.$el).on("mousedown." + PLUGIN_NS, $.proxy(this.toggleOnEvent, this));
                }
                $document.on("mousedown." + PLUGIN_NS, $.proxy(this.closeOnEvent, this));
                this.$el.on("mousedown." + PLUGIN_NS, $.proxy(this.showOnEvent, this));
            } else {
                this.show();
            }
            //销毁初始化方法
            delete this._init;
            return this;
        },
        //创建容器
        _createContainer: function() {
            //构建UI
            if (!this.__inline) {
                var cid = moment() + "";
                var p = this.__$$popup = Popup.create(cid, {
                    tpl: "<div/>",
                    alignTo: this.$el,
                    align: this.option("align"),
                    scrolling: this.option("scrolling") || false
                })._init();
                //取消冒泡
                this.$container = p.$popup.on("mousedown." + PLUGIN_NS, function(e) {
                    e.stopPropagation();
                });
                //打开后重新渲染
                p.on("opened", $.proxy(this.renderContainer, this));
                //关闭后选中
                p.on("hidden", $.proxy(this.selectOnClose, this));
                //关闭后清空
                p.on("hidden", function() {
                    this.$popup.empty();
                });
            } else {
                this.$container = $(this.option("container"));
                if (!this.$container.length) {
                    this.$container = this.$el;
                }
                this.renderContainer();
            }
            //事件绑定
            this.bindEvent("click", '[role="prev"]', "switchToPrevView");
            this.bindEvent("click", '[role="next"]', "switchToNextView");
            this.bindEvent("click", '[role="title"]', "switchToHigherView");
            this.bindEvent("click", '[role="gotoday"]', "goTodayOnEvent");
            this.bindEvent("click", '[role="clear"]', "clearOnEvent");
            this.bindEvent("click", '[role="close"]', "closeOnEvent");
            this.bindEvent("click", '[role="date"]', "selectOnDateClick");
        },
        renderContainer: function() {
            var html = this.option("tpl", [ {
                data: this.formatSelectedDateTime()
            } ]);
            this.$container.empty().append(html);
            this.renderView(this.option("toSelect"));
            this.showTimepicker();
        },
        /*
         * 面板操作
         */
        //显示日历
        show: function() {
            if (!this.$container) {
                this._createContainer();
            }
            this.openPopup();
        },
        showOnEvent: function(e) {
            e.stopPropagation();
            this.show();
        },
        toggle: function() {
            if (!this.__isOpened) {
                this.show();
            } else {
                this.closePopup();
            }
        },
        toggleOnEvent: function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        },
        //打开日历面板
        openPopup: function() {
            var p = this.__$$popup;
            if (p && !p.__isOpened) {
                this.__temp = null;
                this.__view = this.option('toSelect');
                this.resetStartDate();
                p.open();
                this.__isOpened = true;
                this.triggerHandler("opened:popup");
            }
        },
        //关闭日历面板
        closePopup: function() {
            var p = this.__$$popup;
            if (p && p.__isOpened) {
                p.hide();
                this.__isOpened = false;
                this.triggerHandler("closed:popup");
            }
        },
        closeOnEvent: function(e) {
            e.stopPropagation();
            this.closePopup();
        },
        /*
         * 视图
         */
        //载入视图
        renderView: function(view) {
            if (!this.$container) {
                return;
            }
            var view = view || this.__view;
            //隐藏所有视图
            this.$role("view").children().hide();
            //显示当前视图
            switch (view) {
              case "year":
                this.renderYearView();
                break;

              case "month":
                this.renderMonthView();
                break;

              default:
                this.renderDateView();
            }
        },
        //载入日视图
        renderDateView: function() {
            var start = this.startDate();
            var count = this.displayCount();
            var data = this.getDateViewData(start.unix(), count);
            for (var i = 0; i < count; i++) {
                //设置标题
                this.setTitle({
                    year: start.year(),
                    month: start.month() + 1
                }, i);
                //载入视图
                this.$view("date").eq(i).html(this.option("tpl_date", [ {
                    data: _.matrix(data[i], 7)
                } ])).show();
                //下个月
                start.add(1, "M");
            }
            this.__$$popup && this.__$$popup.reset();
        },
        //载入月视图
        renderMonthView: function() {
            var year = this.startDate().year();
            var count = this.displayCount();
            var data = this.getMonthViewData(year, count);
            for (var i = 0; i < count; i++) {
                //设置标题
                this.setTitle({
                    year: year
                }, i);
                //载入视图
                this.$view("month").eq(i).html(this.option("tpl_month", [ {
                    data: _.matrix(data[i], 4)
                } ])).show();
                //下一个日期
                year++;
            }
            this.__$$popup && this.__$$popup.reset();
        },
        //载入年视图
        renderYearView: function() {
            var year = this.startDate().year();
            var count = this.displayCount();
            var data = this.getYearViewData(year, count);
            for (var i = 0; i < count; i++) {
                //设置标题
                this.setTitle({
                    year: [ data[i][0].year, data[i][data[i].length - 1].year ]
                }, i);
                //载入视图
                this.$view("year").eq(i).html(this.option("tpl_year", [ {
                    data: _.matrix(data[i], 4)
                } ])).show();
            }
            this.__$$popup && this.__$$popup.reset();
        },
        //切换到上一个同级视图
        switchToPrevView: function() {
            if (this.option("animation")) {
                if (this.isAnimating()) {
                    return;
                }
                this.onAniStart();
                this.animateHandler("clone");
                if (!this.prevView()) {
                    this.onAniEnd();
                    return;
                }
                this.animateHandler("slideRight");
            } else {
                this.prevView();
            }
        },
        //切换到下一个同级视图
        switchToNextView: function() {
            if (this.option("animation")) {
                if (this.isAnimating()) {
                    return;
                }
                this.onAniStart();
                this.animateHandler("clone");
                if (!this.nextView()) {
                    this.onAniEnd();
                    return;
                }
                this.animateHandler("slideLeft");
            } else {
                this.nextView();
            }
        },
        //切换层级视图
        doLevelSwitch: function(view) {
            if (this.option("animation")) {
                if (this.isAnimating()) {
                    return;
                }
                this.onAniStart();
                this.animateHandler("fadeOut");
                this.__view = view;
                this.animateHandler("hide");
                this.renderView();
                this.animateHandler("fadeIn");
            } else {
                this.__view = view;
                this.renderView();
            }
        },
        //切换到上一级视图
        switchToHigherView: function() {
            var toSelect = this.option("toSelect"), allow, view;
            switch (toSelect) {
              case "year":
                allow = {};
                break;

              case "month":
                allow = {
                    month: "year"
                };
                break;

              case "date":
                allow = {
                    month: "year",
                    date: "month"
                };
            }
            if (view = allow[this.__view]) {
                this.doLevelSwitch(view);
            }
        },
        //切换到下一级视图
        switchToLowerView: function() {
            var toSelect = this.option("toSelect"), allow, view;
            switch (toSelect) {
              case "year":
                allow = {};
                break;

              case "month":
                allow = {
                    year: "month"
                };
                break;

              case "date":
                allow = {
                    year: "month",
                    month: "date"
                };
            }
            if (view = allow[this.__view]) {
                this.doLevelSwitch(view);
            }
        },
        //上个同级视图
        prevView: function() {
            var view = this.__view, flag;
            this.__cloneView = this.cloneView();
            switch (view) {
              case "year":
                flag = this.prevYearView();
                break;

              case "month":
                flag = this.prevMonthView();
                break;

              default:
                flag = this.prevDateView();
            }
            return flag;
        },
        //上个日视图
        prevDateView: function() {
            var start = this.startDate();
            var count = this.displayCount();
            var prev = start.subtract(count, "M");
            return this.setStartDate(prev);
        },
        //上个月视图
        prevMonthView: function() {
            var start = this.startDate();
            var min = this.min();
            var count = this.displayCount();
            var month = Math.max(min.month(), start.month() - 12 * count);
            var prev = start.month(month).subtract(1, 'year');
            return this.setStartDate(prev);
        },
        //上个年视图
        prevYearView: function() {
            var start = this.startDate();
            var min = this.min();
            var count = this.displayCount();
            var year = Math.max(min.year(), start.year() - 12 * count);
            var prev = start.year(year);
            return this.setStartDate(prev);
        },
        //下个同级视图
        nextView: function() {
            var view = this.__view, flag;
            switch (view) {
              case "year":
                flag = this.nextYearView();
                break;

              case "month":
                flag = this.nextMonthView();
                break;

              default:
                flag = this.nextDateView();
            }
            return flag;
        },
        //下个日视图
        nextDateView: function() {
            var start = this.startDate();
            var count = this.displayCount();
            var next = start.add(count, "M");
            return this.setStartDate(next);
        },
        //下个月视图
        nextMonthView: function() {
            var start = this.startDate();
            var max = this.max();
            var count = this.displayCount();
            var month = Math.min(max.month(), start.month() + 12 * count);
            var next = start.month(month).add(1, 'year');
            return this.setStartDate(next);
        },
        //下个年视图
        nextYearView: function() {
            var start = this.startDate();
            var max = this.max();
            var count = this.displayCount();
            var year = Math.min(max.year(), start.year() + 12 * count);
            var next = start.year(year);
            return this.setStartDate(next);
        },
        /*
         * 动画
         */
        //克隆view
        cloneView: function() {
            return this.$role("view").clone().attr("role", "clone");
        },
        //动画处理
        animateHandler: function(type) {
            switch (type) {
              case "clone":
                this.__cloneView = this.cloneView();
                break;

              case "hide":
                this.$view(this.__view).css("opacity", 0);
                break;

              case "fadeIn":
                //执行动画
                this.$view(this.__view).stop().animate({
                    opacity: 1
                }, $.proxy(this.onAniEnd, this));
                break;

              case "fadeOut":
                //执行动画
                this.$view(this.__view).stop().animate({
                    opacity: 0
                }, $.proxy(this.onAniEnd, this));
                break;

              case "slideLeft":
                var $el = this.$role("view");
                //复制view
                var $copy = this.__cloneView || this.cloneView();
                var width = this.option("panelWidth") || 0;
                var val = width * this.displayCount();
                var onAniEnd = $.proxy(this.onAniEnd, this);
                //将复制view置于舞台
                $el.after($copy).css("left", val);
                //执行动画
                $el.stop().animate({
                    left: 0
                }, onAniEnd);
                $copy.stop().animate({
                    left: "-=" + val
                }, onAniEnd);
                break;

              case "slideRight":
                //复制view
                var $el = this.$role("view");
                var $copy = this.__cloneView || this.cloneView();
                var width = this.option("panelWidth") || 0;
                var val = width * this.displayCount();
                var onAniEnd = $.proxy(this.onAniEnd, this);
                //将复制view置于舞台
                $el.before($copy).css("left", -val);
                //执行动画
                $el.stop().animate({
                    left: 0
                }, onAniEnd);
                $copy.stop().animate({
                    left: "+=" + val
                }, onAniEnd);
                break;
            }
        },
        //是否动画中
        isAnimating: function() {
            return this.__animating;
        },
        //动画开始时执行
        onAniStart: function() {
            this.__animating = true;
        },
        //动画结束时执行
        onAniEnd: function() {
            if (this.__cloneView) {
                this.__cloneView.remove();
                this.__cloneView = null;
            }
            this.__animating = false;
        },
        /*
         * 日期判断
         */
        //判断是否今天
        isToday: function(_moment) {
            var today = this.today();
            return util.isDateSame(_moment, today);
        },
        //判断时间是否合法
        isMomentValid: function(_moment, onError) {
            if (!this.option("timepicker")) {
                return this.isDateValid(_moment, onError);
            }
            if (!_moment) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "invalid_date", _moment ]);
                }
                return false;
            }
            var min = this.min();
            var max = this.max();
            if (min && _moment.isBefore(min)) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "before_min_date", util.momentObj(_moment) ]);
                }
                return false;
            }
            if (max && _moment.isAfter(max)) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "after_max_date", util.momentObj(_moment) ]);
                }
                return false;
            }
            return true;
        },
        //判断日期是否合法
        isDateValid: function(_moment, onError) {
            if (!_moment) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "invalid_date", _moment ]);
                }
                return false;
            }
            var min = this.min();
            var max = this.max();
            if (min && util.isDateBefore(_moment, min)) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "before_min_date", util.momentObj(_moment) ]);
                }
                return false;
            }
            if (max && util.isDateAfter(_moment, max)) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "after_max_date", util.momentObj(_moment) ]);
                }
                return false;
            }
            return true;
        },
        //判断日期是否选中
        isDateSelected: function(_moment) {
            var date = this.moment();
            if (!_moment || !date) {
                return false;
            }
            return util.isDateSame(_moment, date);
        },
        //判断日期是否激活
        isDateActive: function(_moment) {
            return this.isDateSelected(_moment);
        },
        /*
         * 日期转换
         */
        //转moment对象
        val2Moment: function(val) {
            if (!val) {
                return null;
            }
            if (moment.isMoment(val)) {
                return val;
            }
            var format = this.getFormat();
            var _moment;
            if (typeof val === "string") {
                _moment = moment(val, format, true);
            } else {
                _moment = moment(val);
            }
            if (_moment.isValid()) {
                return _moment;
            }
            return null;
        },
        /*
         * 日期格式化
         */
        //根据所选类型（toSelect选项指定）获取格式
        getFormat: function() {
            var type = this.option("toSelect");
            if (type == "date" && this.option("timepicker")) {
                var dateFormat = this.getFormatByType("date");
                var timeFormat = this.getFormatByType("time");
                var separator = this.option("separator");
                return [ dateFormat, timeFormat ].join(separator);
            }
            return this.getFormatByType(type);
        },
        //根据类型（year|month|date|time）获取格式
        getFormatByType: function(type) {
            var format;
            switch (type) {
              case "year":
                format = this.option("yearFormat");
                break;

              case "month":
                format = this.option("monthFormat");
                break;

              case "time":
                format = this.option("timeFormat");
                break;

              default:
                format = this.option("dateFormat");
            }
            return format;
        },
        //格式化日期（如：'YYYY-MM-DD'）
        formatDate: function(date) {
            return moment(date).format(this.getFormatByType("date"));
        },
        //格式化时间（如：'HH:mm:ss'）
        formatTime: function(time) {
            return moment(time).format(this.getFormatByType("time"));
        },
        //格式化moment（如：'YYYY-MM-DD HH:mm:ss'）
        formatMoment: function(_moment) {
            if (!_moment) {
                return "";
            }
            var format = this.getFormat();
            return _moment.format(format);
        },
        //格式化日期时间（如： {date:'YYYY-MM-DD',time:'HH:mm:ss'}）
        formatDateTime: function(_moment) {
            if (!_moment) {
                return this.formatEmptyDateTime();
            }
            _moment = moment(_moment);
            return {
                date: this.formatDate(_moment),
                time: this.formatTime({
                    hour: _moment.hour(),
                    minute: _moment.minute(),
                    second: _moment.second()
                })
            };
        },
        //格式化空日期时间（如： {date:'未选择',time:'00:00:00'}）
        formatEmptyDateTime: function() {
            return {
                date: this.option("lang").emptydate,
                time: this.formatTime({
                    hour: 0,
                    minute: 0,
                    second: 0
                })
            };
        },
        //格式化选中日期时间（如： {date:'YYYY-MM-DD',time:'HH:mm:ss'}）
        formatSelectedDateTime: function() {
            var _moment = this.moment();
            return this.formatDateTime(_moment);
        },
        /*
         * 获取
         */
        //面板个数
        displayCount: function() {
            return this.__count;
        },
        //是否行内模式
        inline: function() {
            return this.__inline;
        },
        //是否显示时间选择
        hasTimepicker: function() {
            return this.option("toSelect") == "date" && this.option("timepicker");
        },
        //今天
        today: function() {
            return this.__today ? moment.unix(this.__today) : null;
        },
        //最小时间
        min: function() {
            return this.__min ? moment.unix(this.__min) : null;
        },
        //最大时间
        max: function() {
            return this.__max ? moment.unix(this.__max) : null;
        },
        //选中时间
        moment: function() {
            return this.__moment ? moment.unix(this.__moment) : null;
        },
        //视图开始日期
        startDate: function() {
            return this.__startDate ? moment.unix(this.__startDate) : null;
        },
        //获取数据
        getDataByMoment: function(_moment) {
            if (!_moment) {
                return null;
            }
            return util.momentObj(_moment);
        },
        //获取选中数据
        getSelectedData: function() {
            var _moment = this.moment();
            return this.getDataByMoment(_moment) || {};
        },
        //获取日期视图数据
        getDateViewData: function(dateUnix, count) {
            var _this = this, result = [], maxRow = 6;
            var date = moment.unix(dateUnix);
            count = parseInt(count, 10) || 1;
            for (var i = 0; i < count; i++) {
                //本月开始日期
                var start = date.clone().startOf("M");
                //本月结束日期
                var end = date.clone().endOf("M");
                //本月天数
                var currentDays = end.date();
                //显示上月天数
                var prevDays = start.day();
                //显示下月天数
                var nextDays = 6 - end.day();
                //实际行数
                var actualRow = Math.ceil((currentDays + prevDays + nextDays) / 7);
                //额外天数
                var extraDays = (maxRow - actualRow) * 7;
                //本月范围
                var current = util.momentRange(start, end);
                var rows = [];
                function dataFormatter(date, status) {
                    rows.push($.extend({
                        year: date.year(),
                        month: date.month(),
                        date: date.date()
                    }, _this.getDateStatus(date, status)));
                }
                if (prevDays > 0) {
                    //上月范围
                    var prev = util.momentRange(start.clone().startOf("w"), start.clone().subtract(1, "d"));
                    prev.by("days", function(date) {
                        dataFormatter(date.clone(), {
                            other: true
                        });
                    });
                }
                current.by("days", function(date) {
                    dataFormatter(date.clone(), {
                        other: false
                    });
                });
                if (nextDays > 0 || extraDays > 0) {
                    //下月范围
                    var next = util.momentRange(end.clone().add(1, "d"), end.clone().endOf("w").add(extraDays, "d"));
                    next.by("days", function(date) {
                        dataFormatter(date.clone(), {
                            other: true
                        });
                    });
                }
                result.push(rows);
                //下个月
                date.add(1, "M");
            }
            return result;
        },
        //获取月份视图数据
        getMonthViewData: function(year, count) {
            var result = [];
            count = parseInt(count, 10) || 1;
            for (var i = 0; i < count; i++) {
                var arr = [];
                for (var j = 0; j < 12; j++) {
                    var val = {
                        year: year,
                        month: j
                    };
                    arr.push($.extend(val, this.getDateStatus(val)));
                }
                result.push(arr);
                year++;
            }
            return result;
        },
        //获取年份视图数据
        getYearViewData: function(year, count) {
            var minYear = this.min().year();
            var maxYear = this.max().year();
            var startYear = year - (parseInt((year + "").slice(-1), 10) || 0) - 1;
            var result = [];
            count = parseInt(count, 10) || 1;
            for (var i = 0; i < count; i++) {
                var arr = [];
                for (var j = 0; j < 12; j++) {
                    var val = {
                        year: startYear
                    };
                    var status = {
                        disabled: startYear > maxYear || startYear < minYear,
                        other: j == 0 || j == 11
                    };
                    arr.push($.extend(val, this.getDateStatus(val, status)));
                    startYear++;
                }
                result.push(arr);
                startYear -= 2;
            }
            return result;
        },
        //获取日期状态信息
        getDateStatus: function(date, options) {
            if (!moment.isMoment(date)) {
                var defaults = {};
                var _moment = this.moment();
                if (_moment) {
                    defaults = {
                        year: _moment.year(),
                        month: _moment.month(),
                        date: _moment.date()
                    };
                }
                date = util.momentDate($.extend(defaults, date));
            }
            var result = {
                active: this.isDateActive(date),
                disabled: !this.isDateValid(date),
                isToday: this.isToday(date)
            }, cls = [];
            $.extend(result, options);
            if (result.active) {
                cls.push(this.option("activeCls"));
            }
            if (result.disabled) {
                cls.push(this.option("disabledCls"));
            }
            if (result.other) {
                cls.push(this.option("otherCls"));
            }
            if (result.isToday) {
                cls.push(this.option("todayCls"));
            }
            result.cls = cls.join(" ");
            return result;
        },
        /*
         * 设置
         */
        //设置标题
        setTitle: function(data, index) {
            var view = this.__view;
            var title = "";
            switch (view) {
              case "year":
                title = this.option("yearViewTitleFormatter", [ data.year[0], data.year[1] ]);
                break;

              case "month":
                title = this.option("monthViewTitleFormatter", [ data.year ]);
                break;

              default:
                title = this.option("dateViewTitleFormatter", [ data.year, data.month ]);
            }
            this.$role("title").eq(index).html(title);
        },
        //设置视图开始日期（指定日期当月第一天）
        setStartDate: function(date) {
            if (!date) {
                return this.resetStartDate();
            }
            var count = this.displayCount();
            var min = this.min();
            var max = this.max();
            var end = util.momentDate(date).add(count, "M");
            var range = util.momentDateRange(date, end);
            var limit = util.momentDateRange(min, max);
            if (util.isRangeOverlaps(range, limit)) {
                this.__startDate = util.momentDate(date).unix();
                this.renderView();
                return true;
            }
            return false;
        },
        //重置视图开始日期（选中日期|今天）
        resetStartDate: function() {
            var date = util.momentDate(this.moment() || this.today()).startOf("M");
            return this.setStartDate(date);
        },
        //设置最小值
        setMin: function(val) {
            if (!val) {
                return;
            }
            var _moment = moment(val);
            if (_moment.isValid()) {
                var min = _moment.unix();
                var max = this.__max;
                if (min <= max) {
                    this.__min = min;
                    this.__startDate = Math.min(Math.max(this.__startDate, min), max);
                }
            }
        },
        //设置最大值
        setMax: function(val) {
            if (!val) {
                return;
            }
            var _moment = moment(val);
            if (_moment.isValid()) {
                var min = this.__min;
                var max = _moment.unix();
                if (max >= min) {
                    this.__max = max;
                    this.__startDate = Math.min(Math.max(this.__startDate, min), max);
                }
            }
        },
        //根据日期设置moment
        setMomentByDate: function(date, options) {
            var val = null;
            if (date) {
                var data = this.getSelectedData();
                $.extend(data, util.dateObj(date));
                if (this.option("timepicker")) {
                    var min = this.min();
                    var max = this.max();
                    var minTime = util.timeObj(min);
                    var maxTime = util.timeObj(max);
                    var time = this.getTimeByRole("time");
                    if (util.isDateSame(data, min) && util.isDurationLt(time, minTime)) {
                        time = minTime;
                        this.setTimeByRole("time", minTime, {
                            silent: true
                        });
                    } else if (util.isDateSame(data, max) && util.isDurationGt(time, maxTime)) {
                        time = maxTime;
                        this.setTimeByRole("time", maxTime, {
                            silent: true
                        });
                    }
                    $.extend(data, time);
                }
                val = data;
            }
            return this.setMoment(val, options);
        },
        //根据时间设置moment
        setMomentByTime: function(time, options) {
            var val = null;
            if (!this.moment()) {
                return false;
            }
            if (time) {
                var data = this.getSelectedData();
                val = $.extend({}, data, util.timeObj(time));
            }
            return this.setMoment(val, $.extend({
                stay: true
            }, options));
        },
        //设置moment
        setMoment: function(val, options) {
            var _moment = null;
            var _momentData = null;
            var _momentText = "";
            var old = this.moment();
            var oldData = this.getDataByMoment(old);
            var changed = !!old;
            var onError = function() {
                this.triggerHandler("invalid", arguments);
            };
            if (val) {
                _moment = this.val2Moment(val);
                if (!this.isMomentValid(_moment, onError)) {
                    this.renderContainer();
                    return this.formatMoment(old);
                }
                _momentText = this.formatMoment(_moment);
                _momentData = this.getDataByMoment(_moment);
                changed = !(old && _moment.isSame(old));
            }
            if (changed) {
                var args = [ _momentText, _momentData, oldData ];
                var flag = this.triggerHandler("before:select", args);
                //判断是否选中
                if (flag) {
                    this.__moment = _moment ? _moment.unix() : null;
                    if (this.$el.is(VALUEFIELD)) {
                        this.$el.val(_momentText);
                    }
                    this.toggleResult();
                    this.resetStartDate();
                    options = options || {};
                    if (!options.stay) {
                        this.closePopup();
                    }
                    if (!options.silent) {
                        this.triggerHandler("changed", args);
                    }
                    return true;
                }
            }
            return false;
        },
        toggleResult: function() {
            if (!this.$container) {
                return;
            }
            var data = this.formatSelectedDateTime();
            this.$role("date-val").val(data.date);
            this.restrainTimepicker();
        },
        //选择
        select: function(val, options) {
            return this.setMoment(val, options);
        },
        /*
         * 用户操作
         */
        //选择日期
        _selectDate: function(date) {
            this.setMomentByDate(date);
        },
        //点选
        selectOnDateClick: function(e) {
            var $el = $(e.currentTarget);
            var date = {
                year: $el.data("year"),
                month: $el.data("month"),
                date: $el.data("date")
            };
            if (this.__view != this.option("toSelect")) {
                var start = util.momentDate(date).startOf("M");
                this.setStartDate(start);
                this.switchToLowerView();
                return;
            }
            this._selectDate(date);
        },
        //关闭时选择
        selectOnClose: function() {
            if (!this.$el.is(TEXTFIELD)) {
                return;
            }
            var result = this.setMoment(this.$el.val());
            if (typeof result === "string") {
                this.$el.val(result);
            }
        },
        //转到今天
        goToday: function() {
            var today = this.today();
            this.setStartDate(today);
            this._selectDate(today);
        },
        goTodayOnEvent: function() {
            this.goToday();
        },
        //清除选中值
        clear: function() {
            this._selectDate(null);
        },
        clearOnEvent: function() {
            this.clear();
        },
        /*
         * timepicker
         */
        //显示时间选择
        showTimepicker: function(options) {
            var _this = this;
            if (!this.option("timepicker")) {
                return;
            }
            var defaults = {
                format: this.option("timeFormat"),
                showSecond: this.option("showSecond"),
                onChanged: function(e, time, timeObj) {
                    _this.setMomentByTime(timeObj);
                }
            };
            options = $.extend(defaults, options);
            this.$role("time").timepicker(options);
        },
        //当前时分秒
        getTimeByRole: function(role) {
            return this.$role(role).timepicker("getTime");
        },
        //设置时分秒
        setTimeByRole: function(role, val, options) {
            this.$role(role).timepicker("setTime", val, options);
        },
        //设置时分秒最小值
        setMinTimeByRole: function(role, val) {
            this.$role(role).timepicker("setMin", val);
        },
        //设置时分秒最大值
        setMaxTimeByRole: function(role, val) {
            this.$role(role).timepicker("setMax", val);
        },
        //限制时间选择范围
        restrainTimepicker: function() {
            if (!this.option("timepicker")) {
                return;
            }
            var moment = this.moment();
            var min = this.min();
            var max = this.max();
            if (moment) {
                if (util.isDateSame(moment, min)) {
                    this.setMinTimeByRole("time", util.timeObj(min));
                }
                if (util.isDateSame(moment, max)) {
                    this.setMaxTimeByRole("time", util.timeObj(max));
                }
            } else {
                this.setMinTimeByRole("time", null);
                this.setMaxTimeByRole("time", null);
            }
        },
        /*
         * dom相关
         */
        $: function(selector) {
            return this.$container.find(selector);
        },
        //获取指定role属性的dom元素
        $role: function(role, operator) {
            operator = operator || "";
            return this.$container.find("[role" + operator + '="' + role + '"]');
        },
        //获取指定type的view元素
        $view: function(view) {
            return this.$role("view").find('[role="' + view + '-view"]');
        },
        /*
         * 事件接口
         */
        bindEvent: function(ev, target, fn) {
            if (this.$container) {
                this.$container.on(ev + "." + PLUGIN_NS, target, $.proxy(this[fn], this));
            }
        },
        //将配置中的接口绑定到事件
        bindAll: function() {
            var _this = this;
            $.each(this.options, function(key, value) {
                if (/^on[A-Z]/.test(key) && $.isFunction(value)) {
                    var ev = _.interface2Event(key);
                    _this.$el.on(ev + "." + PLUGIN_NS, $.proxy(value, _this));
                }
            });
        },
        //触发事件回调
        triggerHandler: function(ev, args) {
            var event = $.Event(ev + "." + PLUGIN_NS);
            this.$el.trigger(event, args || []);
            return !event.isDefaultPrevented();
        },
        /*
         * 配置相关
         */
        //解析配置：若配置项为方法，则返回方法执行结果（args为方法参数数组）
        option: function(name, args) {
            return _.resultWith(this.options, name, args, this);
        },
        //设置配置
        setOptions: function(name, val) {
            var options = {};
            if (typeof name == "string") {
                val != undefined && (options[name] = val);
            } else {
                options = name;
            }
            $.extend(this.options, options);
            return this;
        },
        //获取插件实例
        instance: function() {
            return this;
        }
    });
    //时间范围选择类
    function Daterangepicker(element, options) {
        return this.constructor.__base__.apply(this, arguments);
    }
    // 继承Datepicker
    _.inherits(Daterangepicker, Datepicker);
    // 扩展Daterangepicker原型
    $.extend(Daterangepicker.prototype, {
        _init: function() {
            var min = moment(this.option("minDate"));
            var max = moment(this.option("maxDate"));
            var today = util.momentDate(this.option("today"));
            var val = this.$el.is(VALUEFIELD) ? this.$el.val() : this.$el.data("val");
            var date = val || this.option("date");
            var range = this.val2Range(date);
            this.__inline = this.option("inline") && this.$el.not(TOGGLE);
            this.__view = this.option("toSelect");
            this.__count = parseInt(this.option("displayCount"), 10) || 2;
            this.__today = today.isValid() ? today.unix() : null;
            this.__min = min.isValid() ? min.unix() : null;
            this.__max = max.isValid() ? max.unix() : null;
            if (range && this.isRangeValid(range)) {
                range = {
                    start: range.start.unix(),
                    end: range.end.unix()
                };
            } else {
                range = null;
            }
            this.__moment = range;
            this.__temp = null;
            this.resetStartDate();
            //初始化事件
            if (!this.__inline) {
                if (this.option("showIcon")) {
                    $(this.option("tpl_icon")).insertAfter(this.$el).on("mousedown." + PLUGIN_NS, $.proxy(this.toggleOnEvent, this));
                }
                $document.on("mousedown." + PLUGIN_NS, $.proxy(this.closeOnEvent, this));
                this.$el.on("mousedown." + PLUGIN_NS, $.proxy(this.showOnEvent, this));
            } else {
                this.show();
            }
            //销毁初始化方法
            delete this._init;
            return this;
        },
        /*
         * 日期判断
         */
        //判断日期是否选中
        isDateSelected: function(_moment) {
            var range = this.moment();
            if (!_moment || !range) {
                return false;
            }
            return util.momentDateRange(range.start, range.end).contains(_moment);
        },
        //判断日期是否激活
        isDateActive: function(_moment) {
            if (this.__temp) {
                return util.isDateSame(_moment, this.__temp);
            }
            return this.isDateSelected(_moment);
        },
        //判断日期范围是否合法
        isRangeValid: function(range, onError) {
            if (!range) {
                if ($.isFunction(onError)) {
                    onError.apply(this, [ "invalid_range", range ]);
                }
                return false;
            }
            var flag = this.isMomentValid(range.start, onError) && this.isMomentValid(range.end, onError);
            if (flag) {
                var minCount = parseInt(this.option("minSelectCount"), 10) || 2;
                var maxCount = parseInt(this.option("maxSelectCount"), 10) || 0;
                var i = 0;
                //计算范围内天数（非时长）
                util.momentDateRange(range.start, range.end).by("days", function() {
                    i++;
                });
                if (minCount && i < minCount) {
                    if ($.isFunction(onError)) {
                        onError.apply(this, [ "exceed_min_select_count", range ]);
                    }
                    return false;
                }
                if (maxCount && i > maxCount) {
                    if ($.isFunction(onError)) {
                        onError.apply(this, [ "exceed_max_select_count", range ]);
                    }
                    return false;
                }
            }
            return flag;
        },
        //判断是否开始日期
        isRangeStart: function(_moment) {
            var range = this.moment();
            if (!_moment || !range) {
                return false;
            }
            return util.isDateSame(_moment, range.start);
        },
        //判断是否结束日期
        isRangeEnd: function(_moment) {
            var range = this.moment();
            if (!_moment || !range) {
                return false;
            }
            return util.isDateSame(_moment, range.end);
        },
        /*
         * 日期转换
         */
        //转range对象
        val2Range: function(val) {
            var start, end;
            if (!val) {
                return null;
            }
            if (typeof val === "string") {
                var separator = this.option("rangeSeparator");
                var temp = val.split(separator);
                start = this.val2Moment(temp[0]);
                end = this.val2Moment(temp[1]);
                if (!start || !end) {
                    return null;
                }
            } else {
                start = val.start;
                end = val.end;
            }
            return util.momentRange(start, end);
        },
        /*
         * 日期格式化
         */
        //格式化range（如：'YYYY-MM-DD HH:mm:ss,YYYY-MM-DD HH:mm:ss'）
        formatRange: function(range) {
            if (!range) {
                return "";
            }
            var format = this.getFormat();
            var separator = this.option("rangeSeparator");
            return [ range.start.format(format), range.end.format(format) ].join(separator);
        },
        //格式化选中日期时间（如： {start:{date:'YYYY-MM-DD',time:'HH:mm:ss'},end:{date:'YYYY-MM-DD',time:'HH:mm:ss'}}）
        formatSelectedDateTime: function() {
            var data = {};
            var range = this.moment();
            if (range) {
                data.start = this.formatDateTime(range.start);
                data.end = this.formatDateTime(range.end);
            } else {
                data.start = this.formatEmptyDateTime();
                data.end = this.formatEmptyDateTime();
            }
            return data;
        },
        /*
         * 获取
         */
        //当前选中时间
        moment: function() {
            var range = this.__moment;
            if (range) {
                return {
                    start: moment.unix(range.start),
                    end: moment.unix(range.end)
                };
            }
            return null;
        },
        //获取数据
        getDataByMoment: function(_moment) {
            var data = {};
            if (!_moment) {
                return data;
            }
            data.start = util.momentObj(_moment.start);
            data.end = util.momentObj(_moment.end);
            return data;
        },
        //获取日期状态信息
        getDateStatus: function(date, options) {
            if (!moment.isMoment(date)) {
                var defaults = {};
                var range = this.moment();
                if (range) {
                    var _moment = range.start;
                    defaults = {
                        year: _moment.year(),
                        month: _moment.month(),
                        date: _moment.date()
                    };
                }
                date = util.momentDate($.extend(defaults, date));
            }
            var result = {
                active: this.isDateActive(date),
                disabled: !this.isDateValid(date),
                isToday: this.isToday(date),
                start: this.isRangeStart(date),
                end: this.isRangeEnd(date)
            }, cls = [];
            $.extend(result, options);
            if (result.active) {
                cls.push(this.option("activeCls"));
            }
            if (result.disabled) {
                cls.push(this.option("disabledCls"));
            }
            if (result.other) {
                cls.push(this.option("otherCls"));
            }
            if (result.isToday) {
                cls.push(this.option("todayCls"));
            }
            if (result.start) {
                cls.push(this.option("rangeStartCls"));
            }
            if (result.end) {
                cls.push(this.option("rangeEndCls"));
            }
            result.cls = cls.join(" ");
            return result;
        },
        /*
         * 设置
         */
        //重置视图开始日期（选中日期|今天）
        resetStartDate: function() {
            var _moment = this.__moment;
            if (_moment) {
                date = moment.unix(_moment.start);
            } else {
                date = this.today();
            }
            date = date.clone().startOf("M");
            return this.setStartDate(date);
        },
        //根据日期设置moment
        // date:{start:{},end:{}}
        setMomentByDate: function(date, options) {
            var val = null;
            if (date) {
                var data = this.getSelectedData();
                data.start = $.extend(data.start || {}, util.dateObj(date.start));
                data.end = $.extend(data.end || {}, util.dateObj(date.end));
                if (this.option("timepicker")) {
                    var min = this.min();
                    var max = this.max();
                    var minTime = util.timeObj(min);
                    var maxTime = util.timeObj(max);
                    var startTime = this.getTimeByRole("starttime");
                    var endTime = this.getTimeByRole("endtime");
                    if (util.isDateSame(data.start, min) && util.isDurationLt(startTime, minTime)) {
                        startTime = minTime;
                        this.setTimeByRole("starttime", minTime, {
                            silent: true
                        });
                    }
                    if (util.isDateSame(data.end, max) && util.isDurationGt(endTime, maxTime)) {
                        endTime = maxTime;
                        this.setTimeByRole("endtime", maxTime, {
                            silent: true
                        });
                    }
                    data.start = $.extend(data.start, startTime);
                    data.end = $.extend(data.end, endTime);
                }
                val = data;
            }
            return this.setMoment(val, options);
        },
        //根据时间设置moment
        // time:{start:{},end:{}}
        setMomentByTime: function(time, options) {
            var val = null;
            if (!this.moment()) {
                return;
            }
            if (time) {
                var data = this.getSelectedData();
                if (time.start) {
                    data.start = $.extend(data.start || {}, util.timeObj(time.start));
                }
                if (time.end) {
                    data.end = $.extend(data.end || {}, util.timeObj(time.end));
                }
                val = data;
            }
            return this.setMoment(val, $.extend({
                stay: true
            }, options));
        },
        //设置moment
        setMoment: function(val, options) {
            var _moment = null;
            var _momentData = null;
            var _momentText = "";
            var old = this.moment();
            var oldData = this.getDataByMoment(old);
            var changed = false;
            var onError = function() {
                this.triggerHandler("invalid", arguments);
            };
            if (!val) {
                changed = !!old;
            } else {
                var range = this.val2Range(val);
                if (!this.isRangeValid(range, onError)) {
                    this.renderContainer();
                    return this.formatRange(old);
                }
                _moment = range;
                _momentText = this.formatRange(range);
                _momentData = this.getDataByMoment(_moment);
                changed = !(old && range.isSame(old));
            }
            if (changed) {
                var args = [ _momentText, _momentData, oldData ];
                var flag = this.triggerHandler("before:select", args);
                //判断是否选中
                if (flag) {
                    if (_moment) {
                        this.__moment = {
                            start: _moment.start.unix(),
                            end: _moment.end.unix()
                        };
                    } else {
                        this.__moment = null;
                    }
                    if (this.$el.is(VALUEFIELD)) {
                        this.$el.val(_momentText);
                    }
                    this.toggleResult();
                    this.resetStartDate();
                    options = options || {};
                    if (!options.stay) {
                        this.closePopup();
                    }
                    if (!options.silent) {
                        this.triggerHandler("changed", args);
                    }
                }
            } else {
                this.renderView();
            }
        },
        toggleResult: function() {
            if (!this.$container) {
                return;
            }
            var data = this.formatSelectedDateTime();
            this.$role("startdate-val").val(data.start.date);
            this.$role("enddate-val").val(data.end.date);
            this.restrainTimepicker();
        },
        /*
         * 用户操作
         */
        //选择日期
        _selectDate: function(date) {
            if (date) {
                if (!this.__temp) {
                    this.__temp = util.momentDate(date);
                    this.renderView();
                    return;
                }
                date = util.momentRange(this.__temp, date);
            }
            this.__temp = null;
            this.setMomentByDate(date);
        },
        /*
         * timepicker
         */
        showTimepicker: function(options) {
            var _this = this;
            if (!this.option("timepicker")) {
                return;
            }
            var defaults = {
                format: this.option("timeFormat"),
                showSecond: this.option("showSecond")
            };
            options = $.extend({}, defaults, options);
            this.$role("starttime").timepicker($.extend({
                onChanged: function(e, time, timeObj) {
                    _this.setMomentByTime({
                        start: timeObj
                    });
                }
            }, options));
            this.$role("endtime").timepicker($.extend({
                onChanged: function(e, time, timeObj) {
                    _this.setMomentByTime({
                        end: timeObj
                    });
                }
            }, options));
        },
        restrainTimepicker: function() {
            if (!this.option("timepicker")) {
                return;
            }
            var range = this.moment();
            var min = this.min();
            var max = this.max();
            if (range) {
                if (util.isDateSame(range.start, min)) {
                    this.setMinTimeByRole("starttime", util.timeObj(min));
                }
                if (util.isDateSame(range.end, max)) {
                    this.setMaxTimeByRole("endtime", util.timeObj(max));
                }
                if (util.isDateSame(range.start, range.end)) {
                    this.setMaxTimeByRole("starttime", util.timeObj(range.start));
                    this.setMinTimeByRole("endtime", util.timeObj(range.end));
                }
            } else {
                this.setMinTimeByRole("starttime", null);
                this.setMaxTimeByRole("starttime", null);
                this.setMinTimeByRole("endtime", null);
                this.setMaxTimeByRole("endtime", null);
            }
        }
    });
    var old = $.fn[PLUGIN_NAME];
    var allow = [ "defaults", "setDefaults", "util" ];
    $.fn[PLUGIN_NAME] = function(options) {
        var args = Array.prototype.slice.call(arguments, 1);
        var isMethodCall = typeof options === "string";
        var returnValue = this;
        this.each(function() {
            var instance = $(this).data(PLUGIN_NS);
            if (isMethodCall) {
                var methodValue;
                if (!instance || !$.isFunction(instance[options]) || options.charAt(0) === "_") {
                    return false;
                }
                methodValue = instance[options].apply(instance, args);
                if ((options === "instance" || methodValue !== instance) && methodValue !== undefined) {
                    returnValue = methodValue && typeof methodValue === "object" && methodValue.jquery ? returnValue.pushStack(methodValue.get()) : methodValue;
                    return false;
                }
            }
            if (!instance) {
                options = options || {};
                if (_.result(options, "rangeSelect")) {
                    instance = new Daterangepicker(this, options);
                } else {
                    instance = new Datepicker(this, options);
                }
                $(this).data(PLUGIN_NS, instance);
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Datepicker;
    $.fn[PLUGIN_NAME].noConflict = function() {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $.fn[PLUGIN_NAME][this] = Datepicker[this];
    });
    return Datepicker;
});

define("src/plugin/datepicker/js/timepicker", [ "underscore", "lib/moment", "src/plugin/datepicker/js/timepicker.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    if (!moment) var moment = require("lib/moment");
    var defaults = require("src/plugin/datepicker/js/timepicker.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "timepicker";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    var KEYCODE = {
        TAB: 9,
        ENTER: 13,
        ESC: 27,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40
    };
    var VALUEFIELD = "input,textarea,button";
    // 定义插件类
    function Timepicker(element, options) {
        this.el = element;
        this.$el = $(element);
        this.defaults = Timepicker.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 定义默认选项
    Timepicker.defaults = defaults;
    // 设置默认选项
    Timepicker.setDefaults = function(options) {
        $.extend(Timepicker.defaults, options);
    };
    // 定义插件类
    function Timepicker(element, options) {
        this.el = element;
        this.$el = $(element);
        this.defaults = Timepicker.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 扩展插件原型
    $.extend(Timepicker.prototype, {
        _init: function() {
            var val;
            if (this.__isValueField = this.$el.is(VALUEFIELD)) {
                val = this.$el.val();
            }
            //设置当前焦点（'hour'|'minute'|'second'）
            this.__focus = this.option("focus") || "hour";
            //设置最小值（单位：秒）
            this.setMin(this.option("min"));
            //设置最大值（单位：秒）
            this.setMax(this.option("max"));
            //设置当前时间（单位：秒）
            this.__time = this.val2Seconds(val || this.option("time"));
            this.render();
            //销毁初始化方法
            delete this._init;
            return this;
        },
        //取上一个焦点
        prev: function(type) {
            var data = {
                hour: null,
                minute: "hour",
                second: "minute"
            };
            return data[type];
        },
        //取下一个焦点
        next: function(type) {
            var data = {
                hour: "minute",
                minute: "second",
                second: null
            };
            return data[type];
        },
        //渲染
        render: function() {
            this.$container = $(this.option("tpl", [ {
                data: this.getTime()
            } ]));
            if (this.__isValueField) {
                this.$el.hide().before(this.$container);
            } else {
                this.$el.empty().append(this.$container);
            }
            this._addEvents();
        },
        //添加事件
        _addEvents: function() {
            if (!this.$container) {
                return;
            }
            this.$container.on("keydown." + PLUGIN_NS, $.proxy(this._keydownHandler, this)).on("mousewheel wheel", $.proxy(this._mousewheelHandler, this)).on("focus." + PLUGIN_NS, "input", $.proxy(this.setFocusOnEvent, this)).on("blur." + PLUGIN_NS, "input", $.proxy(this.setTimeOnEvent, this)).on("click." + PLUGIN_NS, '[role="add"]', $.proxy(this.add, this)).on("click." + PLUGIN_NS, '[role="subtract"]', $.proxy(this.subtract, this));
        },
        //值转秒
        val2Seconds: function(val) {
            val = val || 0;
            if (typeof val === "number") {
                //换算成毫秒
                val *= 1e3;
            }
            return moment.duration(val).as("s");
        },
        //格式化
        format: function(val) {
            return moment(val).format(this.option("format"));
        },
        //增加
        add: function() {
            var limit = this.getLimit();
            var step = this.getStep();
            var val = this.getVal() + step;
            if (val > limit.max) {
                val = limit.min;
            }
            this.setTimeByFocus(val);
        },
        //减少
        subtract: function() {
            var limit = this.getLimit();
            var step = this.getStep();
            var val = this.getVal() - step;
            if (val < limit.min) {
                val = limit.max;
            }
            this.setTimeByFocus(val);
        },
        //判断值是否合法
        isValid: function(type, val) {
            val = parseInt(val, 10);
            if (isNaN(val)) {
                return false;
            }
            var limit = this.getLimit(type);
            if (val < limit.min || val > limit.max) {
                return false;
            }
            return true;
        },
        //获取值
        getVal: function(type) {
            type = type || this.__focus;
            return parseInt(this.$role(type).val(), 10);
        },
        //根据秒获取时间对象
        getTimeBySecond: function(second) {
            second = parseInt(second, 10) || 0;
            var duration = moment.duration(second * 1e3);
            return {
                hour: duration.get("hour"),
                minute: duration.get("minute"),
                second: duration.get("second")
            };
        },
        //获取时间对象
        getTime: function() {
            return this.getTimeBySecond(this.__time);
        },
        //获取步长
        getStep: function(type) {
            var step;
            type = type || this.__focus;
            switch (type) {
              case "minute":
                step = this.option("minuteStep");
                break;

              case "second":
                step = this.option("secondStep");
                break;

              default:
                step = this.option("hourStep");
                break;
            }
            return parseInt(step, 10) || 1;
        },
        //获取限制
        getLimit: function(type) {
            type = type || this.__focus;
            return {
                min: moment.duration(this.__min * 1e3).get(type),
                max: moment.duration(this.__max * 1e3).get(type)
            };
        },
        //设置最小值
        setMin: function(val) {
            this.__min = this.val2Seconds(val || "00:00:00");
        },
        //设置最大值
        setMax: function(val) {
            this.__max = this.val2Seconds(val || "23:59:59");
        },
        //设置时间
        setTime: function(val, options) {
            var old = this.getTime();
            var time = val;
            options = options || {};
            if (options.type) {
                var type = options.type;
                val = parseInt(val, 10);
                if (!this.isValid(type, val)) {
                    return false;
                }
                time = $.extend({}, old);
                time[type] = val;
                this.$role(type).val(this.zeroFix(val));
            }
            time = this.val2Seconds(time);
            if (time !== this.__time) {
                var timeObj = this.getTimeBySecond(time);
                var text = this.format(timeObj);
                this.__time = time;
                //同步输入框
                this.$role("hour").val(this.zeroFix(timeObj.hour));
                this.$role("minute").val(this.zeroFix(timeObj.minute));
                this.$role("second").val(this.zeroFix(timeObj.second));
                if (this.__isValueField) {
                    this.$el.val(text);
                }
                if (!options.silent) {
                    this.triggerHandler("changed", [ text, timeObj, old ]);
                }
            }
        },
        setTimeByFocus: function(val) {
            this.setTime(val, {
                type: this.__focus
            });
        },
        setTimeOnEvent: function(e) {
            var $el = $(e.currentTarget);
            var val = $el.val();
            var type = $el.attr("role");
            this.setTime(val, {
                type: type
            });
        },
        //设置当前焦点
        setFocus: function(type) {
            if (this.__focus !== type) {
                this.__focus = type;
                this.highlight();
            }
        },
        setFocusOnEvent: function(e) {
            this.setFocus($(e.currentTarget).attr("role"));
        },
        //处理键盘事件
        _keydownHandler: function(e) {
            var type = this.__focus;
            var $prev = this.$role(this.prev(type));
            var $next = this.$role(this.next(type));
            switch (e.keyCode) {
              //上一个焦点
                case KEYCODE.LEFT:
                $prev && $prev.focus();
                e.preventDefault();
                break;

              //下一个焦点
                case KEYCODE.RIGHT:
              case KEYCODE.TAB:
                $next && $next.focus();
                e.preventDefault();
                break;

              //微调+
                case KEYCODE.UP:
                this.add();
                e.preventDefault();
                break;

              //微调-
                case KEYCODE.DOWN:
                this.subtract();
                e.preventDefault();
                break;
            }
        },
        //处理滚轮事件
        _mousewheelHandler: function(e) {
            e.preventDefault();
            var data = e.originalEvent.wheelDelta;
            //chrome ie
            var deltaY = e.originalEvent.deltaY;
            //ff
            if (!data && deltaY) {
                data = -1 * deltaY;
            }
            if (data > 0) {
                this.add();
                return;
            }
            this.subtract();
        },
        //高亮
        highlight: function() {
            this.$role(this.__focus).select();
        },
        //补0
        zeroFix: function(val) {
            var n = parseInt(val, 10) || 0;
            return n < 10 ? "0" + n : n;
        },
        /*
         * dom相关
         */
        $: function(selector) {
            return this.$container.find(selector);
        },
        //获取指定role属性的dom元素
        $role: function(role) {
            return this.$container.find('[role="' + role + '"]');
        },
        /*
         * 事件接口
         */
        //将配置中的接口绑定到事件
        bindAll: function() {
            var _this = this;
            $.each(this.options, function(key, value) {
                if (/^on[A-Z]/.test(key) && $.isFunction(value)) {
                    var ev = _.interface2Event(key);
                    _this.$el.on(ev + "." + PLUGIN_NS, $.proxy(value, _this));
                }
            });
        },
        //触发事件回调
        triggerHandler: function(ev, args) {
            var event = $.Event(ev + "." + PLUGIN_NS);
            this.$el.trigger(event, args || []);
            return !event.isDefaultPrevented();
        },
        /*
         * 配置相关
         */
        //解析配置：若配置项为方法，则返回方法执行结果（args为方法参数数组）
        option: function(name, args) {
            return _.resultWith(this.options, name, args, this);
        },
        //设置配置
        setOptions: function(name, val) {
            var options = {};
            if (typeof name == "string") {
                val != undefined && (options[name] = val);
            } else {
                options = name;
            }
            $.extend(this.options, options);
            return this;
        },
        //获取插件实例
        instance: function() {
            return this;
        }
    });
    var old = $.fn[PLUGIN_NAME];
    var allow = [ "defaults", "setDefaults" ];
    $.fn[PLUGIN_NAME] = function(options) {
        var args = Array.prototype.slice.call(arguments, 1);
        var isMethodCall = typeof options === "string";
        var returnValue = this;
        this.each(function() {
            var instance = $(this).data(PLUGIN_NS);
            if (isMethodCall) {
                var methodValue;
                if (!instance || !$.isFunction(instance[options]) || options.charAt(0) === "_") {
                    return false;
                }
                methodValue = instance[options].apply(instance, args);
                if ((options === "instance" || methodValue !== instance) && methodValue !== undefined) {
                    returnValue = methodValue && typeof methodValue === "object" && methodValue.jquery ? returnValue.pushStack(methodValue.get()) : methodValue;
                    return false;
                }
            }
            if (!instance) {
                $(this).data(PLUGIN_NS, instance = new Timepicker(this, options));
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Timepicker;
    $.fn[PLUGIN_NAME].noConflict = function() {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $.fn[PLUGIN_NAME][this] = Timepicker[this];
    });
    return Timepicker;
});

define("src/plugin/datepicker/js/timepicker.defaults", [], function(require, exports, module) {
    var container = require("src/plugin/datepicker/tpl/timepicker.container.html");
    return function(_) {
        return {
            //模板
            tpl: _.template(container),
            //格式
            format: function() {
                return this.option("showSecond") ? "HH:mm:ss" : "HH:mm";
            },
            //当前焦点
            focus: "hour",
            //当前时间
            time: "00:00:00",
            //最小值
            min: "00:00:00",
            //最大值
            max: "23:59:59",
            //步长
            hourStep: 1,
            minuteStep: 1,
            secondStep: 1,
            //显示秒
            showSecond: false,
            //时间改变时触发
            onChanged: null
        };
    };
});

define("src/plugin/datepicker/tpl/timepicker.container.html", [], '<div class="spinner spinner-timepicker">\n    <div class="spinner-label">\n        <input role="hour" class="spinner-input" type="text" value="<%=this.zeroFix(data.hour||0)%>" style="width: 15px" maxlength="2"/>\n        <span>：</span><input role="minute" class="spinner-input" type="text" value="<%=this.zeroFix(data.minute||0)%>" style="width: 15px" maxlength="2"/>\n        <%if(this.option(\'showSecond\')){%>\n        <span>：</span><input role="second" class="spinner-input" type="text" value="<%=this.zeroFix(data.second||0)%>" style="width: 15px" maxlength="2"/>\n        <%}%>\n    </div>\n    <div class="spinner-btn">\n        <a role="add" class="spinner-prev" href="javascript:;">\n            <i class="icon"></i>\n        </a>\n        <a role="subtract" class="spinner-next" href="javascript:;">\n            <i class="icon"></i>\n        </a>\n    </div>\n</div>');
