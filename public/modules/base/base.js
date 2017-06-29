/*
 基础类 （可根据项目自行扩展）
 * */
define(function (require) {

    var _ = require('underscore');
    var Backbone = require('backbone');
    var Async = require('coms/async');
    var ST = require("lib/ST");
    var Base = {};

    var arraySlice = Array.prototype.slice;

    //事件名转方法名
    function event2method(name) {
        var result = [];
        name = _.trim(name);
        if (name) {
            var arr = name.split(':');
            if (_.indexOf(['before', 'after'], arr[0]) > -1) {
                result.push(arr[0]);
                arr.shift();
            } else {
                result.push('on');
            }
            for (var i = arr.length - 1; i >= 0; i--) {
                var word = arr[i].toLowerCase();
                word = _.capital(word);
                result.push(word);
            }
        }

        return result.join('');
    }

    //重写sync方法
    function sync(method, model, options) {
        var params = {};
        options = _.extend({}, options);

        if (options.data == null && model && (method === 'create' || method === 'update' || method === 'patch')) {
            params.data = $.param(options.attrs || model.toJSON(options));
        }

        if (!options.url) {
            params.url = _.result(model, 'url') || location.href;
        }

        _.extend(params, options);

        //扩展添加pars参数
        /*
         *   ST.AJAXDATA 公共参数 全站ajax请求都会带上的参数
         *   model.pars 静态参数  通常固定写入或者实例化时设置一次
         *   model.get("pars") 动态参数  通常由router 通过changePars传入，如page:1，查询参数等.
         *   优先级：实际参数 > 动态参数 > 静态参数 > 公共参数
         * */
        params.data = _.extend({}, ST.AJAXDATA, _.result(model, 'pars'), model.get("pars"), params.data);

        params.type = model.type || "get";

        params.success = function (resp) {
            // var result = resp[Async.fields[2]];
            _.isFunction(options.success) && options.success.call(this, resp);
        };

        var xhr = options.xhr = Async.ajax(params);
        model.trigger('request', model, xhr, options);
        return xhr;
    }

    /**
     * 基础Model
     */
    Base.Model = Backbone.Model.extend({
        sync: sync
    });

    /**
     * 基础Collection
     */
    Base.Collection = Backbone.Collection.extend({
        sync: sync
    });

    /**
     * 基础View
     */
    Base.View = Backbone.View.extend({
        ui: {},
        triggerHandler: function (event_name) {
            var args = arraySlice.call(arguments, 1);
            var method_name = event2method(event_name);
            this.trigger.apply(this, _.union([event_name], args));
            _.isFunction(this[method_name]) && this[method_name].apply(this, args);
        },
        constructor: function (options) {
            // 定义options，在任何方法里可用this.options调用
            this.options = _.extend({}, _.result(this, 'options'), _.isFunction(options) ? options.call(this) : options);
            // 定义子View管理器
            this.children = new Backbone.ChildViewContainer();
            // 缓存ui元素
            this.on('after:render', this.cacheUIElements);
            // 调用Backbone.View构造器
            Backbone.View.prototype.constructor.apply(this, arguments);
        },
        //缓存this.ui中定义的元素
        //在任何方法里可使用this.$ui调用
        cacheUIElements: function () {
            var $ui = {}, $el = this.$el;
            _.each(this.ui, function (v, k) {
                $ui[k] = $el.find(v);
            });
            return this.$ui = $ui;
        },
        //获取模板数据 {data: 模型数据, items:集合数据}
        //模型 ：this.model
        //集合：this.collection
        getTemplateData: function () {
            var data = {};
            //将模型和集合里的数据转换成模板数据
            if (this.model) {
                _.extend(data, {data: this.model.toJSON()});
            }
            if (this.collection) {
                _.extend(data, {items: this.collection.toJSON()});
            }
            return data;
        },
        //使用this.template中定义的方法渲染模板
        _render: function () {
            if (!this.template) return this;
            this.__isClosed = false;
            this.triggerHandler('render');
            this.$el.html(this.getRendered());
            this.__isRendered = true;
            this.triggerHandler('after:render');
            return this;
        },
        //调用的this._render，可以根据需要重置
        render: function () {
            return this._render.apply(this, arguments);
        },
        //获取渲染后的html
        getRendered: function () {
            var tpl = this.template,
                result = '';
            if (_.isFunction(tpl)) {
                result = this.template(this.getTemplateData())
            } else if (typeof tpl == 'string') {
                result = tpl;
            }
            return result;
        },
        //参数变更通知
        changePars: function (pars) {
            if (this.model) {
                //通知模型参数变更，由模型驱动自身的视图变化
                this.model.set("pars", this.model.parsList ? ST.serData(pars, this.model.parsList) : pars);
                //递归执行
                var children = this.children;
                children.each(function (c) {
                    c.changePars && c.changePars(pars);
                });
            }
        },
        //关闭view及其子view（如有）
        close: function () {
            //
            if (this.__isClosed) {
                return this;
            }
            // 判断是否关闭view
            if (_.isFunction(this.shouldClose) && !this.shouldClose()) {
                return this;
            }
            // 将关闭标识置为true
            this.__isClosed = true;
            // 关闭子view
            this.closeChildren();
            // 触发关闭前回调
            this.triggerHandler('close');
            this.remove();
            // 触发关闭后回调（此时）
            this.triggerHandler('after:close');
            return this;
        },
        //关闭子view（如有）
        closeChildren: function () {
            var children = this.children;
            children.each(_.bind(this.removeChildView, this));
        },
        //添加子view
        addChildView: function (view, index) {
            if (!this.children.findByCid(view.cid)) {
                this.triggerHandler('add:child', view);
                view.parent = this;
                this.children.add(view, index);
                this.triggerHandler('after:add:child', view);
            }
            return this;
        },
        //移除子view
        removeChildView: function (view) {
            if (typeof view === 'string') {
                view = {cid: view};
            }
            view = this.children.findByCid(view.cid);
            if (view) {
                this.triggerHandler('remove:child', view);
                this.children.remove(view);
                if (_.isFunction(view.close)) {
                    view.close();
                } else {
                    view.remove();
                }
                this.triggerHandler('after:remove:child');
            }
            return this;
        }
    });

    /**
     * 列表View
     */
    Base.ListView = Base.View.extend({
        itemView: Backbone.View.extend(),
        constructor: function (options) {
            // 定义子view容器
            this.itemContainer = this.itemContainer || this.el;
            // 定义集合
            if (!this.collection) {
                this.collection = new Backbone.Collection();
            }
            // 调用父类构造器
            Base.View.prototype.constructor.apply(this, arguments);
            // 初始化事件
            this.collection.each(_.bind(this.addItemView, this));
            this.listenTo(this.collection, "all", this._assignEvent, this);
        },
        // 分配事件回调方法
        _assignEvent: function (event_name) {
            var args = arraySlice.call(arguments, 1);
            switch (event_name) {
                case 'add':
                    this.addItemView.apply(this, args);
                    break;
                case 'remove':
                    this.removeItemView.apply(this, args);
                    break;
                case 'sync':
                case 'reset':
                case 'destroy':
                    this.renderItems();
                    break;
            }
        },
        // 一次渲染所有项视图
        renderItems: function () {
            var _this = this;
            var temp = document.createDocumentFragment();
            // 确保主view已渲染
            if (!_this.__isRendered) {
                this.render.apply(this, arguments);
            }
            // 创建文档片段，避免多次操作dom
            _this.collection.each(function (item) {
                var itemView = _this.children.findByModel(item);
                itemView.render();
                temp.appendChild(itemView.el);
            });
            //触发子项渲染前回调
            this.triggerHandler('render:items');
            //渲染子项
            this.$(_this.itemContainer).html(temp);
            //触发子项渲染后回调
            this.triggerHandler('after:render:items');
            return _this;
        },
        // 创建一个新项视图，可根据需要重置
        newItemView: function (model) {
            return new this.itemView({model: model});
        },
        // 添加项视图
        addItemView: function (model) {
            var itemView = this.newItemView(model),
                index = this.collection.indexOf(model);
            return this.addChildView(itemView, index);
        },
        // 移除项视图
        removeItemView: function (model) {
            var itemView = this.children.findByModel(model);
            return this.removeChildView(itemView);
        }
    });

    /**
     * Third Party Plugins
     */

        // Backbone.BabySitter
        // -------------------
        // v0.1.0
        //
        // Copyright (c)2014 Derick Bailey, Muted Solutions, LLC.
        // Distributed under MIT license
        //
        // http://github.com/marionettejs/backbone.babysitter

        // Backbone.ChildViewContainer
        // ---------------------------
        //
        // Provide a container to store, retrieve and
        // shut down child views.

    Backbone.ChildViewContainer = (function (Backbone, _) {

        // Container Constructor
        // ---------------------

        var Container = function (views) {
            this._views = {};
            this._indexByModel = {};
            this._indexByCustom = {};
            this._updateLength();

            _.each(views, this.add, this);
        };

        // Container Methods
        // -----------------

        _.extend(Container.prototype, {

            // Add a view to this container. Stores the view
            // by `cid` and makes it searchable by the model
            // cid (and model itself). Optionally specify
            // a custom key to store an retrieve the view.
            add: function (view, customIndex) {
                var viewCid = view.cid;

                // store the view
                this._views[viewCid] = view;

                // index it by model
                if (view.model) {
                    this._indexByModel[view.model.cid] = viewCid;
                }

                // index by custom
                if (customIndex) {
                    this._indexByCustom[customIndex] = viewCid;
                }

                this._updateLength();
                return this;
            },

            // Find a view by the model that was attached to
            // it. Uses the model's `cid` to find it.
            findByModel: function (model) {
                return this.findByModelCid(model.cid);
            },

            // Find a view by the `cid` of the model that was attached to
            // it. Uses the model's `cid` to find the view `cid` and
            // retrieve the view using it.
            findByModelCid: function (modelCid) {
                var viewCid = this._indexByModel[modelCid];
                return this.findByCid(viewCid);
            },

            // Find a view by a custom indexer.
            findByCustom: function (index) {
                var viewCid = this._indexByCustom[index];
                return this.findByCid(viewCid);
            },

            // Find by index. This is not guaranteed to be a
            // stable index.
            findByIndex: function (index) {
                return _.values(this._views)[index];
            },

            // retrieve a view by its `cid` directly
            findByCid: function (cid) {
                return this._views[cid];
            },

            // Remove a view
            remove: function (view) {
                var viewCid = view.cid;

                // delete model index
                if (view.model) {
                    delete this._indexByModel[view.model.cid];
                }

                // delete custom index
                _.any(this._indexByCustom, function (cid, key) {
                    if (cid === viewCid) {
                        delete this._indexByCustom[key];
                        return true;
                    }
                }, this);

                // remove the view from the container
                delete this._views[viewCid];

                // update the length
                this._updateLength();
                return this;
            },

            // Call a method on every view in the container,
            // passing parameters to the call method one at a
            // time, like `function.call`.
            call: function (method) {
                this.apply(method, _.tail(arguments));
            },

            // Apply a method on every view in the container,
            // passing parameters to the call method one at a
            // time, like `function.apply`.
            apply: function (method, args) {
                _.each(this._views, function (view) {
                    if (_.isFunction(view[method])) {
                        view[method].apply(view, args || []);
                    }
                });
            },

            // Update the `.length` attribute on this container
            _updateLength: function () {
                this.length = _.size(this._views);
            }
        });

        // Borrowing this code from Backbone.Collection:
        // http://backbonejs.org/docs/backbone.html#section-106
        //
        // Mix in methods from Underscore, for iteration, and other
        // collection related features.
        var methods = ['forEach', 'each', 'map', 'find', 'detect', 'filter',
            'select', 'reject', 'every', 'all', 'some', 'any', 'include',
            'contains', 'invoke', 'toArray', 'first', 'initial', 'rest',
            'last', 'without', 'isEmpty', 'pluck'];

        _.each(methods, function (method) {
            Container.prototype[method] = function () {
                var views = _.values(this._views);
                var args = [views].concat(_.toArray(arguments));
                return _[method].apply(_, args);
            };
        });

        // return the public API
        return Container;
    })(Backbone, _);

    return Base;
});