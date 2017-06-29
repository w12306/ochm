ST.Calender = $.createClass($.Widget.Control,
  function (_base) {
    return {
      dateFormat: "yyyy-MM-dd",//暂不支持此配置，格式为yyyy-MM-dd HH:mm:ss
      startYear: 1960,
      endYear: 0,
      curDay: 1,
      curMonth: 1,
      curYear: 1990,
      curHour: 0,
      curMinute: 0,
      curSecond: 0,
      zIndex: 50,
      multiple: false,
      align: 1,
      displayMonth: 1,
      monthWidth: 160,
      minDate: "",
      maxDate: "",
      maxSelectedDay: "",
      islunar: false,
      mode: "hang",
      temp: "common_calender_cnt_temp",
      monthPicker: "ddlist",
      timePicker: false,
      showSeconds: false,
      setup: function () {
        var a = this, m,
          b = $.getBound(a.cid),
          c = "PCAL_" + a.cid;
        if (a.mode == "hang") {
          m = $("<div id='" + c + "'>").css({
            position: "absolute",
            zIndex: a.zIndex,
            display: "none"
          }).appendTo("body");
          ST.JTE.fetch(a.temp).toFill(c, {
            displayMonth: a.displayMonth,
            controlId: a.cid,
            timePicker: a.timePicker,
            showSeconds: a.showSeconds
          });
          a.Jid.attr({
            readOnly: true,
            autoComplete: 'off'
          });
          a.Jid.bind("click", $.Lang.bind(a.show, a));
        } else if (a.mode == "fill") {
          ST.JTE.fetch(a.temp).toFill(a.cid, {
            displayMonth: a.displayMonth,
            controlId: a.cid,
            timePicker: a.timePicker,
            showSeconds: a.showSeconds
          });
        }
        a.$panel = $("#PCAL_PANEL_" + a.cid);
        a._setupYm();
        $("#PCAL_PM_" + a.cid).bind("mousedown", $.Lang.bind(a._turnM, a, "p"));
        $("#PCAL_NM_" + a.cid).bind("mousedown", $.Lang.bind(a._turnM, a, "n"));
        $("#PCAL_DAYS_" + a.cid).bind("click",
          function (e) {
            a.fill(e)
          });
        if ($("#" + a.cid + "_picker").length > 0) {
          $("#" + a.cid + "_picker").bind("click", $.Lang.bind(a.show, a));
        }
        $("#PCAL_" + a.cid).bind("mousedown", function(e){
          $.stopEvent(e);
          $(this).find('input').trigger('blur');
        });
        a.$todayBtn = $("#PCAL_TODAY_" + a.cid).bind('click', function () {//添加“今天”按钮事件
          if (a.today < a.minDate || a.today > a.maxDate) return;
          a._edl && setTimeout(a._edl, 5);
          a._selectDate(a.today);
          a.setTime();
        });
        a.$clearBtn = $("#PCAL_CLEAR_" + a.cid).bind('click', function () {//添加“清空”按钮事件
          var cdate = a.today;
          a._edl && setTimeout(a._edl, 5);
          cdate = cdate < a.minDate ? a.minDate : cdate;
          cdate = cdate > a.maxDate ? a.maxDate : cdate;
          a._selectDate(cdate);
          a.Jid.val('');
        });
        a.$sureBtn = $("#PCAL_SURE_" + a.cid).bind('click', function () {//添加“确定”按钮事件
          a._edl && setTimeout(a._edl, 5);
          a.setTime();
        });
        if (!a._minDate) {
          a._minDate = a.minDate;
          a._maxDate = a.maxDate;
          var dd = new Date().getTime(), tmp;
          if ($.Lang.isNumber(a.minDate)) {
            dd = new Date(dd - Math.abs(a.minDate) * 86400000);
            tmp = a.getDateStr(dd);
            a.minDate = a._minDate = tmp.join("-");
          }
          if ($.Lang.isNumber(a.maxDate)) {
            dd = new Date(new Date().getTime() + Math.abs(a.maxDate) * 86400000);
            tmp = a.getDateStr(dd);
            a.maxDate = a._maxDate = tmp.join("-");
          }
        }
        a.show();
        delete a.setup;
      },
      goToday: function () {
        var t = this,
          today = t.today;
        if (today) {
          today = today.split("-");
          t.showDays(today[0], today[1], today[2]);
        }
      },
      _setupYm: function () {
        var a = this,
          b, c;
        if (a.monthPicker == "ddlist") {
          b = [];
          for (c = a.startYear; c <= a.endYear; c++) b.push({
            text: c,
            value: c
          });
          a._ydl = ST.ddList("PCAL_CY_" + a.cid, b,
            function (d) {
              a.curYear = parseInt(d.value, 10);
              a.showDays()
            },
            5);
          a._ydl.setZIdx(a.zIndex+1);
          b = [];
          for (c = 1; c < 13; c++) b.push({
            text: c,
            value: c
          });
          a._mdl = ST.ddList("PCAL_CM_" + a.cid, b,
            function (d) {
              a.curMonth = parseInt(d.value, 10);
              a.showDays()
            },
            5);
          a._mdl.setZIdx(a.zIndex+1);
        } else if (a.monthPicker == "droplist") {
          a._ym = $("#PCAL_CYM_" + a.cid).attr({
            readOnly: true
          }).bind("click",
            function () {
              if (!a.YMPikcer) {
                a.YMPikcer = new ST.YMPicker({
                  id: a.cid,
                  year: a.curYear,
                  month: a.curMonth,
                  day: a.curDay
                });
                a.YMPikcer.onSelected = function (arg) {
                  var y = arg.date.split("-");
                  a.curYear = Number(y[0]) || a.curYear;
                  a.curMonth = Number(y[1]) || a.curMonth;
                  a.showDays();
                }
              }
              a.YMPikcer.show(a.curYear, a.curMonth);
            });
        }
      },
      _turnM: function (a) {
        var b = this;
        if (a == "p") {
          b.curMonth -= 1;
          if (b.curMonth == 0) {
            b.curYear -= 1;
            b.curMonth = 12;
            if (b.curYear < b.startYear) b.curYear = b.endYear
          }
        } else {
          b.curMonth += 1;
          if (b.curMonth > 12) {
            b.curYear += 1;
            b.curMonth = 1;
            if (b.curYear > b.endYear) b.curYear = b.startYear
          }
        }
        b._mdl && b._mdl.hide();
        b._ydl && b._ydl.hide();
        b.$timer && window.clearTimeout(b.$timer);
        b.$timer = window.setTimeout(function () {
          b.showDays();
        }, 200);
      },
      init: function (a, b, c, d) {
        var g = this, today, date, dateArr, timeArr;
        g.cid = a;
        g.Jid = $("#" + a);
		if(g.Jid.length==0) return;
        if (c && /[0-9]{4}/.test(c)) g.startYear = c;
        g.endYear = d && /[0-9]{4}/.test(d) ? d : (new Date).getFullYear();
        g._pos = b || 3;
        if (date = g.Jid.val().t(), /^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}( ([0-9]{1,2}:){0,2}([0-9]{1,2}){1})?$/.test(date)) {
          if (date.split(" ").length > 1) {
            timeArr = date.split(" ")[1].split(":");
            dateArr = date.split(" ")[0].split("-");
          } else {
            dateArr = date.split("-");
          }
        } else {
          var dt = new Date();
          dateArr = g.getDateStr(dt);
        }
        today = g.getDateStr(new Date());
        g.curDate = dateArr.join("-");
        g.curYear = dateArr[0];
        g.curMonth = dateArr[1];
        g.curDay = dateArr[2];
        g.minDate = g.startYear + "-01-01";
        g.maxDate = g.endYear + "-12-31";
        g.today = today.join("-");
        if (timeArr && timeArr.length > 0) {
          g.curHour = timeArr[0];
          if (timeArr[1]) g.curMinute = timeArr[1];
          if (timeArr[2]) g.curSecond = timeArr[2];
        }
        g.Jid.one("click", function () {
          if (g.mode == "hang") g.setup();
        });
        return g;
      },
      getDateStr: function (dt) {
        var tmp = [];
        tmp.push(dt.getFullYear());
        tmp.push(dt.getMonth() + 1);
        tmp.push(dt.getDate());
        tmp[0] = tmp[0] + '';
        tmp[1] = (tmp[1] < 10) ? "0" + tmp[1] : tmp[1] + '';
        tmp[2] = (tmp[2] < 10) ? "0" + tmp[2] : tmp[2] + '';
        return tmp;
      },
      getTimeStr: function (dt) {
        var tmp = [];
        tmp.push(dt.getHours());
        tmp.push(dt.getMinutes());
        tmp.push(dt.getSeconds());
        tmp[0] = (tmp[0] < 10) ? "0" + tmp[0] : tmp[0] + '';
        tmp[1] = (tmp[1] < 10) ? "0" + tmp[1] : tmp[1] + '';
        tmp[2] = (tmp[2] < 10) ? "0" + tmp[2] : tmp[2] + '';
        return tmp;
      },
      showDays: function (a, b, c) {
        var d = this;
        a = a || d.curYear;
        b = b || d.curMonth;
        c = c || d.curDay;
        d.curYear = parseInt(a, 10);
        d.curMonth = parseInt(b, 10);
        d.curDay = parseInt(c, 10);
        var days = ST.CalenderCal.getDays(d.curYear, d.curMonth);
        d.curDay = d.curDay > days ? days : d.curDay;
        d.$todayBtn.toggleClass('disabled', d.today < d.minDate || d.today > d.maxDate);
        ST.JTE.fetch("common_calender_days_temp").toFill("PCAL_DAYS_" + d.cid, {
          year: d.curYear,
          month: d.curMonth,
          currentDay: d.curDay,
          today: d.today,
          currentDate: d.curDate,
          displayMonth: d.displayMonth,
          mult: d.multiple,
          mindate: d.minDate,
          maxdate: d.maxDate
        });
        d.setPanel();
      },
      setPanel: function () {
        var t = this;
        if (t.monthPicker == "ddlist") {
          t._ydl && t._ydl.setText(t.curYear);
          t._mdl && t._mdl.setText(t.curMonth);
        } else if (t.monthPicker == "droplist") {
          t._ym.val(t.curYear + "年" + t.curMonth + "月");
        }
        if (!t.$panel.length) return;
        ST.JTE.fetch("common_calender_panel_temp").toFill("PCAL_PANEL_" + t.cid, {
          year: t.curYear,
          month: t.curMonth,
          day: t.curDay
        });
      },
      hide: function (a) {
        a = this;
        a.isShown = false;
        $("#PCAL_" + a.cid).hide();
        if (a.monthPicker == "ddlist") {
          a._mdl && a._mdl.hide();
          a._ydl && a._ydl.hide();
        } else if (a.monthPicker == "droplist") {
        }
        $(document).unbind("mousedown.calender", a._edl);
        delete a._edl
      },
      show: function () {
        if (this.isShown) {
          this.hide();
        } else {
          var a = this;
          a.isShown = true;
          if (!a._edl) {
            a._edl = $.Lang.bind(a.hide, a);
            $(document).bind("mousedown.calender", a._edl);
          }
          var date;
          if (a.multiple) {
            date = $.Lang.toArray(a.Jid.val(), "至");
            a.multiple = [date[0]||"", date[1]||""];
            date = $.Lang.toArray(date[0], "-");
          } else {
            date = $.Lang.toArray(a.Jid.val(), "-");
          }
          a.showDays(Math.max(parseInt(date[0] || a.curYear, 10), a.startYear), parseInt(date[1] || a.curMonth, 10), parseInt(date[2] || a.curDay, 10));
          var b = $("#PCAL_" + a.cid),
            pos = ST._posCalculate(a.Jid, b, a._pos, a.align);
          b.css({
            left: pos.x,
            top: pos.y
          }).show();
          if (a.timePicker) {
            if (!ST.TimePicker) alert('使用时间选择功能，请先引入' + ST.PATH.JS + 'ST.TimePicker.js');
            if (!a.$timePicker) {
              var tpid = 'PCAL_TIME_' + a.cid;
              a.$timePicker = new ST.TimePicker(tpid, {
                defaultTime: [a.curHour, a.curMinute, a.curSecond],
                timeFormat: a.showSeconds ? 'HH:mm:ss' : 'HH:mm',
                showSeconds: a.showSeconds,
                className:'timepicker'
              });
            }
          }
        }
      },
      fill: function (e) {
        var t = this, a;
        a = e.target;
        if (a = $(a).attr("date")) {
          var dd = a.split("-");
          t.curYear = parseInt(dd[0], 10);
          t.curMonth = parseInt(dd[1], 10);
          t.curDay = parseInt(dd[2], 10);
          var c = {};
          t.curDate = c.date = a;
          if (!t.multiple) {
            if (t.timePicker) {
              t.showDays();
              return;
            }
            t.onselected && t.onselected(c);
            t.Jid.val(c.date);
            if (!c.cancle) {
              t.hide();
            } else {
              t.showDays();
            }
            t._edl && t._edl();
          } else {
            if (!$.Lang.isArray(t.multiple) || t.multiple.length == 2) t.multiple = [];
            t.multiple.push(c.date);
            if (t.multiple.length == 2) {
              t.multiple = t.multiple.sort(function (x, y) {
                return x > y
              });
              t.onselected && t.onselected(c);
              t.Jid.val(t.multiple[0] + "至" + t.multiple[1]);
              if (!c.cancle) {
                t.hide();
              } else {
                t.showDays();
              }
            } else {
              if (t.maxSelectedDay) {
                if ($.Lang.isString(t.maxSelectedDay)) {
                  t.maxSelectedDay = t.maxSelectedDay.split("-");
                } else if ($.Lang.isNumber(t.maxSelectedDay)) {
                  var num = t.maxSelectedDay;
                  t.maxSelectedDay = new Array();
                  t.maxSelectedDay.push(num);
                  t.maxSelectedDay.push(num);
                } else {
                  t.showDays();
                  return;
                }
                var __tmp = new Date(dd[0], dd[1] - 1, dd[2] - Math.abs(t.maxSelectedDay[0]));
                t.minDate = __tmp.getFullYear() + "-" + (__tmp.getMonth() + 1 < 10 ? "0" + (__tmp.getMonth() + 1) : __tmp.getMonth() + 1) + "-" + (__tmp.getDate() < 10 ? "0" + __tmp.getDate() : __tmp.getDate());
                t.minDate = (t.minDate < t._minDate) ? t._minDate : t.minDate;
                if (t.maxSelectedDay[1]) {
                  __tmp = new Date(dd[0], dd[1] - 1, parseInt(dd[2], 10) + Math.abs(t.maxSelectedDay[1]));
                  t.maxDate = __tmp.getFullYear() + "-" + (__tmp.getMonth() + 1 < 10 ? "0" + (__tmp.getMonth() + 1) : __tmp.getMonth() + 1) + "-" + (__tmp.getDate() < 10 ? "0" + __tmp.getDate() : __tmp.getDate());
                  t.maxDate = (t.maxDate > t._maxDate) ? t._maxDate : t.maxDate;
                }
                t.showDays();
              } else {
                $("#PCAL_DAYS_" + t.cid).find(".active").removeClass("active");
                e.target.parentNode.className = "active";
              }
            }
          }
        }
      },
      //选择日期
      _selectDate: function (date) {
        var t = this, y, m, d, dd, tmp;
        dd = ($.Lang.Browser.isIE && (tmp = date.toString().split("-")).length > 1) ? new Date(tmp[0], tmp[1] - 1, tmp[2]) : new Date(date);
        if (dd.toString() != "Invalid Date") {
          y = dd.getFullYear();
          m = dd.getMonth() + 1;
          d = dd.getDate();
          t.curYear = y;
          t.curMonth = m;
          t.curDay = d;
          var ddd = y + "-" + (m < 10 ? '0' + m : m) + "-" + (d < 10 ? '0' + d : d);
          t.curDate = ddd;
          t.Jid.val(ddd);
        }
      },
      //更换日期的接口
      changeDate: function (date) {
        t._selectDate(date);
        var c = {date: ddd};
        t.onselected && t.onselected(c);
      },
      //设置时间:用于按钮设置时间
      setTime: function () {
        var t = this, d = t.curDate;
        if (t.timePicker && t.$timePicker) {
          var time, timeString;
          time = t.$timePicker.getTime();
          t.curHour = time['H'];
          t.curMinute = time['M'];
          t.curSecond = time['S'];
          timeString = $('#PCAL_TIME_' + t.cid).val();
          d += ' ' + timeString;
        }
        t.Jid.val(d);
        var c = {date: d};
        t.onselected && t.onselected(c);
      },
      onselected: ""
    }
  });

