/*
 * Module Name: combobox
 * Module Require: jquery, underscore, ui/selectbox
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/combobox/js/combobox");
});

define("src/plugin/combobox/js/combobox.defaults", [], function(require, exports, module) {
    return function(_) {
        return {
            //静态数据
            data: {},
            //默认信息
            model: {
                //id
                id: "",
                //父级id
                pid: null,
                //数据索引
                data: "",
                //插件配置
                options: {},
                //是否异步获取数据
                async: false,
                //异步地址
                url: "",
                //解析发送参数
                parseParams: function(pid) {
                    if (pid) {
                        var params = {};
                        params[this.pid] = pid;
                        return params;
                    }
                    return false;
                },
                //解析返回数据
                parseResponse: function(resp) {
                    return resp.data;
                }
            },
            //配置selectbox集合
            collection: [],
            //选项数据载入后（第一次）：
            onInitLoaded: null,
            //选项数据载入后（非第一次）
            onLaterLoaded: null,
            //选项数据载入后
            onLoaded: null
        };
    };
});

define("src/plugin/combobox/js/combobox", [ "underscore", "./combobox.defaults", "ui/selectbox" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var defaults = require("./combobox.defaults")(_);
    require("ui/selectbox");
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "combobox";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    // 定义插件类
    function Combobox(element, options) {
        this.el = element;
        this.$el = $(element);
        this.defaults = Combobox.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 定义默认选项
    Combobox.defaults = defaults;
    // 设置默认选项
    Combobox.setDefaults = function(options) {
        $.extend(Combobox.defaults, options);
    };
    // 扩展插件原型
    $.extend(Combobox.prototype, {
        _init: function() {
            var collection = this.option("collection");
            var deferred = [];
            this.models = [];
            this.__initialized = {};
            //过滤不存在的元素
            _.each(collection, $.proxy(function(model) {
                model = model || {};
                var id = model.id, $el = this.$(id), existing = $el.length > 0;
                if (existing) {
                    !this.__root && (this.__root = id);
                    this.models.push($.extend({}, this.getDefaultModel(), model));
                    deferred.push($.Deferred());
                }
            }, this));
            //监听selectbox初始化
            $.when.apply($, deferred).done($.proxy(this._onReady, this));
            //初始化selectbox
            _.each(this.models, $.proxy(function(model, i) {
                this.$(model.id).on("created.bee.selectbox", deferred[i].resolve).selectbox(model.options);
            }, this));
        },
        //所有插件初始化后的处理方法：绑定事件 & 处理选中
        _onReady: function() {
            //绑定change事件
            _.each(this.models, $.proxy(function(model) {
                var id = model.id, child = _.findWhere(this.models, {
                    pid: id
                }), cid;
                if (child && (cid = child.id) && this.$(cid).length > 0) {
                    this.$(id).on("changed.bee.selectbox", $.proxy(function(e) {
                        this.loadDataByPid(cid, $(e.currentTarget).val());
                    }, this));
                }
            }, this));
            //载入root selectbox数据
            this.loadDataByPid(this.__root);
        },
        //数据格式化：保证最上级数据为数组
        dataFormatter: function(data) {
            if (_.isArray(data)) {
                return data;
            } else {
                return _.union.apply(_, _.toArray(data));
            }
        },
        //根据父级id获取数据
        fetchDataByPid: function(type, pid, callback) {
            type = _.toRealString(type);
            pid = _.toRealString(pid);
            callback = _.isFunction(callback) ? callback : $.noop;
            var model = _.findWhere(this.models, {
                id: type
            });
            if (model.async) {
                var params = _.resultWith(model, "parseParams", [ pid ]);
                //参数结果为false并且不是根节点的不发请求
                if (params === false && type != this.__root) {
                    //数据置空
                    callback.call(this, [], true);
                    return;
                }
                $.ajax({
                    url: model.url,
                    data: params,
                    success: $.proxy(function(resp) {
                        var data = _.resultWith(model, "parseResponse", [ resp ]) || resp;
                        callback.call(this, data, true);
                    }, this),
                    error: $.proxy(function() {
                        //数据置空
                        callback.call(this, [], true);
                    }, this)
                });
            } else {
                var data = model.data, toSet = true;
                //支持本地数据索引模式
                var localData = this.option("data");
                if (_.isString(data) && localData) {
                    data = localData[data];
                }
                if (_.isArray(data) || _.isObject(data)) {
                    //判断是否root
                    if (type == this.__root) {
                        data = this.dataFormatter(data);
                    } else {
                        data = data[pid];
                    }
                } else {
                    toSet = false;
                }
                callback.call(this, data, toSet);
            }
        },
        //根据父级id载入数据
        loadDataByPid: function(type, pid) {
            type = _.toRealString(type);
            this.fetchDataByPid(type, pid, function(data, toSet) {
                var $el = this.$(type);
                var $$ = $el.selectbox("instance");
                if (toSet) {
                    var selection;
                    if (!this.__initialized[type]) {
                        var val = this.$(type).attr("data-val");
                        selection = val && val.split(",");
                    }
                    $el.selectbox("setData", data, $.extend(_.isUndefined(selection) ? {} : {
                        selection: selection
                    }, {
                        silent: true
                    }));
                }
                //读取子数据
                var child = _.findWhere(this.models, {
                    pid: type
                });
                if (child) {
                    this.loadDataByPid(child.id, $el.val());
                }
                if (!this.__initialized[type]) {
                    this.__initialized[type] = 1;
                    this.triggerHandler("loaded:init", [ $$, type ]);
                } else {
                    this.triggerHandler("loaded:later", [ $$, type ]);
                }
                this.triggerHandler("loaded", [ $$, type ]);
            });
        },
        //获取默认model
        getDefaultModel: function() {
            return $.extend({}, this.defaults.model, this.option("model"));
        },
        /*
         * dom相关
         */
        $: function(type) {
            return this.$el.find(".js-" + type);
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
                $(this).data(PLUGIN_NS, instance = new Combobox(this, options));
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Combobox;
    $.fn[PLUGIN_NAME].noConflict = function() {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $.fn[PLUGIN_NAME][this] = Combobox[this];
    });
    return Combobox;
});
