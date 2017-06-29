/*
 * Module Name: notice
 * Module Require: jquery, underscore, ui/popup
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/notice/js/notice");
});

define("src/plugin/notice/js/notice.defaults", [ "../tpl/notice.container.html" ], function(require, exports, module) {
    var container = require("../tpl/notice.container.html");
    return function(_) {
        return {
            //Popup：模板
            tpl: _.template(container),
            //内容（支持字符串，html元素, jQuery对象）
            content: "提示信息",
            //状态
            state: "",
            //状态样式
            stateCls: function(state) {
                return state ? "notice-" + state : "";
            },
            //是否模态化
            modal: false,
            //自动关闭(ms)
            autoClose: 2e3,
            //点击立即关闭
            quickClose: false
        };
    };
});

define("src/plugin/notice/tpl/notice.container.html", [], '<div class="notice">\n    <i class="notice-icon"></i>\n    <div role="content" class="notice-main"></div>\n</div>');

define("src/plugin/notice/js/notice", [ "underscore", "ui/popup", "./notice.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var Popup = require("ui/popup");
    var defaults = require("./notice.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "notice";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    //全局变量
    var $document = $(document);
    // 定义插件类
    function Notice() {
        return this.constructor.__base__.apply(this, arguments);
    }
    // 继承Popup
    _.inherits(Notice, Popup);
    // 当前实例
    Notice.__instance = null;
    // 创建实例
    Notice.create = function(options) {
        var isModal = false;
        if (typeof options === "string") {
            var args = arguments;
            var content = options;
            options = {
                content: content
            };
            if (!_.isUndefined(args[1])) {
                options.state = args[1];
            }
            if (!_.isUndefined(args[2])) {
                options.autoClose = args[2];
            }
            if (!_.isUndefined(args[3])) {
                options.modal = args[3];
            }
        } else {
            options = $.extend({}, options);
        }
        Notice.destroy();
        _instance = Notice.__instance = Popup.create.call(Notice, options);
        return _instance._init();
    };
    //销毁实例
    Notice.destroy = function() {
        var _instance = Notice.__instance;
        if (_instance) {
            Popup.destroy(_instance.cid);
        }
    };
    // 设置默认选项
    Notice.setDefaults = Popup.setDefaults;
    // 设置zIndex
    Notice.setZIndex = Popup.setZIndex;
    // 定义默认选项
    Notice.defaults = $.extend({}, Popup.defaults, defaults);
    // 扩展插件原型
    $.extend(Notice.prototype, {
        //弹层创建完成后执行
        _onCreated: function() {
            var _this = this;
            _this.state(_this.option("state"));
            _this.content(_this.option("content"));
            _this.open(_this.option("modal"));
            return _this;
        },
        //弹层打开后执行
        _onOpened: function() {
            var _this = this, autoClose = parseInt(_this.option("autoClose"), 10) || 0;
            if (autoClose > 0) {
                if (!_this.lazyDestroy) {
                    _this.lazyDestroy = _.debounce(_this.destroy, autoClose);
                }
                _this.lazyDestroy();
            }
            if (this.option("quickClose")) {
                setTimeout(function() {
                    $document.one("click." + PLUGIN_NS, $.proxy(_this.destroy, _this)).one("keydown." + PLUGIN_NS, function(e) {
                        if (e.keyCode == 27) {
                            _this.destroy();
                        }
                    });
                }, 50);
            }
            return _this;
        },
        //获取/设置状态
        state: function(state) {
            var _this = this;
            if (arguments.length == 0) {
                return _.toRealString(_this.option(state));
            }
            state = _.toRealString(state);
            var stateCls = _this.option("stateCls", [ state ]);
            _this.$popup.addClass(stateCls);
            return _this;
        },
        //获取/设置内容
        //- 支持字符串，html元素和jquery对象
        content: function(content) {
            var _this = this, $content = _this.$role("content");
            if (_.isElement(content) || content instanceof $) {
                content = content.length > 0 && $(content);
            }
            if (content || typeof content == "string") {
                $content.empty().append(content);
                _this.reset();
                return _this;
            } else {
                return $content.html();
            }
        },
        //获取指定role的jquery对象
        $role: function(role) {
            return this.$('[role="' + role + '"]');
        }
    });
    var old = $[PLUGIN_NAME], allow = [ "defaults", "setDefaults", "setZIndex", "destroy" ];
    $[PLUGIN_NAME] = function() {
        return Notice.create.apply(this, arguments);
    };
    $[PLUGIN_NAME].Constructor = Notice;
    $[PLUGIN_NAME].noConflict = function() {
        $[PLUGIN_NAME] = old;
        return this;
    };
    _.each(allow, function(prop) {
        $[PLUGIN_NAME][prop] = Notice[prop];
    });
    return Notice;
});