ST.CalenderCal = {
  Month: ['', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十', "十一", "十二"],
  getWeek: function (year, month, day) {
    var dd, ds, obj = {}, y, m, d;
    dd = new Date(year, parseInt(month, 10) - 1, parseInt(day, 10));//当前日期对象
    ds = dd.getDay();               //当前星期几
    //算出星期一的日期
    dd = new Date(dd.getTime() - ds * 86400000);//当前日期周一所对应对象
    for (var i = 0; i <= 6; i++) {
      y = dd.getFullYear();
      m = dd.getMonth() + 1;
      d = dd.getDate();
      obj[i] = y + "-" + (m < 10 ? '0' + m : m) + "-" + (d < 10 ? '0' + d : d);
      dd = new Date(dd.getTime() + 86400000); //获取上一天日期对象
    }
    return obj;
  },
  getDays: function (year, month, dismonth) {
    month = parseInt(month, 10);
    if (dismonth) {
      if (-12 < dismonth && dismonth > 12) {
        dismonth = 0;
      }
      month += dismonth;
      if (month > 12) {
        year++;
        month -= 12
      }
      ;
      if (month < 1) {
        year--;
        month += 12
      }
      ;
    }
    var days = 30;
    if (month == 2) {
      if ((year % 4 == 0 && year % 100 != 0) || (year % 400 == 0)) {
        days = 29;
      } else {
        days = 28;
      }
    } else {
      if ((month <= 7 && month % 2 == 1) || (month >= 8 && month % 2 == 0)) {
        days = 31;
      }
    }
    return days;
  },
  getMonthStr: function (month) {
    return this.Month[month] + "月";
  },
  getIntYears: function (year, fix) {
    if (!fix) fix = 1;
    var a, b = fix, c;
    year = year.toString();
    while (fix > 0) {
      c = Number(year.charAt(year.length - fix));
      a = (fix > 1 ? (c == 0 ? 1 : c) : c) * Math.pow(10, fix - 1);
      year = year - a;
      year = year.toString();
      fix--;
    }
    year = year - 1;
    return year;
  }
};