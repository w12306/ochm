/*
 * Module Name: selectbox
 * Module Require: jquery, underscore
 * Module Author: zhangshu
 * version: 1.0.0-pre
 */
define(function(require){
    return require("src/plugin/selectbox/js/selectbox");
});

define("src/plugin/selectbox/js/selectbox.defaults", [ "../tpl/selectbox.selection.html", "../tpl/selectbox.container.html", "../tpl/selectbox.result.html" ], function(require, exports, module) {
    var selection = require("../tpl/selectbox.selection.html");
    var container = require("../tpl/selectbox.container.html");
    var result = require("../tpl/selectbox.result.html");
    return function(_) {
        return {
            /*
             * 模板配置
             */
            tpl: _.template(container),
            tpl_result: _.template(result),
            tpl_selection: _.template(selection),
            /*
             * 外观配置
             */
            //主题样式
            cls: function() {
                return this.multiple ? "selectbox-tags" : "selectbox-dropdown";
            },
            //组样式
            groupCls: "caption",
            //单项（不属于任何组）样式
            onlyItemCls: "only",
            //项选中样式
            selectedCls: "selected",
            //项鼠标滑过样式
            hoverCls: "hover",
            //项获取焦点样式
            focusCls: "focus",
            //项禁用样式
            disabledCls: "disabled",
            //宽度
            width: function() {
                return this.multiple ? 225 : 150;
            },
            //层级（默认自适应）
            zIndex: "auto",
            //单个下拉项高度
            itemHeight: 30,
            //默认显示下拉项数（多于此数，则显示滚动条）
            displayItemCount: 6,
            /*
             * 数据配置
             */
            //选项数据
            data: null,
            //已选项
            selection: null,
            //多选数据分隔符（用于非select元素）
            separator: ",",
            /*
             * 字段配置
             */
            //id
            idField: "id",
            //文本
            textField: "text",
            //组
            groupField: "group",
            //禁用
            disabledField: "disabled",
            /*
             * 字段格式化
             */
            //项（默认显示textField）
            itemFormatter: function(data) {
                return data[this.option("textField")];
            },
            //组（默认显示groupField）
            groupFormatter: function(data) {
                return data[this.option("groupField")];
            },
            /*
             * 搜索配置
             */
            //是否启用搜索
            searchable: false,
            //是否启用实时查询
            iSearch: true,
            //是否使用本地搜索
            localSearch: true,
            //搜索提示
            searchTip: "请输入关键字",
            //空数据提示
            searchEmptyTip: "无结果",
            //搜索中提示
            searchLoadingTip: "搜索中...",
            //搜索出错提示
            searchErrorTip: "搜索出错",
            //ajax配置
            ajaxOption: {
                url: "",
                type: "get",
                dataType: "json"
            },
            //搜索间隔
            searchWait: 100,
            //搜索参数
            searchParams: function(keyword) {
                return {
                    text: keyword
                };
            },
            //搜索结果转译
            searchParser: function(response) {
                return response;
            },
            //自定义查询（用于本地数据搜索）
            mySearch: function(keyword, item) {
                var textField = this.option("textField");
                return new RegExp(keyword).test(item[textField]);
            },
            //自定义筛选（用于过滤关键字：返回非字符串则不触发查询）
            myFilter: function(keyword) {
                return _.trim(keyword);
            },
            /*
             * placeholder
             */
            //默认值占位符
            placeholder: null,
            //placeholder是否作为第一项数据
            placeholderAsData: false,
            //默认选中第一项
            firstDataAsDefault: false,
            /*
             * 事件接口
             */
            //创建后
            onCreated: null,
            //打开后
            onOpened: null,
            //关闭后
            onClosed: null,
            //选中前
            onBeforeSelect: null,
            //选中后
            onSelected: null,
            //选中项改变后
            onChanged: null,
            //选项数据改变后
            onDataChanged: null
        };
    };
});

