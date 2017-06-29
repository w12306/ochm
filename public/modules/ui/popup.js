/*
 * Module Name: popup
 * Module Require: jquery, underscore
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/popup/js/popup");
});

define("src/plugin/popup/js/popup.defaults", [], function(require, exports, module) {
    return function(_) {
        return {
            //弹出层模板
            tpl: "<div/>",
            //遮罩层模板
            tpl_overlay: "<div/>",
            //弹出层css类
            cls: null,
            //弹出层css样式
            css: null,
            //遮罩层样式
            overlayCss: null,
            //对齐
            align: "center center",
            //对齐到
            alignTo: null,
            //是否随滚动条滚动（不支持ie6）
            scrolling: true,
            /**
             * 事件接口
             */
            //层创建前
            onCreate: null,
            //层创建后
            onCreated: null,
            //层打开前
            onOpen: null,
            //层打开后
            onOpened: null,
            //层关闭后
            onClose: null,
            //层关闭后
            onClosed: null,
            //层隐藏前
            onHide: null,
            //层隐藏后
            onHidden: null
        };
    };
});

define("src/plugin/popup/js/popup", [ "underscore", "./popup.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var defaults = require("./popup.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "popup";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    // 全局变量
    var $window = $(window);
    var isIE6 = _.isIE6();
    //使用$.Callbacks实现接口事件
    var Events = {
        on: function(ev, callback) {
            var callbacks = this._callbacks(ev);
            callbacks && callbacks.add(callback);
            return this;
        },
        once: function(ev, callback) {
            if (!$.isFunction(callback)) {
                return this;
            }
            var callbacks = this._callbacks(ev);
            var fn = function() {
                callback.apply(this, arguments);
                callbacks.remove(fn);
            };
            callbacks.add(fn);
            return this;
        },
        off: function(ev) {
            var callbacks = this._callbacks(ev);
            if (callbacks) {
                callbacks.empty();
                delete callbacks;
            }else{
                for(var _ev in this.__callbacks){
                    this.off(_ev);
                }
            }
            return this;
        },
        trigger: function(ev) {
            var callbacks = this._callbacks(ev);
            callbacks && callbacks.fireWith(this);
            return this;
        },
        _callbacks: function(ev) {
            var callbacks = null;
            if (ev = typeof ev == "string" && $.trim(ev)) {
                this.__callbacks || (this.__callbacks = {});
                callbacks = this.__callbacks[ev];
                if (!callbacks) {
                    callbacks = this.__callbacks[ev] = $.Callbacks();
                }
            }
            return callbacks;
        }
    };
    //对齐
    //--------------------------------
    //x         横向对齐方式
    //y         纵向对齐方式
    //to        对齐到(默认对齐到窗口)
    function Align(x, y, to) {
        var $window = $(window);
        var $document = $(document);
        var $me = $(this);
        var fixed = $me.css("position") === "fixed";
        var dsLeft = fixed ? 0 : $document.scrollLeft();
        var dsTop = fixed ? 0 : $document.scrollTop();
        var winWidth = $window.width();
        var winHeight = $window.height();
        var width = $me.outerWidth();
        var height = $me.outerHeight();
        var allowX = [ "center", "left", "right" ];
        var allowY = [ "center", "top", "bottom" ];
        var top, left;
        //过滤对齐方式
        if (_.indexOf(allowX, _.trim(x)) === -1 && !parseInt(x, 10)) {
            x = "center";
        }
        if (_.indexOf(allowY, _.trim(y)) === -1 && !parseInt(y, 10)) {
            y = "center";
        }
        //判断元素是否合法（排除document,window,body等 ,以及不存在和隐藏元素）
        to = $(to).eq(0).get(0);
        if (to && to.parentNode && !$(to).is(":hidden")) {
            var $to = $(to), tWidth = $to.outerWidth(), tHeight = $to.outerHeight(), tOffset = $to.offset(), tLeft = tOffset.left, tTop = tOffset.top;
            switch (x) {
              case "left":
                if (y == "center") {
                    left = tLeft - width;
                } else {
                    left = tLeft;
                }
                break;

              case "center":
                left = tLeft + (tWidth - width) / 2;
                break;

              case "right":
                if (y == "center") {
                    left = tLeft + tWidth;
                } else {
                    left = tLeft + tWidth - width;
                }
                break;

              default:
                left = tLeft + parseInt(x, 10) || 0;
            }
            switch (y) {
              case "top":
                top = tTop - height;
                break;

              case "center":
                top = tTop + (tHeight - height) / 2;
                break;

              case "bottom":
                top = tTop + tHeight;
                break;

              default:
                top = tTop + parseInt(y, 10) || 0;
            }
            top = Math.round(top);
            left = Math.round(left);
        } else {
            //相对window对齐
            var coords = [ x || 0, y || 0 ];
            for (var i = 0; i < 2; i++) {
                switch (coords[i]) {
                  case "top":
                  case "left":
                    coords[i] = "0%";
                    break;

                  case "bottom":
                  case "right":
                    coords[i] = "100%";
                    break;

                  case "center":
                    coords[i] = "50%";
                    break;

                  default:
                    if (!/%$/.test(coords[i])) {
                        coords[i] = parseInt(coords[i], 10) || 0;
                    }
                }
            }
            //计算水平偏移量
            x = coords[0];
            if (/%$/.test(x)) {
                left = (winWidth - width) * parseInt(x, 10) / 100 + dsLeft;
            } else {
                left = dsLeft + x;
            }
            //计算垂直偏移量
            y = coords[1];
            if (/%$/.test(y)) {
                top = (winHeight - height) * parseInt(y, 10) / 100 + dsTop;
            } else {
                top = dsTop + y;
            }
            top = Math.round(Math.max(top, dsTop));
            left = Math.round(Math.max(left, dsLeft));
        }
        return {
            top: top,
            left: left
        };
    }
    // 定义插件类
    function Popup(cid, options) {
        this.cid = cid;
        this.defaults = this.constructor.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 定义默认选项
    Popup.defaults = defaults;
    // 设置默认选项
    Popup.setDefaults = function(options) {
        $.extend(Popup.defaults, options);
    };
    // 实例集合
    Popup.__instance = {};
    // 获取实例
    Popup.get = function(cid) {
        return Popup.__instance[cid];
    };
    // 创建实例
    Popup.create = function(cid, options) {
        var _instance;
        if (typeof cid == "string") {
            cid = $.trim(cid);
            _instance = Popup.get(cid);
        } else {
            options = cid;
            cid = _.now() + "";
        }
        if (!_instance) {
            _instance = Popup.__instance[cid] = new this(cid, options)._init();
        }
        return _instance;
    };
    // 销毁实例
    Popup.destroy = function(cid) {
        var _instance = Popup.get(cid);
        _instance && _instance.close();
        delete Popup.__instance[cid];
    };
    // 销毁所有实例
    Popup.destroyAll = function() {
        var popups = Popup.__instance;
        $.each(popups, function(cid) {
            Popup.destroy(cid);
        });
        Popup.__count = 0;
    };
    //获取zIndex
    Popup.getZIndex = function() {
        return Popup.__zIndex;
    };
    //设置zIndex
    Popup.setZIndex = function(zIndex) {
        var old = Popup.__zIndex;
        Popup.__zIndex = parseInt(zIndex, 10) || old;
    };
    //计数
    Popup.__count = 0;
    //基础zIndex
    Popup.__zIndex = 999;
    // 扩展插件原型
    $.extend(Popup.prototype, Events, {
        //初始化方法：插件初始化后自动调用
        _init: function() {
            this.create();
            delete this._init;
            return this;
        },
        //创建弹出层
        create: function() {
            var _this = this;
            if (!_this.__isCreated) {
                this.__cssPosition = this.option("scrolling") && !isIE6 ? "fixed" : "absolute";
                _this.triggerHandler("create");
                var $popup = $(_this.option("tpl") || _this.defaults.tpl);
                if ($popup.length > 1) {
                    $popup = $(tpl).wrapInner($popup);
                }
                //创建节点
                _this.$popup = $popup.addClass(_this.option("cls")).css($.extend({
                    display: "none",
                    position: _this.__cssPosition
                }, _this.option("css"))).appendTo("body");
                //绑定事件
                $window.on("resize." + PLUGIN_NS, $.proxy(_this._windowResize, _this));
                _this.__isCreated = true;
                _this.triggerHandler("created");
            }
            return _this;
        },
        //打开弹出层
        open: function(isModal) {
            var _this = this;
            if (!_this.__isOpened) {
                if (!_this.__isCreated) {
                    _this.create();
                }
                _this.triggerHandler("open");
                if (isModal) {
                    _this.openOverlay();
                }
                _this.$popup && _this.$popup.show();
                _this.__isOpened = true;
                _this._zIndex().reset();
                _this.triggerHandler("opened");
            }
            return _this;
        },
        //关闭弹出层
        close: function() {
            var _this = this;
            if (_this.__isCreated) {
                _this.triggerHandler("close");
                _this.$popup && _this.$popup.remove();
                _this.destroyOverlay();
                $window.off("resize." + PLUGIN_NS);
                _this.__isOpened = false;
                _this.__isCreated = false;
                _this.triggerHandler("closed");
            }
            return _this;
        },
        //隐藏弹出层
        hide: function() {
            var _this = this;
            if (_this.__isCreated && _this.__isOpened) {
                _this.triggerHandler("hide");
                _this.$popup && _this.$popup.hide();
                _this.hideOverlay();
                _this.__isOpened = false;
                _this.triggerHandler("hidden");
            }
            return _this;
        },
        //销毁弹出层
        destroy: function() {
            this.off();
            Popup.destroy(this.cid);
        },
        //打开遮罩层
        openOverlay: function() {
            var _this = this;
            if (!_this.$overlay) {
                _this.$overlay = $(_this.option("tpl_overlay") || _this.defaults.tpl_overlay).css($.extend({
                    display: "none",
                    position: _this.__cssPosition,
                    top: 0,
                    left: 0,
                    width: "100%",
                    height: "100%",
                    background: "#000",
                    opacity: "0.4"
                }, _this.option("overlayCss"))).insertBefore(_this.$popup);
            }
            _this.$overlay.show();
            return this;
        },
        //隐藏遮罩层
        hideOverlay: function() {
            var _this = this;
            _this.$overlay && _this.$overlay.hide();
            return _this;
        },
        //销毁遮罩层
        destroyOverlay: function() {
            var _this = this;
            _this.$overlay && _this.$overlay.remove();
            delete _this.$overlay;
            return _this;
        },
        //调整遮罩层大小
        resizeOverlay: function() {
            var _this = this;
            _this.$overlay && _this.$overlay.css({
                width: $window.width(),
                height: $window.height()
            });
        },
        //重置弹出层位置
        reset: function() {
            return this.position();
        },
        //定位弹出层
        position: function(align, alignTo) {
            var _this = this;
            if (_this.__isOpened) {
                var x, y, offset;
                align = (align || _this.option("align")).split(" ");
                alignTo = alignTo || _this.option("alignTo");
                x = align[0] || "center";
                y = align[1] || "center";
                //计算位置
                offset = _this.offset(x, y, alignTo);
                _this.$popup && _this.$popup.css(offset);
            }
            return _this;
        },
        //计算弹出位置
        offset: function() {
            return Align.apply(this.$popup, arguments);
        },
        //自动调整层级
        _zIndex: function() {
            var _this = this;
            if (_this.__isCreated) {
                var $popup = _this.$popup, _zIndex = parseInt($popup.css("zIndex"), 10) || -1, zIndex = Popup.__zIndex + Popup.__count;
                if (_zIndex < zIndex) {
                    Popup.__count += 2;
                    zIndex += 2;
                    _this.$popup.css("zIndex", zIndex);
                    _this.$overlay && _this.$overlay.css("zIndex", zIndex - 1);
                }
            }
            return _this;
        },
        //处理window resize事件
        _windowResize: function() {
            var _this = this;
            if (_this.__isCreated) {
                //处理IE6
                isIE6 && _this.resizeOverlay();
                //重新定位
                _this.reset();
            }
        },
        /*
         * dom相关
         */
        //获取元素的jquery对象
        $: function(selector) {
            return this.$popup.find(selector);
        },
        /*
         * 事件接口
         */
        //将配置中的接口绑定到事件
        bindAll: function() {
            var _this = this;
            $.each(this.options, function(key, value) {
                if (/^on[A-Z]/.test(key)) {
                    var ev = _.interface2Event(key);
                    var fn = _this["_" + key];
                    //绑定内部方法，用于子类扩展
                    $.isFunction(fn) && _this.on(ev, fn);
                    //绑定配置接口
                    $.isFunction(value) && _this.on(ev, value);
                }
            });
        },
        //触发事件回调
        triggerHandler: function(ev, args) {
            this.trigger(ev, args || []);
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
    var old = $[PLUGIN_NAME];
    var allow = [ "defaults", "setDefaults", "getZIndex", "setZIndex", "get", "destroy", "destroyAll" ];
    $[PLUGIN_NAME] = function(options) {
        return Popup.create.apply(Popup, arguments);
    };
    $[PLUGIN_NAME].Constructor = Popup;
    $[PLUGIN_NAME].noConflict = function() {
        $[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $[PLUGIN_NAME][this] = Popup[this];
    });
    $.align = Align;
    return Popup;
});
