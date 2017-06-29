/*
 * Module Name: tooltip
 * Module Require: jquery, underscore, ui/popup
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/tooltip/js/tooltip");
});

define("src/plugin/tooltip/js/tooltip.defaults", [ "../tpl/tooltip.container.html" ], function(require, exports, module) {
    var container = require("../tpl/tooltip.container.html");
    return function(_) {
        return {
            //Popup：模板
            tpl: _.template(container),
            //Popup：对齐
            align: "right top",
            //内容（支持字符串，html元素, jQuery对象）
            content: "提示文字",
            //箭头样式
            arrowCls: {
                "left top": "tooltip-sw",
                "left center": "tooltip-e",
                "left bottom": "tooltip-nw",
                "center top": "tooltip-s",
                "center bottom": "tooltip-n",
                "right top": "tooltip-se",
                "right center": "tooltip-w",
                "right bottom": "tooltip-ne"
            },
            //关闭延迟
            closeDelay: 200,
            //箭头偏移量
            arrowOffset: 6
        };
    };
});

define("src/plugin/tooltip/tpl/tooltip.container.html", [], '<div class="tooltip">\n    <div role="arrow" class="tooltip-arrow">\n        <div class="tooltip-arrow-before"></div>\n        <div class="tooltip-arrow-after"></div>\n    </div>\n    <div role="content" class="tooltip-main"></div>\n</div>');

define("src/plugin/tooltip/js/tooltip", [ "underscore", "ui/popup", "./tooltip.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var Popup = require("ui/popup");
    var defaults = require("./tooltip.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee", PLUGIN_NAME = "tooltip", PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    // 全局变量
    var $document = $(document);
    // 定义插件类
    function Tooltip() {
        var returnValue = this.constructor.__base__.apply(this, arguments);
        this.el = this.option("alignTo");
        this.$el = $(this.el);
        return returnValue;
    }
    // 继承Popup
    _.inherits(Tooltip, Popup);
    // 创建实例
    Tooltip.create = function(el, options) {
        var cid = _.now() + "";
        options = $.extend({}, options, {
            alignTo: el,
            scrolling: false
        });
        return Popup.__instance[cid] = new Tooltip(cid, options)._init();
    };
    // 销毁实例
    Tooltip.destroy = Popup.destroy;
    // 设置zIndex
    Tooltip.setZIndex = Popup.setZIndex;
    // 定义默认选项
    Tooltip.defaults = $.extend({}, Popup.defaults, defaults);
    // 设置默认选项
    Tooltip.setDefaults = Popup.setDefaults;
    // 扩展插件原型
    $.extend(Tooltip.prototype, {
        _init: function() {
            var _this = this;
            _this.$el.one("mouseenter." + PLUGIN_NS, function(e) {
                e.stopPropagation();
                _this.create().open();
            });
            delete _this._init;
            return _this;
        },
        _onCreated: function() {
            var _this = this, content = _this.option("content"), align = _this._alignFilter(this.option("align")), arrowCls = $.extend({}, _this.option("arrowCls"))[align] || "";
            _this.__align = align;
            _this.__arrowCls = arrowCls;
            //设置箭头样式
            _this.setArrowCls(arrowCls);
            //填充内容
            _this.content(content);
            //绑定事件
            _this.$popup.add(_this.$el).on("mouseenter." + PLUGIN_NS, $.proxy(_this._mouseEnter, _this)).on("mouseleave." + PLUGIN_NS, $.proxy(_this._mouseLeave, _this));
            $document.on("click." + PLUGIN_NS, $.proxy(_this.close, _this));
            return _this;
        },
        _onClosed: function() {
            $document.off(PLUGIN_NS);
            this.$el.off(PLUGIN_NS);
        },
        //计算弹出位置
        offset: function() {
            var _this = this, offsetFn = _this.constructor.__base__.prototype.offset, //调用父类方法
            offset = offsetFn.apply(_this, arguments), top = offset.top, left = offset.left, arrowOffset = parseInt(_this.option("arrowOffset"), 10) || 0;
            switch (_this.__align) {
              case "left center":
              case "right center":
                left -= arrowOffset;
                break;

              default:
                top -= arrowOffset;
            }
            return {
                top: top,
                left: left
            };
        },
        //重置弹出层位置
        reset: function() {
            return this.position().setArrowCls();
        },
        //获取/设置内容
        //- 支持字符串，html元素和jquery对象
        content: function(content) {
            var _this = this, $content = _this.$role("content");
            if (_.isElement(content) || content instanceof $) {
                content = content.length > 0 && $(content).show();
            }
            if (content || typeof content == "string") {
                $content.empty().append(content);
                _this.reset();
                return _this;
            } else {
                return $content.html();
            }
        },
        //设置箭头样式
        setArrowCls: function(cls) {
            var _this = this;
            cls = cls || _this.__arrowCls;
            if (cls !== _this.__arrowCls) {
                _this.$popup.removeClass(old);
                _this.__arrowCls = cls;
            }
            _this.$popup.addClass(cls);
            return _this;
        },
        //过滤对齐方式
        _alignFilter: function(align) {
            var allow = [ "left top", "left center", "left bottom", "center top", "center bottom", "right top", "right center", "right bottom" ];
            align = _.trim(align);
            if ($.inArray(align, allow) == -1) {
                align = [ this.defaults.alignX, this.defaults.alignY ].join(" ");
            }
            return align;
        },
        //处理鼠标移入事件
        _mouseEnter: function() {
            var _this = this;
            _this.__timer && clearTimeout(_this.__timer);
            _this.open();
            return _this;
        },
        //处理鼠标移出事件
        _mouseLeave: function() {
            var _this = this, delay = parseInt(_this.option("closeDelay"), 10) || 0;
            _this.__timer && clearTimeout(_this.__timer);
            _this.__timer = setTimeout(function() {
                _this.close();
            }, delay);
            return _this;
        },
        //获取指定role的jquery对象
        $role: function(role) {
            return this.$('[role="' + role + '"]');
        }
    });
    var old = $.fn[PLUGIN_NAME];
    var allow = [ "defaults", "setDefaults", "setZIndex", "destroy" ];
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
                $(this).data(PLUGIN_NS, instance = Tooltip.create(this, options));
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Tooltip;
    $.fn[PLUGIN_NAME].noConflict = function() {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $.fn[PLUGIN_NAME][this] = Tooltip[this];
    });
    return Tooltip;
});