define("src/plugin/selectbox/tpl/selectbox.selection.html", [], '<%if(!data || data.length==0){%>\n<%=this.placeholder%>\n<%}else{%>\n<%if(!this.multiple){%>\n<%=data.label%>\n<%}else{%>\n<ul>\n    <%_.each(data,function(d,i){%>\n    <%d = d || {};%>\n    <li>\n        <span><%=d.label%></span>\n        <a role="cancel" data-val="<%=d.id%>" class="close" href="javascript:;">&times;</a>\n    </li>\n    <%});%>\n</ul>\n<%}%>\n<%}%>');

define("src/plugin/selectbox/tpl/selectbox.container.html", [], '<div class="selectbox">\n    <div role="toggle" class="selectbox-toggle" tabindex="0">\n        <span role="selection" class="selectbox-label"></span>\n        <span class="selectbox-icon">\n          <i class="icon"></i>\n        </span>\n    </div>\n    <div role="panel" class="selectbox-panel" style="display: none">\n        <%if(this.option(\'searchable\')){%>\n        <div class="selectbox-search">\n            <input role="search-keyword" type="text" placeholder="<%=this.option(\'searchTip\')%>" autocomplete="off">\n        </div>\n        <div role="search-tip" class="selectbox-text" style="display: none"></div>\n        <%}%>\n        <div role="result" class="selectbox-menu"></div>\n    </div>\n</div>');

define("src/plugin/selectbox/tpl/selectbox.result.html", [], '<%var itemCls = _.bind(this.itemCls,this);%>\n<ul>\n<%_.each(data,function(d,i){%>\n    <%var group = d.group;%>\n    <%if(group){%>\n    <li role="item:group" data-val="<%=d.id%>" class="caption" title="<%=d.label%>" style="cursor: pointer">\n        <%=d.label%>\n    </li>\n    <%_.isArray(group) && _.each(group,function(item,i){%>\n    <%if(item.disabled){%>\n    <li role="item:disabled" data-val="<%=item.id%>" class="<%=itemCls(item)%>" title="<%=item.label%>"><%=item.label%></li>\n    <%}else{%>\n    <li role="item" data-val="<%=item.id%>" class="<%=itemCls(item)%>" title="<%=item.label%>" tabindex="-1">\n    <a href="javascript:;"><%=item.label%></a>\n    </li>\n    <%}%>\n    <%});%>\n\n    <%}else{%>\n\n    <%if(d.disabled){%>\n    <li role="item:disabled" data-val="<%=d.id%>" class="<%=itemCls(d)%>" title="<%=d.label%>"><%=item.label%></li>\n    <%}else{%>\n    <li role="item" data-val="<%=d.id%>" class="<%=itemCls(d)%>" title="<%=d.label%>" tabindex="-1">\n        <a href="javascript:;"><%=d.label%></a>\n    </li>\n    <%}%>\n\n    <%}%>\n<%});%>\n</ul>');

