/*
 正则表达式
 * */
define(function(require) {

    
    var regs= {
        //用于beeui验证插件
        Validator:{
            //任意字符
            any: /([\s\S]*)/,
            //必填
            required: /^([\s\S]+)$/,
            //汉字
            chinese: /^[\u4E00-\u9FAF]+$/,
            //字母
            letter: /^[a-zA-Z]+$/,
            //数字
            number: /^[0-9]+$/,
            //整数
            integer: /^-?(0|[1-9][0-9]*)$/,
            //小数
            decimal: /^-?[0-9]+\.[0-9]+$/,
            //邮箱
            email: /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/,
            //用户名：4-20个字符，支持字母、数字、下划线，首位为字母
            username:/^[a-zA-Z][a-zA-Z0-9_]{3,19}$/i,
            //密码：6-16个字符，支持字母、数字、下划线，区分大小写
            password:/^[a-zA-Z0-9_]{6,16}$/,
            //ip地址
            ip:/^((?:(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d)))\.){3}(?:25[0-5]|2[0-4]\d|((1\d{2})|([1-9]?\d))))$/,
            //电话号码
            tel: /^((\+?86)|(\(\+86\)))?\d{3,4}-\d{7,8}(-\d{3,4})?$/,
            //手机号码
            mobile:/^((\+?86)|(\(\+86\)))?1\d{10}$/,
            //qq(5-11位)
            qq:/^[1-9]\d{4,10}$/
        },
        //用于ST.Verify
        Regs : {
            common: {
                reg: /^\w+$/,
                desc: "字母,数字或下划线！"
            },
            fangle: {
                reg: /[\uFF00-\uFFFF]/,
                desc: "全角字符!"
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
                reg: /^(\d{1,4}\-)?(13|15|18){1}\d{9}$/,
                desc: "手机号码！"
            },
            symbol: {
                reg: /[`~!@#$%^&*()+=|{}':;',.<>/?~！@#￥%……&*（）——+|{}【】'；：""'。，、？]/,
                desc: "特殊字符！"
            },
            password: {
                reg: /^\w+$/,
                desc: "字母和数字或下划线"
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

                        var d = key.length - key.indexOf('.') - 1;
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
    };

    //兼容ST
    ST.Regs = regs.Regs;
    return regs;

});