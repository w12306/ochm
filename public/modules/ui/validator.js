/*
 * Module Name: validator
 * Module Require: jquery, underscore
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function (require) {
    return require("src/plugin/validator/js/validator");
});

define("src/plugin/validator/js/validator.defaults", ["../tpl/validator.helper.html"], function (require) {
    var helper = require("../tpl/validator.helper.html");
    return function (_) {
        return {
            //模板
            tpl_helper: _.template(helper),
            tpl_formhelper: null,
            //状态样式(tip|success|error|loading)
            stateCls: function (state) {
                return state ? "alert-" + state : "";
            },
            //显示最新一条错误信息
            showLatestError: false,
            //验证成功后提示
            okText: null,
            //是否附加帮助信息
            appendHelper: true,
            //是否在第一个错误后停止验证
            stopOnFalse: false,
            //远程方法（用于远程验证）
            remoteMethod: $.ajax,
            //ajax方法（用于表单提交）
            ajaxMethod: $.ajax,
            //是否使用ajax提交表单
            ajaxSubmit: true,
            //提交表单的ajax配置
            ajaxSubmitOption: {},
            showHelperMethod: function ($helper) {
                return $helper.css("display", "");
            },
            hideHelperMethod: function ($helper) {
                return $helper.css("display", "none");
            },
            //验证前执行的方法（返回false则不执行验证）
            onBefore: null,
            //元素验证成功后执行的方法
            onValid: null,
            //元素验证失败后执行的方法
            onInvalid: null,
            //表单验证成功后
            onFormValid: null,
            //表单验证失败后
            onFormInvalid: null,
            //验证成功后表单提交前（返回false则不执行表单提交）
            onFormSubmit: null
        };
    };
});

define("src/plugin/validator/tpl/validator.helper.html", [], '<div role="v-helper" class="help-inline ml5">\n    <div role="container" class="alert">\n        <i class="alert-icon"></i>\n        <div role="content" class="alert-main"></div>\n    </div>\n</div>');

define("src/plugin/validator/js/validator", ["underscore", "./validator.defaults"], function (require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var defaults = require("./validator.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "validator";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    //存储验证规则配置的名词空间
    var RULECONFIG_NS = "config.rules." + PLUGIN_NS;
    //存储验证规则名称的名词空间
    var RULENAME_NS = "name.rules." + PLUGIN_NS;
    //配置验证规则
    var RULES = "data-rules";
    //配置组验证
    var GROUP = "data-group";
    //验证对象
    var ELEMENTS = "[" + RULES + "]";
    //提示容器
    var HELPER = "data-helper";
    //占位容器
    var HOLDER = "data-holder";
    //忽略验证标识
    var IGNORED = "data-ignored";
    //输入提示
    var TIPMSG = "data-tip";
    //错误提示
    var ERRORMSG = "data-msg";
    //成功提示
    var SUCCESSMSG = "data-ok";
    //载入提示
    var LOADINGMSG = "data-loading";
    //始终验证
    var REQUIREDRULE = ["required", "equal", "nequal", "either", "least", "most"];
    //远程验证规则
    var REMOTE = "remote";
    //规则分隔符
    var SEPARATOR = " ";
    //定义验证规则类
    function Rules(options) {
        this.shortcut = function (args) {
            this.test = _.toJSON(args);
        };
        options._msg = options.msg;
        for (var k in options) {
            this[k] = options[k];
        }
        return this;
    }

    //获取验证规则
    //- 若不存在则根据已有正则新建
    function getRule(name) {
        var rule = Validator.rules[name];
        if (!rule) {
            var reg = Validator.regexp[name];
            if (!reg) {
                return null;
            }
            rule = {
                test: function () {
                    var val = _.trim($(this.el).val());
                    return reg.test(val);
                },
                msg: Validator.lang[name] || Validator.lang.match
            };
            Validator.rules[name] = rule;
        }
        return rule;
    }

    //获取元素验证规则列表
    function getRulesByElement(el) {
        var $el = $(el);
        //验证规则列表
        var names = $el.data(RULENAME_NS);
        //是否包含参数
        var REG_HAS_ARGS = /\(.*\)/;
        if (!names) {
            //第一次从DOM取
            var domRules = _.trim($el.attr(RULES));
            domRules = domRules ? domRules.split(SEPARATOR) : [];
            names = _.map(domRules, function (rule) {
                return rule.replace(REG_HAS_ARGS, "").toLowerCase();
            });
            $el.data(RULENAME_NS, names);
        }
        return names;
    }

    //获取|设置|刷新元素上的验证规则
    //- 规则设置的优先级：默认值 < DOM属性 < 快捷参数 < vals参数
    //- name {string|number} 规则名|规则索引
    //- vals {object|boolean} 规则值（object:新规则，boolean：true-重新获取dom配置）
    function vAttr(name, vals) {
        var $el = $(this);
        //验证规则配置
        var rules = $el.data(RULECONFIG_NS) || {};
        //验证规则列表
        var names = getRulesByElement(this);
        //规则在DOM上的索引
        var index = -1;
        //是否包含参数
        var REG_HAS_ARGS = /\(.*\)/;
        if (typeof name === "number") {
            //从索引取
            index = name;
            if (index < -1 || index > names.length - 1) {
                index = 0;
            }
            name = names[index];
        } else {
            //从名称取
            name = _.trim(name);
            index = _.indexOf(names, name);
            if (index === -1) {
                names.push("name");
                $el.data(RULENAME_NS, names);
            }
        }
        //规则对象，如：{msg:'必填'}
        var rule = rules[name];
        //判断是否刷新规则
        if (vals === true) {
            rule = null;
        }
        if (!rule) {
            //存放规则配置的DOM属性，如： data-rules-required
            var rule_attr = [RULES, name].join("-").replace(/^-|-$/, "");
            //获取DOM属性配置值
            var attrs = _.toJSON($el.attr(rule_attr));
            //获取验证器默认值
            var defaults = getRule(name);
            //配置信息
            var options = $.extend({
                __name: name,
                el: this,
                test: null,
                msg: ""
            }, defaults, attrs);
            //DOM上的验证规则配置
            var domRules = _.trim($el.attr(RULES));
            domRules = domRules ? domRules.split(SEPARATOR) : [];
            //实例化规则
            rule = new Rules(options);
            //使用DOM规则
            if (index < domRules.length) {
                var dom_msg = _.trim($el.attr(ERRORMSG));
                var args = REG_HAS_ARGS.exec(domRules[index]);
                //使用快捷参数设置规则
                dom_msg = dom_msg ? dom_msg.split(SEPARATOR) : [];
                $.extend(rule, {
                    _msg: dom_msg[index],
                    msg: dom_msg[index]
                });
                if ($.isArray(args)) {
                    rule.shortcut(args[0].replace(/^\(/, "").replace(/\)$/, ""));
                }
            }
        }
        //使用vals参数设置规则
        if ($.isPlainObject(vals)) {
            for (var k in vals) {
                rule[k] = vals[k];
            }
        }
        //缓存验证规则
        rules[name] = rule;
        $el.data(RULECONFIG_NS, rules);
        return rule;
    }

    // 定义插件类
    function Validator(element, options) {
        this.el = element;
        this.$el = $(element);
        this.defaults = this.constructor.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }

    // 定义默认选项
    Validator.defaults = defaults;
    // 语言包
    Validator.lang = {
        required: "必填",
        either: "同时填写或同时不填写",
        least: "至少{0}项",
        most: "至多{0}项",
        number: "请输入数字",
        chinese: "请输入汉字",
        letter: "请输入字母",
        email: "请输入正确的邮箱地址",
        integer: ["请输入{0}", {
            "*": "整数",
            "0": "0",
            "+": "正整数",
            "+0": "正整数或0",
            "-": "负整数",
            "-0": "负整数或0"
        }],
        decimal: ["请输入{0},保留{1}位小数", {
            "*": "小数",
            "+": "正小数",
            "-": "负小数"
        }],
        length: "请输入{0}~{1}个字符",
        range: ["请输入数值", "范围{0}{1},{2}{3}"],
        equal: "请输入等于{0}的值",
        nequal: "请输入不等于{0}的值",
        match: "验证失败",
        remote: ["远程验证失败", {
            loading: "正在验证中...",
            ok: ""
        }]
    };
    //工具箱
    Validator.util = {
        match: function (val, match) {
            val = _.trim(val ? val : $(this.el).val());
            match = match || this.match;
            //正则
            var REG_IS_REG = /^\/.+\//;
            //逻辑与
            var REG_IS_AND = /\&/g;
            //逻辑或
            var REG_IS_OR = /\|/g;
            //逻辑非
            var REG_IS_NOT = /^\!/;
            //匹配正则开头
            var REG_START = /^\/\^*/;
            //匹配正则结尾
            var REG_END = /\$*\/.*$/;
            //匹配正则修饰符
            var REG_FLAG = /[ig].+$/;
            //正则过滤
            var filter = function (reg) {
                var flag, new_reg;
                reg = reg.toString();
                flag = reg.match(REG_FLAG);
                reg = reg.replace(REG_START, "").replace(REG_END, "").replace(REG_FLAG, "");
                if (flag) {
                    new_reg = new RegExp(reg, flag);
                } else {
                    new_reg = new RegExp(reg);
                }
                return new_reg;
            };
            //自定义验证
            var test = function (name, is_filter) {
                var is_not = REG_IS_NOT.test(name);
                var reg = Validator.regexp[is_not ? name.replace(REG_IS_NOT, "") : name];
                if ($.type(reg) === "regexp") {
                    if (is_not) {
                        return !filter(reg).test(val);
                    }
                    reg = is_filter ? filter(reg) : reg;
                    return reg.test(val);
                }
                return null;
            };
            var flag = true;
            //自定义正则
            if (REG_IS_REG.test(match)) {
                var t_exp = _.toJSON(match);
                if ($.type(t_exp) === "regexp") {
                    flag = t_exp.test(val);
                }
            } else {
                var regexp = Validator.regexp, reg;
                match = _.trim(match ? match : "any");
                //逻辑或
                if (REG_IS_OR.test(match)) {
                    match = match.split("|");
                    flag = false;
                    for (var i = 0, l = match.length; i < l; i++) {
                        flag = test(match[i]);
                        if (flag === true) {
                            break;
                        }
                    }
                } else if (REG_IS_AND.test(match)) {
                    match = match.split("&");
                    flag = false;
                    for (var i = 0, l = match.length; i < l; i++) {
                        flag = test(match[i], true);
                        if (flag === false) {
                            break;
                        }
                    }
                } else {
                    flag = test(match);
                }
            }
            return flag;
        },
        required: function (el) {
            var $el = $(el);
            if ($el.is(":checkbox,:radio")) {
                return $el.is(":checked");
            } else {
                return Validator.regexp.required.test(_.trim($el.val()));
            }
        }
    };
    // 正则表达式
    Validator.regexp = {
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
        email: /^\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/
    };
    // 验证规则
    Validator.rules = {
        //必填
        required: {
            test: function () {
                return Validator.util.required(this.el);
            },
            msg: Validator.lang.required
        },
        //同时验证
        either: {
            test: function () {
                var $els = $(this.els);
                if ($els.length == 0) return true;
                var $target = $(this.target);
                var count = 0;
                $els.each(function () {
                    if (Validator.util.required(this)) {
                        count++;
                    }
                });
                if ($target.length > 0) {
                    var flag = _.trim($target.val());
                    if (flag) {
                        return count == $els.length;
                    }
                    return count == 0;
                }
                return count == 0 || count == $els.length;
            },
            msg: Validator.lang.either,
            shortcut: function (args) {
                this.target = args;
            }
        },
        //至少验证
        least: {
            test: function () {
                var $els = $(this.els);
                if ($els.length == 0) return true;
                var count = 0;
                $els.each(function () {
                    if (Validator.util.required(this)) {
                        count++;
                    }
                });
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, this.num);
                return count >= this.num;
            },
            num: 1,
            msg: Validator.lang.least,
            //快捷参数，例：
            // 至少2项 => least(2)
            shortcut: function (args) {
                this.num = parseInt(args, 10) || this.num;
            }
        },
        //至多验证
        most: {
            test: function () {
                var $els = $(this.els);
                if ($els.length == 0) return true;
                var count = 0;
                $els.each(function () {
                    if (Validator.util.required(this)) {
                        count++;
                    }
                });
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, this.num);
                return count <= this.num;
            },
            num: 1,
            msg: Validator.lang.most,
            //快捷参数，例：
            // 至多2项 => most(2)
            shortcut: function (args) {
                this.num = parseInt(args, 10) || this.num;
            }
        },
        //整数
        integer: {
            test: function () {
                var val = _.trim($(this.el).val()), type = _.trim(this.type) || "*", lang = Validator.lang.integer[1], text = lang[type], flag;
                flag = Validator.regexp.integer.test(val);
                if (flag) {
                    val = parseInt(val, 10);
                    switch (type) {
                        case "0":
                            flag = val == 0;
                            break;

                        case "+":
                            flag = val > 0;
                            break;

                        case "+0":
                            flag = val >= 0;
                            break;

                        case "-":
                            flag = val < 0;
                            break;

                        case "-0":
                            flag = val <= 0;
                            break;

                        case "*":
                        default:
                            text = lang["*"];
                    }
                }
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, text);
                return flag;
            },
            // 类型：0，正整数，正整数或0，负整数，负整数或0，整数
            type: "*",
            msg: Validator.lang.integer[0],
            // 快捷参数，例：
            // 正整数 => integer(+)
            // 正整数或0 => integer(+0)
            shortcut: function (args) {
                this.type = args;
                return this;
            }
        },
        //小数
        decimal: {
            test: function () {
                var val = _.trim($(this.el).val()), type = _.trim(this.type) || "*", digit = _.trim(this.digit), lang = Validator.lang.decimal[1], text = lang[type], dText = "", flag;
                flag = Validator.regexp.decimal.test(val);
                if (flag) {
                    //判断类型
                    switch (type) {
                        case "+":
                            flag = parseFloat(val) > 0;
                            break;

                        case "-":
                            flag = parseFloat(val) < 0;
                            break;

                        case "*":
                        default:
                            text = lang["*"];
                    }
                    if (flag && !/^\*$/.test(digit)) {
                        //判断小数位数
                        var n = val.split(".")[1].length;
                        flag = n == digit;
                        dText = digit;
                    }
                }
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, text).replace(/\{1\}/g, dText || this.digit);
                return flag;
            },
            //位数
            digit: "*",
            //类型：正小数，负小数，小数
            type: "*",
            msg: Validator.lang.decimal[0],
            //快捷参数，例：
            //正小数，保留2位小数 => decimal(+2)
            shortcut: function (args) {
                var is_type = /[*+-]/;
                this.type = is_type.exec(args);
                this.digit = parseInt(args.replace(is_type, ""), 10) || "*";
            }
        },
        //长度范围（字符）
        length: {
            test: function () {
                var val = _.trim($(this.el).val());
                var minlen = parseInt(this.minlen, 10);
                var maxlen = parseInt(this.maxlen, 10);
                var flag = true;
                if (!isNaN(minlen)) {
                    flag = flag && this.getLength(val) >= minlen;
                }
                if (!isNaN(maxlen)) {
                    flag = flag && this.getLength(val) <= maxlen;
                }
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, !isNaN(minlen) ? minlen : "0").replace(/\{1\}/g, !isNaN(maxlen) ? maxlen : "∞");
                return flag;
            },
            //按字节计算
            useByte: false,
            //用于计算字节的编码
            //- ascii    1英文 = 1字节 1汉字 = 2字节
            //- utf-8    1英文 = 1字节 1汉字 = 3字节
            //- unicode  1英文 = 2字节 1汉字 = 2字节
            encode: "ascii",
            //获取字符串长度
            getLength: function (val) {
                if (this.useByte) {
                    var encode = (this.encode + "").toLowerCase();
                    var REG_EN = /[0-9a-zA-Z]/g;
                    var REG_CHN = /[\u4E00-\u9FA5]/g;
                    switch (encode) {
                        case "utf-8":
                            val = val.replace(REG_CHN, "***");
                            break;

                        case "unicode":
                            val = val.replace(REG_EN, "**");
                            break;

                        case "ascii":
                        default:
                            val = val.replace(REG_CHN, "**");
                            break;
                    }
                    val = val.replace(/[\uFE30-\uFFA0]/g, "**");
                }
                return val.length;
            },
            //长度范围
            minlen: null,
            maxlen: null,
            //错误提示
            msg: Validator.lang.length,
            //快捷参数，例：
            //5及以上个字符 => length(5)
            //5个以内字符 => length(~5)
            shortcut: function (args) {
                var begin_line = /^~/, arr;
                arr = args.split("~");
                if (arr.length < 2) {
                    if (begin_line.test(args)) {
                        this.maxlen = arr[0];
                    } else {
                        this.minlen = arr[0];
                    }
                } else {
                    this.minlen = arr[0];
                    this.maxlen = arr[1];
                }
            }
        },
        //数值范围
        range: {
            test: function () {
                var val = _.trim($(this.el).val());
                var flag = Validator.util.match.call(this);
                //类型验证（默认为整数或小数）
                if (!flag) {
                    this.msg = this._msg[0];
                    return false;
                }
                //范围验证
                var exclude = /^\^/, exclude_min, exclude_max;
                //验证最小值
                if (this.min != null && this.min != undefined) {
                    var min = _.trim(this.min.toString());
                    //判断是否包含最小值
                    exclude_min = exclude.test(min);
                    //数字化
                    min = parseFloat(exclude_min ? min.replace(exclude, "") : min);
                    if (!isNaN(min)) {
                        flag = flag && (exclude_min ? val > min : val >= min);
                    }
                }
                //验证最大值
                if (this.max != null && this.max != undefined) {
                    var max = _.trim(this.max.toString());
                    //判断是否包含最大值
                    exclude_max = exclude.test(max);
                    //数字化
                    max = parseFloat(exclude_max ? max.replace(exclude, "") : max);
                    if (!isNaN(max)) {
                        flag = flag && (exclude_max ? val < max : val <= max);
                    }
                }
                //格式化出错信息
                this.msg = this._msg[1].replace(/\{0\}/g, exclude_min ? "(" : "[").replace(/\{1\}/g, isNaN(min) ? "-∞" : min).replace(/\{2\}/g, isNaN(max) ? "∞" : max).replace(/\{3\}/g, exclude_max ? ")" : "]");
                return flag;
            },
            //匹配正则
            match: "integer|decimal",
            //最小值(^开头表示不包含）
            min: "^0",
            //最大值(^开头表示不包含）
            max: "^0",
            msg: Validator.lang.range,
            //快捷参数，例：
            //(5,10] => range(^5~10)
            //[5,*) => range(5)
            //(*,5) => range(~^5)
            shortcut: function (args) {
                var begin_line = /^~/;
                var arr = args.split("~");
                if (arr.length < 2) {
                    if (begin_line.test(args)) {
                        this.max = arr[0];
                    } else {
                        this.min = arr[0];
                    }
                } else {
                    this.min = arr[0];
                    this.max = arr[1];
                }
            }
        },
        //是否与指定输入框的值相同
        equal: {
            test: function () {
                var el_val = _.trim($(this.el).val());
                var $to = $(this.to);
                var to_val, flag;
                if ($to.length > 0) {
                    to_val = _.trim($to.val());
                } else {
                    return true;
                }
                flag = el_val == to_val;
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, this.to);
                return flag;
            },
            //待比较的对象
            to: null,
            msg: Validator.lang.equal,
            //快捷参数，例：
            //比较对象#id => equal(#id)
            shortcut: function (args) {
                this.to = args;
            }
        },
        //是否与指定输入框的值不同
        nequal: {
            test: function () {
                var el_val = _.trim($(this.el).val());
                var $to = $(this.to);
                var to_val, flag;
                if ($to.length > 0) {
                    to_val = _.trim($to.val());
                } else {
                    return true;
                }
                flag = el_val !== to_val;
                //格式化出错信息
                this.msg = this._msg.replace(/\{0\}/g, this.to);
                return flag;
            },
            //待比较的对象
            to: null,
            msg: Validator.lang.nequal,
            //快捷参数，例：
            //比较对象#id => nequal(#id)
            shortcut: function (args) {
                this.to = args;
            }
        },
        //正则验证
        match: {
            test: function () {
                return Validator.util.match.call(this);
            },
            //匹配正则（支持逻辑'与''非'，及自定义正则）
            match: null,
            msg: Validator.lang.match,
            //快捷参数，例：
            //匹配邮件地址 => match(email)
            //匹配自定义正则 => match(/^abc/)
            shortcut: function (args) {
                this.match = args;
            }
        },
        //远程验证
        remote: {
            url: "",
            paramKey: null,
            params: {},
            type: "get",
            dataType: "json",
            loadingText: Validator.lang.remote[1]["loading"],
            okText: Validator.lang.remote[1]["ok"],
            isValid: function (resp) {
                //格式{status:'',info:'',data:null}
                resp = resp || {};
                return resp.status == "success";
            },
            parseMsg: function (resp) {
                return (resp || {}).info || this.msg;
            },
            msg: Validator.lang.remote[0],
            //快捷参数，例：
            //从www.abc.com验证 => remote(www.abc.com)
            shortcut: function (args) {
                this.url = args;
            }
        }
    };
    // 设置默认选项
    Validator.setDefaults = function (options) {
        $.extend(this.defaults, options);
    };
    // 设置语言包
    Validator.setLang = function (lang) {
        $.extend(this.lang, lang);
    };
    // 设置正则表达式
    Validator.setRegexp = function (exp) {
        $.extend(this.regexp, exp);
    };
    // 设置验证规则
    Validator.setRules = function (rule) {
        $.extend(this.rules, rule);
    };
    // 扩展插件原型
    Validator.prototype = $.extend(Validator.prototype, {
        //初始化
        _init: function () {
            this.initValidation();
            delete this._init;
            return this;
        },
        //初始化验证
        initValidation: function () {
            var fn = $.proxy(this._validateElementOnEvent, this);
            this.$el.on("blur." + PLUGIN_NS, "input,textarea" + ELEMENTS, fn).on("change." + PLUGIN_NS, "select" + ELEMENTS, fn);
            if (this.$el.is("form")) {
                this.$el[0].onsubmit = $.proxy(function () {
                    this.validate(this.submit);
                    return false;
                }, this);
            }
            this.formError = [];
            this.deferred = [];
            return this;
        },
        //提交表单
        submit: function () {
            //判断是否执行表单提交
            var data = this.getFormData();
            if (!this.triggerHandler("submit:form", [data])) {
                return this;
            }
            if (this.option("ajaxSubmit")) {
                this.ajaxSubmit();
            } else {
                this.$el[0].onsubmit = null;
                this.$el.submit();
            }
            return this;
        },
        //使用ajax提交表单
        ajaxSubmit: function (options) {
            var ajaxMethod = this.options.ajaxMethod || $.ajax;
            var $form = this.$el;
            var url = $form.attr("action") || location.href;
            var type = $form.attr("method") || "post";
            var params = this.getFormData();
            var settings = {};
            options = $.extend(this.option("ajaxSubmitOption"), options);
            $.extend(settings, {
                url: url,
                type: type
            }, options);
            settings.data = $.extend(params, settings.data);
            return ajaxMethod(settings);
        },
        //获取表单数据
        getFormData: function () {
            return _.toParam(this.$el.serializeArray());
        },
        //验证
        validate: function (onValid, onInvalid) {
            //判断是否执行验证
            if (!this.triggerHandler("before")) {
                return this;
            }
            var els = this.getElements();
            var validateElement = $.proxy(this.validateElement, this);
            var stopOnFalse = this.option("stopOnFalse");
            //清空变量
            this.clearup();
            //逐个验证
            els.each(function () {
                if (!validateElement(this)) {
                    return !stopOnFalse;
                }
            });
            this.__isclearup = false;
            //处理验证结果
            var def = $.when.apply($, this.deferred);
            def.done($.proxy(function () {
                this.triggerHandler("valid:form");
            }, this)).fail($.proxy(function () {
                this.triggerHandler("invalid:form");
            }, this));
            if ($.isFunction(onValid)) {
                def.done($.proxy(onValid, this));
            }
            if ($.isFunction(onInvalid)) {
                def.fail($.proxy(onInvalid, this));
            }
            return this;
        },
        //清空变量
        clearup: function () {
            for (var i = 0, l = this.deferred.length; i < l; i++) {
                delete this.deferred[i];
            }
            this.formError = [];
            this.deferred = [];
            this.__isclearup = true;
        },
        //验证元素
        _validateElementOnEvent: function (e) {
		    /*解决datapicker时间控件选中前验证问题*/
			var t=this;
            setTimeout(function(){
                t.validateElement(e.currentTarget);
            },100);        
		},
        validateElement: function (el) {
            var $el = $(el), flag = true, msg = "", rule = "";
            //处理非法元素
            if (!this.isTarget(el)) {
                if (this.isIgnored(el)) {
                    this.hideHelper(el);
                }
                return true;
            }
            //获取验证值
            var val = this.getValue(el);
            //获取验证配置
            var rules = getRulesByElement(el);
            //逐项验证
            for (var i = 0, l = rules.length; i < l; i++) {
                //获取验证规则
                var attrs = $el.vAttr(i);
                rule = attrs.__name;
                //跳过被忽略的验证规则
                if (attrs.ignored) {
                    flag = true;
                    continue;
                }
                //无值且非始终验证项，则返回验证成功
                if (!val && _.indexOf(REQUIREDRULE, rule) == -1) {
                    flag = true;
                    break;
                }
                //处理组
                if (this.isGroup(el)) {
                    attrs.els = this.getGroupElements(el);
                }
                if (rule === REMOTE) {
                    //远程验证
                    this.remote(attrs);
                    return false;
                }
                //普通验证
                var test = attrs.test;
                switch ($.type(test)) {
                    case "function":
                        flag = test.apply(attrs);
                        break;

                    case "regexp":
                        flag = test.test(val);
                        break;
                }
                //验证失败跳出
                if (!flag) {
                    msg = attrs.msg;
                    break;
                }
            }
            //添加延迟对象
            var def = $.Deferred();
            $el.data("deferred." + PLUGIN_NS, def);
            this.deferred.push(def);
            //显示帮助信息
            if (flag) {
                this.toggleHelper($el, "success");
                this.triggerHandler("valid", [msg], $el);
                def.resolveWith(this);
            } else {
                this.toggleHelper($el, "error", msg);
                this.triggerHandler("invalid", [msg, rule], $el);
                def.rejectWith(this, [msg]);
            }
            return flag;
        },
        //忽略验证
        ignore: function (el, rules) {
            if (rules) {
                rules = $.makeArray(rules);
                for (var i = 0, l = rules.length; i < l; i++) {
                    $(el).vAttr(rules[i], {
                        ignored: true
                    });
                }
            } else {
                $(el).attr(IGNORED, true);
            }
            this.hideHelper(el);
            return this;
        },
        //激活验证
        activate: function (el, rules) {
            if (rules) {
                rules = $.makeArray(rules);
                for (var i = 0, l = rules.length; i < l; i++) {
                    $(el).vAttr(rules[i], {
                        ignored: false
                    });
                }
            }
            $(el).removeAttr(IGNORED);
            return this;
        },
        //远程验证
        remote: function (options) {
            options = options || Validator.rules.remote;
            var el = options.el, $el = $(el);
            //过滤重复请求
            if ($el.data("isRemoting." + PLUGIN_NS)) {
                return;
            }
            //过滤重复验证
            var val = this.getValue(el);
            var lastVal = $el.data("lastVal." + PLUGIN_NS);
            if (lastVal !== undefined && lastVal === val) {
                if (this.__isclearup) {
                    this.deferred.push($el.data("deferred." + PLUGIN_NS));
                    if ($el.data('lastResult.' + PLUGIN_NS)){
                        //显示帮助信息
                        this.toggleHelper(el, "success", options.okText);
                        //触发回调
                        this.triggerHandler("valid", [], $el);
                    }else{
                        var msg = $el.data('lastMsg.' + PLUGIN_NS);
                        //显示帮助信息
                        this.toggleHelper(el, "error", msg);
                        //触发回调
                        this.triggerHandler("invalid", [msg, REMOTE], $el);
                    }

                }
                return;
            }
            $el.data("lastVal." + PLUGIN_NS, val);
            //定义成功后回调方法
            function successHanlder(resp) {
                var el = options.el, $el = $(el);
                var def = $el.data("deferred." + PLUGIN_NS);
                $el.data("isRemoting." + PLUGIN_NS, false);
                if (options.isValid(resp)) {
                    $el.data("lastResult." + PLUGIN_NS, true);
                    //显示帮助信息
                    this.toggleHelper(el, "success", options.okText);
                    //触发回调
                    this.triggerHandler("valid", [], $el);
                    //处理延迟对象
                    def && def.resolveWith(this);
                } else {
                    var msg = options.parseMsg(resp);
                    $el.data("lastResult." + PLUGIN_NS, false);
                    $el.data("lastMsg." + PLUGIN_NS, msg);
                    //显示帮助信息
                    this.toggleHelper(el, "error", msg);
                    //触发回调
                    this.triggerHandler("invalid", [msg, REMOTE], $el);
                    //处理延迟对象
                    def && def.rejectWith(this, [msg]);
                }
            }

            //定义验证失败后回调方法
            function errorHandler() {
                var el = options.el, $el = $(el);
                var msg = arguments[1];
                //修改状态
                $el.data("isRemoting." + PLUGIN_NS, false);
                $el.data("lastResult." + PLUGIN_NS, false);
                $el.data("lastMsg." + PLUGIN_NS, msg);
                //显示帮助信息
                this.toggleHelper(el, "error", options.parseMsg());
                //触发回调
                this.triggerHandler("invalid", [msg, REMOTE], $el);
                //处理延迟对象
                var def = $el.data("deferred." + PLUGIN_NS);
                def && def.rejectWith(this, [msg]);
            }

            //获取发送参数
            var params = $.extend({}, options.params);
            var key = options.paramKey || $el.attr("name");
            params[key] = val;
            this.toggleHelper(el, "loading", options.loadingText);
            //添加延迟对象
            var def = $.Deferred();
            $el.data("deferred." + PLUGIN_NS, def);
            this.deferred.push(def);
            //发送远程请求
            var remoteMethod = this.options.remoteMethod || $.ajax;
            var xhr = remoteMethod({
                url: options.url || location.href,
                data: params,
                type: options.type || "get",
                dataType: options.dataType || "json",
                success: $.proxy(successHanlder, this),
                error: $.proxy(errorHandler, this)
            });
            return xhr;
        },
        //处理表单出错信息
        _formErrorHandler: function (el, msg) {
            var emsg = (this.formError || []).slice(0);
            var flag = "errorIndex." + PLUGIN_NS;
            var i = _.indexOf(_.pluck(emsg, "i"), $(el).data(flag));
            if (typeof msg === "string") {
                //to update
                if (i !== -1) {
                    emsg[i].msg = msg;
                } else {
                    //to add
                    i = emsg.length;
                    $(el).data(flag, i);
                    emsg.push({
                        i: i,
                        msg: msg
                    });
                }
            } else {
                if (i !== -1) {
                    //to remove
                    emsg.splice(i, 1);
                    $(el).removeData(flag);
                }
            }
            this.formError = emsg;
            return this;
        },
        //切换helper
        toggleHelper: function (el, type, msg) {
            var $el = $(el);
            //判断信息类型
            switch (type) {
                case "tip":
                    msg = $el.attr(TIPMSG) || msg;
                    break;

                case "success":
                    this._formErrorHandler(el, false);
                    msg = $el.attr(SUCCESSMSG) || msg || this.option("okText");
                    break;

                case "loading":
                    msg = $el.attr(LOADINGMSG) || msg;
                    break;

                default:
                    type = "error";
                    this._formErrorHandler(el, msg);
            }
            //切换helper
            if (typeof msg === "string") {
                this.showHelper(el, type, msg);
            } else {
                if (this.isGroup(el)) {
                    var _this = this;
                    var $els = this.getGroupElements(el);
                    $els.each(function () {
                        _this.hideHelper(this);
                    });
                } else {
                    this.hideHelper(el);
                }
            }
            this.updateFormHelper();
            return this;
        },
        //显示helper
        showHelper: function (el, type, msg) {
            if (!this.option("appendHelper")) {
                return;
            }
            var $helper = this.findHelper(el);
            var $ctn = $helper.find('[role="container"]');
            var $cont = $helper.find('[role="content"]');
            var oldCls = $helper.data("stateCls." + PLUGIN_NS) || "";
            var cls = this.option("stateCls", [type]) || "";
            $ctn.length == 0 && ($ctn = $helper);
            $cont.length == 0 && ($cont = $helper);
            //切换状态
            $helper.data("stateCls." + PLUGIN_NS, cls);
            $ctn.removeClass(oldCls).addClass(cls);
            //填充帮助信息
            $cont.html(msg);
            //显示帮助信息
            this.option("showHelperMethod", [$helper]);
        },
        //隐藏helper
        hideHelper: function (el) {
            if (!this.option("appendHelper")) {
                return;
            }
            var $helper = this.findHelper(el);
            this.option("hideHelperMethod", [$helper]);
            return this;
        },
        //获取helper
        //-优先级：dom-helper > dom-holder-helper > el-helper
        findHelper: function (el) {
            var $el = $(el), $helper;
            //dom配置helper
            var elHelper = $el.attr(HELPER);
            if (elHelper, $helper = $(elHelper), $helper.length > 0) {
                return $helper;
            }
            //dom配置holder
            var holder = $el.attr(HOLDER), $holder;
            if (holder, $holder = $(holder), $holder.length > 0) {
                $el = $holder;
            }
            //el-helper
            $helper = $el.next('[role="v-helper"]');
            if ($helper.length == 0) {
                $helper = $(this.option("tpl_helper")).insertAfter($el);
            }
            return $helper;
        },
        //获取表单helper
        findFormHelper: function () {
            return $(this.$el.attr(HELPER));
        },
        //更新表单helper
        updateFormHelper: function () {
            var $helper = this.findFormHelper();
            if ($helper.length == 0) {
                return;
            }
            var formError = this.formError;
            var len = formError.length;
            var msg = [];
            if (len > 0) {
                if (this.option("showLatestError")) {
                    msg.push(formError[len - 1].msg);
                } else {
                    for (var i = 0; i < len; i++) {
                        msg.push(formError[i].msg);
                    }
                }
            }
            if (this.options.tpl_formhelper) {
                var $el = $(this.option('tpl_formhelper', [{msg: msg}]));
                $helper.empty().append($el);
            } else {
                $helper.html(msg.join("<br/>"));
            }

        },
        //判断是否验证对象
        isElement: function (el) {
            return $(el).is(ELEMENTS);
        },
        //判断是否忽略验证
        isIgnored: function (el) {
            return $(el).is("[" + IGNORED + '="true"]');
        },
        //判断是否合法验证对象
        isTarget: function (el) {
            return this.isElement(el) && !this.isIgnored(el);
        },
        //判断是否组验证对象
        isGroup: function (el) {
            return this.isTarget(el) && $(el).attr(GROUP);
        },
        //获取验证对象
        getElements: function () {
            return this.$(ELEMENTS + ":not([" + IGNORED + '="true"])');
        },
        //获取组验证对象
        getGroupElements: function (el) {
            var group = $(el).attr(GROUP);
            return this.getElementsByGroup(group);
        },
        getElementsByGroup: function (group) {
            var _this = this;
            return this.$("[" + GROUP + '="' + group + '"]').filter(function () {
                return _this.isTarget(this);
            });
        },
        //获取验证值
        getValue: function (el) {
            return _.trim($(el).val());
        },
        /*
         * dom相关
         */
        //获取元素的jquery对象
        $: function (selector) {
            return this.$el.find(selector);
        },
        /*
         * 事件接口
         */
        //将配置中的接口绑定到事件
        bindAll: function () {
            var _this = this;
            $.each(this.options, function (key, value) {
                if (/^on[A-Z]/.test(key) && $.isFunction(value)) {
                    var ev = _.interface2Event(key);
                    _this.$el.on(ev + "." + PLUGIN_NS, $.proxy(value, _this));
                }
            });
        },
        //触发事件回调
        triggerHandler: function (ev, args, target) {
            var event = $.Event(ev + "." + PLUGIN_NS);
            (target || this.$el).trigger(event, args || []);
            return !event.isDefaultPrevented();
        },
        /*
         * 配置相关
         */
        //解析配置：若配置项为方法，则返回方法执行结果（args为方法参数数组）
        option: function (name, args) {
            return _.resultWith(this.options, name, args, this);
        },
        //设置配置
        setOptions: function (name, val) {
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
        instance: function () {
            return this;
        }
    });
    var old = $.fn[PLUGIN_NAME];
    var allow = ["defaults", "lang", "util", "regexp", "rules", "setDefaults", "setLang", "setRegexp", "setRules"];
    $.fn[PLUGIN_NAME] = function (options) {
        var args = Array.prototype.slice.call(arguments, 1);
        var isMethodCall = typeof options === "string";
        var returnValue = this;
        this.each(function () {
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
                $(this).data(PLUGIN_NS, instance = new Validator(this, options));
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Validator;
    $.fn[PLUGIN_NAME].noConflict = function () {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function () {
        $.fn[PLUGIN_NAME][this] = Validator[this];
    });
    $.fn.vAttr = vAttr;
    return Validator;
});
