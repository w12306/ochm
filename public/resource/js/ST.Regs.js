/*
 正则列表, 每个网站验证规则不一样,
 正则列表需要根据网站独立的去配置.
 */
ST.Regs = {
  common: {
    reg: /^\w+$/,
    desc: "字母,数字或下划线！"
  },
  fangle: {
    reg: /[\uFF00-\uFFFF]/,
    desc: "全角字符!"
  },
  passport:{
    reg: /^[a-zA-Z](\w){3,19}$/,
    desc:"4~20字 可使用字母,数字,下划线，首位为字母"
  },
  version:{
    reg: /^\d+.\d+.\d+.\d+$/,
    desc:"请按照X.X.X.X的格式输入"
  },
  password:{
    reg:/^(\S){6,32}$/,
    desc:"6~32字,可使用字母数字组合(区分大小写)"
  },
  vcode: {
    reg: /^\d{4}$/,
    desc: "4位数字！"
  },
  email: {
    reg: /^\w[\w\.-]*@[\w-]+(\.[\w-]+)+$/,
    desc: "邮箱格式！"
  },
  idcard: {
    reg: /^(\d{15}|\d{17}[\dx])$/,
    desc: "15或18位身份证号码！"
  },

  chinese: {
    reg: /^[\u4E00-\u9FAF]+$/,
    desc: "中文！"
  },
  truename: {
    reg: /^[\u4E00-\u9FAF]{2,4}$/,
    desc: "2-4个中文！"
  },
  english: {
    reg: /^[A-Za-z]+$/,
    desc: "英文！"
  },
  date: {
    reg: /^\d{4}-\d{2}-\d{2}$/i,
    desc: "公历日期(2013-07-06)！"
  },
  url: {
    //reg: /^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=#]*)?$/i,
    reg:/^http(s)?:\/\//i,
    desc: "URL！"
  },
  qq: {
    reg: /^[1-9]\d{4,10}$/,
    desc: "5-11位QQ号！"
  },
  phone: {
    reg: /^((((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?)|(\d{11}))$/,
    desc: "电话或手机号码！"
  },
  mobile: {
    reg: /^(\d{1,4}\-)?(13[0-9]|14[5|7]|15[0-9]|17[0|6|7|8]|18[0-9]){1}\d{8}$/,
    desc: "请输入有效手机号码！"
  },
  symbol: {
    reg: /[`~!@#$%^&*()+=|{}':;',.<>/?~！@#￥%……&*（）——+|{}【】'；：""'。，、？]/,
    desc: "特殊字符！"
  },
  ip: {
    reg: /^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/,
    desc: "IP地址"
  },
  mac: {
    reg: /[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}:[A-F\d]{2}/i,
    desc: "MAC地址"
  },

  number: {
    reg: /^\d+$/,
    desc: "数字"
  },
  unnumber: {
    reg: /\D/,
    desc: "不为数字"
  },
  sinteger: {
      reg: /^([1-9]\d*)$/,
      desc: "正整数！"
  },
  integer: {
    reg: /^-?(0|[1-9]\d*)$/,
    desc: "整数！"
  },
  decimal: {
    reg: /^-?[0-9]+\.[0-9]+$/,
    desc: "小数"
  },
  int: {
    reg: {
      test: function (key, o) {
        var t = ST.Regs.int;
        if (!ST.Regs.integer.reg.test(key)) {
          t.desc = "整数";
          return false;
        }
        var num = parseInt(key, 10), sign = o.attr("sign");
        if (sign == '+') {
          t.desc = "正整数";
          return num > 0;
        }
        if (sign == '-') {
          t.desc = "负整数";
          return num < 0;
        }
        return true;
      }
    },
    desc: "整数"
  },
  dec: {
    reg: {
      test: function (key, o) {
        var t = ST.Regs.dec;
        //小数（digits属性为小数位数，例：1-3或2）
        if (!ST.Regs.decimal.reg.test(key)) {
          t.desc = '小数';
          return false;
        }
        var digits = o.attr("digits").split("-"),
          len = digits.length;
        if (!len) return true;

        var d = key.length - key.charAt('.') - 1;
        if (len > 1) {
          if (d < digits[0] || d > digits[1]) {
            t.desc = '保留' + digits[0] + '-' + digits[1] + '位小数';
            return false;
          }
        } else {
          if (d != digits[0]) {
            t.desc = '保留' + digits[0] + '位小数';
            return false;
          }
        }
        var num = parseFloat(key), sign = o.attr("sign");
        if (sign == '+') {
          t.desc = '正小数';
          return num > 0;
        }
        if (sign == '-') {
          t.desc = '负小数';
          return num < 0;
        }
        return true;
      }
    },
    desc: "小数"
  },
  //最小值
  min: {
    reg: {
      test: function (key, o) {
        var t = ST.Regs.min;
        if (!(ST.Regs.integer.reg.test(key) || ST.Regs.decimal.reg.test(key))) {
          t.desc = ST.LRes.FormErrorNumber;
          return false;
        }
        var min = o.attr("min");
        if (!min) return true;
        if (parseFloat(key) < parseFloat(min)) {
          t.desc = '最小值为' + min;
          return false;
        }
        return true;
      }
    },
    desc: "最小值"
  },
  //最大值
  max: {
    reg: {
      test: function (key, o) {
        var t = ST.Regs.max;
        if (!(ST.Regs.integer.reg.test(key) || ST.Regs.decimal.reg.test(key))) {
          t.desc = ST.LRes.FormErrorNumber;
          return false;
        }
        var max = o.attr("max");
        if (!max) return true;
        if (parseFloat(key) > parseFloat(max)) {
          t.desc = '最大值为' + max;
          return false;
        }
        return true;
      }
    },
    desc: "最大值"
  }

}