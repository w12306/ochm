/*
 * Module Name: dialog
 * Module Require: jquery, underscore, ui/popup
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/dialog/js/dialog");
});

define("src/plugin/dialog/js/dialog.defaults", [ "../tpl/dialog.container.html" ], function(require, exports, module) {
    var container = require("../tpl/dialog.container.html");
    return function(_) {
        return {
            /*
             * 覆盖popup默认配置
             */
            //模板
            tpl: _.template(container),
            //对齐
            align: "center 38.2%",
            /*
             *dialog配置
             */
            //标题
            title: "标题",
            //内容（支持字符串，html元素, jQuery对象）
            content: "内容",
            //宽度
            width: "auto",
            //高度
            height: "auto",
            //最大宽度
            maxWidth: function() {
                return $(window).width();
            },
            //最大高度
            maxHeight: function() {
                return $(window).height();
            },
            //内容区padding
            padding: 20,
            //底部按钮
            buttons: null,
            //按钮基础样式
            buttonCls: "btn mr5",
            //点击关闭按钮隐藏
            clickToHide: false
        };
    };
});

define("src/plugin/dialog/tpl/dialog.container.html", [], '<div class="dialog">\n    <div role="container" class="dialog-inner">\n        <div role="header" class="dialog-hd">\n            <div role="title" class="dialog-title"></div>\n            <a role="close" class="dialog-close" href="javascript:;">&times;</a>\n        </div>\n        <div role="body" class="dialog-bd">\n            <div role="content" class="dialog-container"></div>\n        </div>\n        <div role="footer" class="dialog-ft"></div>\n    </div>\n</div>');