define("src/plugin/selectbox/js/selectbox", [ "underscore", "./selectbox.defaults" ], function(require, exports, module) {
    // 引入依赖
    if (!_) var _ = require("underscore");
    var defaults = require("./selectbox.defaults")(_);
    // 常量（命名空间，插件名，插件命名空间）
    var NAMESPACE = "bee";
    var PLUGIN_NAME = "selectbox";
    var PLUGIN_NS = NAMESPACE + "." + PLUGIN_NAME;
    var KEY = {
        TAB: 9,
        ENTER: 13,
        ESC: 27,
        LEFT: 37,
        UP: 38,
        RIGHT: 39,
        DOWN: 40
    };
    // 全局变量
    var $document = $(document);
    // 定义插件类
    function Selectbox(element, options) {
        this.el = element;
        this.$el = $(element);
        if (!this.$el.is("select,input:text,input:hidden,textarea")) {
            return;
        }
        Selectbox.__instance[this.cid = _.now()] = this;
        this.originalEvent = this.$el.is("select") ? "change" : "blur";
        this.defaults = Selectbox.defaults;
        this.options = $.extend({}, this.defaults, options);
        this.bindAll();
    }
    // 定义默认选项
    Selectbox.defaults = defaults;
    // 默认zIndex
    Selectbox.zIndex = 199;
    // 实例集合
    Selectbox.__instance = {};
    // 获取实例
    Selectbox.get = function(cid) {
        return Selectbox.__instance[cid];
    };
    // 隐藏其他
    Selectbox.hideOthers = function(cid) {
        var cIns = Selectbox.get(cid);
        _.each(Selectbox.__instance, function(ins) {
            ins != cIns && ins.togglePanel(0);
        });
        return this;
    };
    // 设置默认选项
    Selectbox.setDefaults = function(options) {
        $.extend(Selectbox.defaults, options);
        return this;
    };
    // 设置zIndex
    Selectbox.setZIndex = function(zIndex) {
        var old = Selectbox.zIndex;
        Selectbox.zIndex = parseInt(zIndex, 10) || old;
        return this;
    };
    // 扩展插件原型
    $.extend(Selectbox.prototype, {
        //初始化方法：插件初始化后自动调用
        _init: function() {
            //收集dom信息
            this.multiple = this.$el.attr("multiple");
            this.placeholder = this.option("placeholder") || this.$el.attr("placeholder");
            this.placeholderAsData = !this.multiple ? this.option("placeholderAsData") : false;
            //获取数据（优先配置信息）
            var data = this.option("data") || this.fetchDomData();
            var selection = this.option("selection") || this.fetchDomSelection();
            //配置搜索
            if (this.option("searchable") && this.option("iSearch")) {
                this.search = _.debounce(this.search, this.option("searchWait"));
            }
            //构建UI
            var mouseEnter = $.proxy(function(e) {
                $(e.currentTarget).addClass(this.option("hoverCls"));
            }, this);
            var mouseLeave = $.proxy(function(e) {
                $(e.currentTarget).removeClass(this.option("hoverCls"));
            }, this);
            this.$container = $(this.option("tpl")).addClass(this.option("cls")).css({
                width: this.option("width")
            }).on("click." + PLUGIN_NS, $.proxy(function(e) {
                e.stopPropagation();
                Selectbox.hideOthers(this.cid);
            }, this)).on("click." + PLUGIN_NS, '[role="toggle"]', $.proxy(function() {
                this.togglePanel();
            }, this)).on("click." + PLUGIN_NS, '[role="cancel"]', $.proxy(function(e) {
                e.stopPropagation();
                this.selectByIdOnEvent.apply(this, arguments);
            }, this)).on("click." + PLUGIN_NS, '[role="clear"]', $.proxy(function(e) {
                e.stopPropagation();
                this.clearSelection();
            }, this)).on("click." + PLUGIN_NS, '[role="item:group"]', $.proxy(this.selectByGroupOnEvent, this)).on("click." + PLUGIN_NS, '[role="item"]', $.proxy(this.selectByIdOnEvent, this)).on("mouseenter." + PLUGIN_NS, '[role="item"]', mouseEnter).on("mouseleave." + PLUGIN_NS, '[role="item"]', mouseLeave).on("focus." + PLUGIN_NS, '[role="item"]', $.proxy(this.focusItem, this)).on("blur." + PLUGIN_NS, '[role="item"]', $.proxy(this.unfocusItem, this)).on("keydown." + PLUGIN_NS, $.proxy(this.keydown, this)).on("keyup." + PLUGIN_NS, '[role="search-keyword"]', $.proxy(this.searchOnKeyup, this)).insertBefore(this.$el.hide());
            //点击空白处隐藏
            $document.on("click." + PLUGIN_NS, $.proxy(function() {
                this.togglePanel(0);
            }, this));
            this.setData(data, {
                silent: true,
                selection: selection
            });
            this.__opening = false;
            this.triggerHandler("created");
            delete this._init;
            return this;
        },
        /*
         * 数据操作
         */
        //转译选项数据
        parseChoiceData: function(data) {
            var idField = this.option("idField");
            var textField = this.option("textField");
            var groupField = this.option("groupField");
            var disabledField = this.option("disabledField");
            return {
                id: data[idField],
                text: data[textField],
                gid: data[groupField] || null,
                group: null,
                disabled: data[disabledField] || false,
                label: this.option("itemFormatter", [ data ]) || data[textField],
                attr: data
            };
        },
        //数据转换
        data2Result: function(data) {
            var groupField = this.option("groupField");
            var parseChoiceData = $.proxy(this.parseChoiceData, this);
            var result = [], temp = null;
            data = $.makeArray(data);
            //遍历项
            for (var i = 0, l = data.length; i < l; i++) {
                var g = data[i][groupField];
                if (g != null) {
                    if (g != temp) {
                        var group = $.map(data, function(d) {
                            if (d[groupField] == g) {
                                return parseChoiceData(d);
                            } else {
                                return null;
                            }
                        });
                        result.push({
                            id: g,
                            label: this.option("groupFormatter", [ data[i] ]) || g,
                            group: group
                        });
                        temp = g;
                    }
                } else {
                    result.push(parseChoiceData(data[i]));
                }
            }
            return result;
        },
        //获取/设置选项数据
        data: function(data, options) {
            if (arguments.length == 0) {
                return this.__data;
            }
            this.setData(data, options);
            return this;
        },
        //设置选项数据
        setData: function(data, options) {
            data = $.makeArray(data);
            options = options || {};
            if (this.placeholder && this.placeholderAsData) {
                var d = {
                    group: null,
                    disabled: false
                };
                d[this.option("idField")] = "";
                d[this.option("textField")] = this.placeholder;
                data.splice(0, 0, d);
            }
            var oldData = this.__data;
            this.__data = data;
            this.__result = this.data2Result(data);
            this.renderResult();
            //更新选中项
            var selection = [];
            if (!_.isUndefined(options.selection)) {
                selection = options.selection;
            } else if (data[0] && this.option("firstDataAsDefault")) {
                selection = data[0][this.option("idField")];
            }
            this.setSelection(selection, {
                silent: true
            });
            if (!options.silent) {
                this.triggerHandler("changed:data", [ data, oldData ]);
            }
        },
        //设置已选项
        setSelection: function(val, option) {
            var oldSelection = this.selection || [], toAdd = [], toRemove = [], num;
            val = $.makeArray(val);
            option = option || {};
            if ((num = val.length) > 0) {
                for (var i = 0; i < num; i++) {
                    var data = this.findItemById(val[i]);
                    if (data && !data.disabled) {
                        var j = $.inArray(val[i], oldSelection);
                        //判断值是否已选中
                        if (j > -1) {
                            //多选则删除
                            if (this.multiple) {
                                toRemove.push(oldSelection[j]);
                                oldSelection.splice(j, 1);
                            }
                        } else {
                            //单选则替换
                            if (!this.multiple) {
                                oldSelection = [];
                            }
                            toAdd.push(val[i]);
                        }
                    }
                }
            } else {
                toRemove = oldSelection.slice(0);
                //单选则替换
                if (!this.multiple) {
                    oldSelection = [];
                }
            }
            var selection = _.uniq(oldSelection.concat(toAdd));
            var items = _.map(selection, $.proxy(function(id) {
                return this.findItemById(id);
            }, this));
            var args = [ this.multiple ? items : items[0], toAdd, toRemove ];
            var isChanged = toAdd.length > 0 || toRemove.length > 0;
            if (!this.triggerHandler("before:select")) {
                return;
            }
            this.selection = selection;
            this.renderSelection();
            if (!option.silent) {
                this.triggerHandler("selected", args);
                if (isChanged) {
                    this.triggerHandler("changed", args);
                    this.$el.trigger(this.originalEvent, args);
                }
            }
        },
        //清空已选项
        clearSelection: function() {
            this.setSelection(null);
        },
        //由id获取项
        findItemById: function(id) {
            var data = this.__data, cond = {}, item;
            cond[this.option("idField")] = id;
            item = _.findWhere(data, cond) || null;
            item && (item = this.parseChoiceData(item));
            return item;
        },
        //由group获取项
        findItemsByGroup: function(group) {
            var groupField = this.option("groupField");
            var parseChoiceData = $.proxy(this.parseChoiceData, this);
            var data = this.__data;
            return $.map(data, function(item) {
                if (item[groupField] == group) {
                    return parseChoiceData(item);
                }
                return null;
            });
        },
        /*
         * 操作交互
         */
        //显隐选项面板
        togglePanel: function(toOpen) {
            if (toOpen == undefined || toOpen == null) {
                toOpen = this.$("panel").css("display") == "none";
            }
            if (this.__opening == !toOpen) {
                var zIndex = parseInt(this.option("zIndex"), 10);
                if (!zIndex) {
                    zIndex = Selectbox.zIndex;
                    toOpen && zIndex++;
                }
                this.__opening = !!toOpen;
                this.$container.css("zIndex", zIndex);
                if (toOpen) {
                    this.$("panel").show();
                    this.$("search-keyword").focus();
                    this.triggerHandler("opened");
                } else {
                    this.$("panel").hide();
                    this.clearSearch();
                    this.__stopSearching = true;
                    this.triggerHandler("closed");
                }
            }
        },
        //项选择
        selectById: function(id, options) {
            if (!this.multiple) {
                this.togglePanel(0);
                this.$("toggle").focus();
            }
            this.setSelection(id, options);
        },
        //事件触发：项选择
        selectByIdOnEvent: function(e) {
            var val = $(e.currentTarget).attr("data-val");
            this.selectById(val);
        },
        //组选择
        selectByGroup: function(group, options) {
            if (!this.multiple) return;
            var items = this.findItemsByGroup(group);
            var val = $.map(items, function(item) {
                return item.id;
            });
            this.setSelection(val, options);
        },
        //事件触发：组选择
        selectByGroupOnEvent: function(e) {
            var group = $(e.currentTarget).attr("data-val");
            this.selectByGroup(group);
        },
        //处理keydown事件
        keydown: function(e) {
            var allowKey = [ KEY.ENTER, KEY.ESC, KEY.LEFT, KEY.UP, KEY.RIGHT, KEY.DOWN ], reg = new RegExp("^(" + allowKey.join("|") + ")$");
            //TAB键关闭
            if (this.__opening && e.keyCode == KEY.TAB) {
                this.togglePanel(0);
                return;
            }
            if (!reg.test(e.keyCode)) return;
            e.preventDefault();
            e.stopPropagation();
            var focusCls = this.option("focusCls"), $items = this.$availItems(), i = $items.index($items.filter("." + focusCls)), l = $items.length;
            if (l == 0) return;
            //打开panel
            if (!this.__opening) {
                this.togglePanel(1);
                i = -1;
            } else {
                //esc键关闭panel
                if (e.keyCode == KEY.ESC) {
                    this.togglePanel(0);
                    return;
                }
            }
            //处理enter键选中
            if (e.keyCode == KEY.ENTER) {
                if (i > -1 && i < l) {
                    var id = $items.eq(i).attr("data-val");
                    this.selectById(id);
                }
                return;
            }
            //计算当前焦点位置
            switch (e.keyCode) {
              case KEY.LEFT:
              case KEY.UP:
                if (i > 0) {
                    i--;
                } else {
                    i == 0 && (i = l - 1);
                }
                break;

              case KEY.RIGHT:
              case KEY.DOWN:
                if (i < l - 1) {
                    i++;
                } else {
                    i == l - 1 && (i = 0);
                }
                break;

              default:
                i = 0;
            }
            $items.removeClass(focusCls).eq(i).focus().addClass(focusCls);
        },
        /*
         * 原生模拟
         */
        //读取dom数据
        fetchDomData: function() {
            var $options = this.$el.find("option, optgroup");
            var idField = this.option("idField");
            var textField = this.option("textField");
            var groupField = this.option("groupField");
            var disabledField = this.option("disabledField");
            var data = [], groups = [], i = 0;
            $options.each(function() {
                var $this = $(this);
                if ($this.attr("placeholder")) {
                    return true;
                }
                if ($this.is("optgroup")) {
                    i++;
                    groups.push($this.attr("label"));
                } else {
                    var in_group = $this.parent().is("optgroup"), item = {};
                    item[idField] = $this.val();
                    item[textField] = $this.text();
                    item[groupField] = in_group ? groups[i - 1] : null;
                    item[disabledField] = $this.is(":disabled");
                    data.push(item);
                }
            });
            return data;
        },
        //读取dom选中数据
        fetchDomSelection: function() {
            return (this.multiple || !this.placeholder ? this.$el.val() : this.$el.find("option[selected]").val()) || this.$el.attr("data-val");
        },
        //同步dom选项数据
        syncData: function() {
            if (!this.$el.is("select")) return;
            var result = this.__result;
            var arr = [];
            var formatItem = function(item) {
                var arr = [];
                arr.push('<option value="' + item.id + '"');
                arr.push(item.disabled ? " disabled" : "");
                arr.push(">" + item.text + "</option>");
                return arr.join("");
            };
            //select单选模式下默认选中第一项，需插入空选项
            if (this.placeholder && !this.placeholderAsData) {
                arr.push('<option placeholder="true" value=""></option>');
            }
            $.each(result, function(i, item) {
                var group = item.group;
                if (group) {
                    var children = [];
                    $.each(group, function(j, child) {
                        children.push(formatItem(child));
                    });
                    arr.push('<optgroup label="' + item.id + '">', children.join(""), "</optgroup>");
                } else {
                    arr.push(formatItem(item));
                }
            });
            this.$el.empty().append(arr.join(""));
        },
        //同步dom选中状态
        syncSelection: function() {
            var selection = this.selection, val;
            if (!this.$el.is("select")) {
                val = selection.join(this.option("separator"));
            } else {
                val = this.multiple ? selection : selection[0];
            }
            this.$el.val(val || "");
        },
        /*
         * 搜索相关
         */
        //切换搜索提示
        toggleTips: function(flag, text) {
            var $tips = this.$("search-tip");
            text && $tips.html(text);
            $tips[flag ? "show" : "hide"]();
        },
        //搜索关键字过滤
        keywordFilter: function(keyword) {
            return $.isFunction(this.options.myFilter) ? this.option("myFilter", [ keyword ]) : _.trim(keyword);
        },
        //清除搜索结果
        clearSearch: function() {
            if (this.option("searchable")) {
                var $sk = this.$("search-keyword");
                if ($sk.val() != "") {
                    return;
                }
                $sk.val("");
                this.toggleTips(0);
                this.renderResult();
                this.renderSelection();
            }
        },
        //按键搜索
        searchOnKeyup: function(e) {
            if (this.option("iSearch") || e.keyCode == KEY.ENTER) {
                this.doSearch();
            }
        },
        //处理搜索请求
        doSearch: function() {
            var keyword = this.keywordFilter(this.$("search-keyword").val());
            if (keyword) {
                this.toggleTips(1, this.option("searchLoadingTip"));
                this.__stopSearching = false;
                this.search(keyword);
            } else {
                this.clearSearch();
            }
        },
        //搜索
        search: function(keyword) {
            var localSearch = this.option("localSearch"), defer;
            //定义搜索延迟对象
            if (localSearch) {
                defer = $.Deferred();
            } else {
                var options = this.option("ajaxOptions"), settings = $.extend({
                    url: "",
                    type: "get",
                    dataType: "json"
                }, options);
                settings.data = this.option("searchParams", [ keyword ]);
                this.toggleTips(1, this.option("searchLoadingTip"));
                defer = $.ajax(settings);
            }
            //绑定搜索结果处理方法
            defer.done($.proxy(function(data) {
                this.toggleTips(0);
                if (this.__stopSearching) {
                    this.__stopSearching = false;
                } else {
                    if (!this.option("localSearch")) {
                        data = this.option("searchParser", [ data ]) || data;
                    }
                    this.showResult(1, data);
                }
            }, this)).fail($.proxy(function() {
                this.toggleTips(0);
                if (this.__stopSearching) {
                    this.__stopSearching = false;
                } else {
                    this.showResult(0, this.option("searchErrorTip"));
                }
            }, this));
            //根据配置执行本地搜索
            if (localSearch) {
                var data = this.__data;
                var result = $.grep(data, $.proxy(function(item) {
                    return this.option("mySearch", [ keyword, item ]);
                }, this));
                if (result === false) {
                    defer.reject();
                } else {
                    defer.resolve(result);
                }
            }
        },
        //显示搜索结果
        showResult: function(flag, data) {
            if (flag) {
                if (data.length > 0) {
                    this.toggleTips(0);
                    data = this.data2Result(data);
                } else {
                    this.toggleTips(1, this.option("searchEmptyTip"));
                }
                this.renderResult(data);
            } else {
                this.toggleTips(1, data);
            }
        },
        /*
         * dom相关
         */
        //获取role的jquery对象
        $: function(role) {
            return this.$container.find('[role="' + role + '"]');
        },
        //获取选项的jquery对象
        $items: function() {
            return this.$("result").find('[role^="item"]');
        },
        //获取可用选项的jquery对象
        $availItems: function() {
            return this.$("result").find('[role="item"]');
        },
        //载入待选项
        renderResult: function(data) {
            this.$("result").html(this.option("tpl_result", [ {
                data: data || this.__result
            } ]));
            //同步选中状态
            this.toggleSelected();
            //调整高度
            this.adjustHeight();
            //同步原生dom
            this.syncData();
        },
        //载入已选项
        renderSelection: function() {
            var data = _.map(this.selection, $.proxy(function(id) {
                return this.findItemById(id);
            }, this));
            this.$("selection").html(this.option("tpl_selection", [ {
                data: this.multiple ? data : data[0]
            } ]));
            //同步选中状态
            this.toggleSelected();
            //同步原生dom
            this.syncSelection();
        },
        //获取项样式
        itemCls: function(data) {
            var cls = [];
            if (typeof data === "string") {
                data = this.findItemById(data);
            }
            if (!data.group) {
                !data.gid && cls.push(this.option("onlyItemCls"));
            } else {
                cls.push(this.option("groupCls"));
            }
            if (data.disabled) {
                cls.push(this.option("disabledCls"));
            }
            return cls.join(" ");
        },
        //切换选中样式
        toggleSelected: function() {
            var $items = this.$availItems();
            var cls = this.option("selectedCls");
            var selection = this.selection;
            $items.removeClass(cls).filter(function() {
                return $.inArray($(this).attr("data-val"), selection) > -1;
            }).addClass(cls);
        },
        //附加获取焦点样式
        focusItem: function(e) {
            $(e.currentTarget).addClass(this.option("focusCls"));
        },
        //去掉获取焦点样式
        unfocusItem: function() {
            this.$availItems().removeClass(this.option("focusCls"));
        },
        //面板高度自适应
        adjustHeight: function() {
            var actualCount = this.$items().length;
            var displayCount = Math.min(actualCount, parseInt(this.option("displayItemCount"), 10) || 0);
            var itemHeight = parseInt(this.option("itemHeight"), 10) || 0;
            this.$("result").css("height", Math.round(itemHeight * displayCount));
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
    var allow = [ "defaults", "zIndex", "setDefaults", "setZIndex" ];
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
                $(this).data(PLUGIN_NS, instance = new Selectbox(this, options));
                instance._init();
            }
        });
        return returnValue;
    };
    $.fn[PLUGIN_NAME].Constructor = Selectbox;
    $.fn[PLUGIN_NAME].noConflict = function() {
        $.fn[PLUGIN_NAME] = old;
        return this;
    };
    $.each(allow, function() {
        $.fn[PLUGIN_NAME][this] = Selectbox[this];
    });
    return Selectbox;
});