define("src/plugin/dialog/js/dialog", [ "underscore", "ui/popup", "./dialog.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var Popup = require("ui/popup");
    var defaults = require("./dialog.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee", PLUGIN_NAME = "dialog", PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    // 定义插件类
    function Dialog() {
        return this.constructor.__base__.apply(this, arguments);
    }
    // 继承Popup
    _.inherits(Dialog, Popup);
    // 创建实例
    Dialog.create = Popup.create;
    // 获取实例
    Dialog.get = Popup.get;
    // 销毁实例
    Dialog.destroy = Popup.destroy;
    // 设置zIndex
    Dialog.setZIndex = Popup.setZIndex;
    // 定义默认选项
    Dialog.defaults = $.extend({}, Popup.defaults, defaults);
    // 设置默认选项
    Dialog.setDefaults = Popup.setDefaults;
    // 扩展插件原型
    $.extend(Dialog.prototype, {
        //创建对话框
        //- Popup创建完成后执行
        _onCreated: function() {
            var _this = this;
            //设置对话框高宽
            _this.$popup.css({
                width: _this.option("width"),
                height: _this.option("height")
            });
            //按钮数组
            _this.__buttons = [];
            //填充标题
            _this.title(_this.option("title"));
            //设置padding
            var padding = parseInt(_this.option("padding"), 10) || 0;
            _this.$role("body").css("padding", padding);
            //填充内容
            _this.content(_this.option("content"));
            //设置按钮
            _this.setButton(_this.option("buttons"), {
                reset: true
            });
            //绑定事件
            _this.$popup.css({
                width: _this.option("width"),
                height: _this.option("height")
            }).on("click." + PLUGIN_NS, '[role="button"]', $.proxy(_this._buttonClick, _this)).on("click." + PLUGIN_NS, '[role="close"]', $.proxy(_this.closeOnClick, _this));
        },
        //重置对话框位置
        //- 重写Popup.reset方法
        reset: function() {
            return this.autoSize().position();
        },
        //对话框大小自适应
        autoSize: function() {
            var _this = this;
            if (_this.__isOpened) {
                var $popup = _this.$popup, //重置以获得内容区实际高宽
                $content = _this.$role("content").css({
                    height: "",
                    width: ""
                }), width = parseInt(_this.option("width"), 10) || "", height = parseInt(_this.option("height"), 10) || "", maxWidth = parseInt(_this.option("maxWidth"), 10), maxHeight = parseInt(_this.option("maxHeight"), 10), fixedHeight = _this._fixedHeight(), actualWidth = $content.outerWidth(), actualHeight = $content.outerHeight(), cWidth = "auto", cHeight = "auto";
                //固定高度
                if (height) {
                    //最小高度
                    cHeight = Math.max(height - _this._fixedHeight(), 0);
                }
                //最大高度
                if (!isNaN(maxHeight) && actualHeight > maxHeight - fixedHeight) {
                    cHeight = Math.max(parseInt(cHeight, 10) || 0, maxHeight - fixedHeight);
                }
                //最大宽度
                if (!isNaN(maxWidth) && actualWidth > maxWidth) {
                    cWidth = maxWidth;
                }
                //设置弹窗尺寸
                $popup.css({
                    width: width || "auto",
                    height: height || "auto"
                });
                //设置内容区尺寸
                $content.css({
                    overflow: "auto",
                    width: cWidth,
                    height: cHeight
                });
            }
            return _this;
        },
        //计算内容区外的固定高度
        _fixedHeight: function() {
            var _this = this, padding = parseInt(_this.option("padding"), 10) || 0, headerHeight = _this.$role("header").outerHeight(), footerHeight = _this.$role("button", "footer").length > 0 ? _this.$role("footer").outerHeight() : 0;
            return headerHeight + footerHeight + padding * 2;
        },
        //获取/设置标题
        title: function(title) {
            var _this = this, $title = _this.$role("title");
            if (typeof title == "string") {
                $title.html(title);
                return _this;
            } else {
                return $title.html();
            }
        },
        //获取/设置内容
        //- 支持字符串，html元素和jquery对象
        content: function(content) {
            var _this = this, $content = _this.$role("content");
            if (_.isElement(content) || content instanceof $) {
                content = $(content);
            }
            if (content || typeof content == "string") {
                $content.empty().append(content);
                _this.reset();
                return _this;
            } else {
                return $content.html();
            }
        },
        //设置按钮
        //- 根据配置智能判断添加|更新|删除|重置
        //- buttons {Array|Object} 按钮（组）信息
        //- options {Object} 选项
        //  {
        //    reset:{Boolean},    //是否重置
        //    remove:{Boolean}    //是否移除
        //  }
        setButton: function(buttons, options) {
            var _this = this, $footer = _this.$role("footer"), $buttons = _this.$role("button", "footer"), $button, reset, remove;
            options = $.extend({}, options);
            reset = options.reset;
            remove = options.remove;
            buttons = $.makeArray(buttons);
            if (reset) {
                _this.__buttons = [];
                $footer.empty();
            }
            _.each(buttons, function(button) {
                var text = typeof button == "string" ? button : button.text, data = _this._buttonData(button), i, info;
                if (!reset && (info = _this.getButtonInfo(text))) {
                    i = info.i;
                    //to remove
                    if (remove) {
                        _.rest(_this.__buttons, i);
                        $buttons.eq(i).remove();
                        return true;
                    }
                    //to update
                    $.extend(_this.__buttons[i], data);
                    $button = _this.$role("button", "footer").eq(i);
                } else {
                    //to add
                    _this.__buttons.push(data);
                    $button = $("<button/>").appendTo($footer);
                }
                $button.data("button." + PLUGIN_NS, data).attr({
                    role: "button",
                    style: data.style,
                    "class": data.cls,
                    disabled: data.disabled
                }).html(data.text);
            });
            $footer.toggle(_this.$role("button", "footer").length != 0);
            _this.reset();
            return _this;
        },
        //获取按钮信息
        getButtonInfo: function(text) {
            var _this = this, i = -1, data;
            text = _.toRealString(text);
            data = _.find(_this.__buttons, function(button, index) {
                i = index;
                return button.text == text;
            });
            return data != undefined ? {
                i: i,
                //序号
                data: data
            } : null;
        },
        // 处理按钮信息
        // -返回按钮配置信息
        // {
        //   text: {String}    名称
        //   cls:  {String}    css类
        //   css:  {String}    css样式
        //   disabled: {Boolean} 是否禁用
        //   click:{Function}  点击事件回调 （默认关闭 return false不关闭）
        // }
        _buttonData: function(data) {
            var buttonCls = this.option("buttonCls");
            if (typeof data == "string") {
                data = {
                    text: data
                };
            }
            data = $.extend({
                text: "",
                cls: null,
                css: null,
                disabled: false,
                click: null
            }, data);
            if (typeof data.cls == "string" && data.cls) {
                data.cls = [ buttonCls, data.cls ].join(" ");
            } else {
                data.cls = buttonCls;
            }
            return data;
        },
        //处理按钮点击事件
        _buttonClick: function(e) {
            e && e.preventDefault();
            var $button = $(e.currentTarget), config = $button.data("button." + PLUGIN_NS), isClose = _.resultWith(config, "click", [], this);
            if (isClose === false) {
                return this;
            }
            this.closeOnClick();
        },
        //点击关闭对话框
        closeOnClick: function() {
            if (this.option("clickToHide")) {
                this.hide();
            } else {
                this.close();
            }
        },
        //获取指定role的jquery对象
        $role: function(role, parent) {
            var _this = this, $parent = _this.$popup;
            parent = _.trim(parent);
            parent && ($parent = _this.$role(parent));
            return $parent.find('[role="' + role + '"]');
        },
        //获取button
        $button: function(i) {
            return this.$role("button", "footer").eq(i);
        }
    });
    var old = $.fn[PLUGIN_NAME];
    var allow = [ "defaults", "setDefaults", "setZIndex", "get", "destroy" ];
    $[PLUGIN_NAME] = function() {
        return Dialog.create.apply(Dialog, arguments);
    };
    $[PLUGIN_NAME].Constructor = Dialog;
    $[PLUGIN_NAME].noConflict = function() {
        $[PLUGIN_NAME] = old;
        return this;
    };
    _.each(allow, function(prop) {
        $[PLUGIN_NAME][prop] = Dialog[prop];
    });
    return Dialog;
});
