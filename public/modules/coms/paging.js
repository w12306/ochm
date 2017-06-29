/*
 分页组件
 * */
define(function (require) {
    var _ = require('underscore');
    var B = require('backbone');
    var Base = require('base');
    var tpl = require('tpl/coms/paging.html');

    var PagingModel = Base.Model.extend({
        defaults: {
            currentPage: 1,
            firstPage: 1,
            lastPage: 1,
            totalRecords: 0,
            perPage: 20,
            pageInRange: 2,
            pageSetInRange: [],
            url:'',
            useStaticData: false,
            staticData: [],
            parseData: null,
            parseResponse: null
        }
    });

    var PagingCollection = Base.Collection.extend({
        initialize: function (options) {
            this.pagingInfo = new PagingModel(options);
            this.listenTo(this.pagingInfo, 'change', this.onModelChange);
        },
        parse: function (resp) {
            var parseResponse = this.info('parseResponse');
            if (!_.isFunction(parseResponse)) {
                return resp;
            }
            var result = parseResponse.call(this, resp);
            var total = parseInt(result.total, 10) || 0;
            this.info('totalRecords', total);
            return result.records || [];
        },
        parseData: function (page, perpage) {
            var parseData = this.info('parseData');
            if (!_.isFunction(parseData)) {
                return {page: page, perpage: perpage};
            }
            return parseData.call(this, page, perpage);
        },
        onModelChange: function (model) {
            if (model.hasChanged('currentPage') || model.hasChanged('totalRecords') || model.hasChanged('perPage')) {
                this.updateLastPage();
                this.updatePageSetInRange();
            }
        },
        //更新总页数
        updateLastPage: function () {
            var totalRecords = this.info("totalRecords");
            var perPage = this.info("perPage");
            var lastPage = Math.ceil(totalRecords / perPage) || 1;
            this.info('lastPage', lastPage);
        },
        //更新显示页码
        updatePageSetInRange: function () {
            var fp = this.info("firstPage");
            var lp = this.info("lastPage");
            var cp = this.info("currentPage");
            var n = Math.min(this.info("pageInRange"), Math.round(lp / 2));
            var pageSet = [], p1, p2, d1, d2;
            p1 = Math.max(cp - n, fp);
            p2 = Math.min(cp + n, lp);
            d1 = cp - p1;
            d2 = p2 - cp;
            if (d1 < n) {
                p2 = Math.min(p2 + n - d1, lp);
            }
            if (d2 < n) {
                p1 = Math.max(p1 - n + d2, fp);
            }
            for (var i = p1; i <= p2; i++) {
                pageSet.push(i);
            }
            this.info('pageSetInRange', pageSet);
        },
        //页数是否合法
        isPageValid: function (page) {
            return !isNaN(page) && page >= this.info('firstPage');
        },
        //获取/设置分页信息
        info: function (name, val) {
            if (arguments.length < 2) {
                if (typeof name === 'string') {
                    return this.pagingInfo.get(name);
                }
                return this.pagingInfo.toJSON();
            }
            if(typeof name === 'string') {
                return this.pagingInfo.set(name, val);
            }
            return this.pagingInfo.set(name);
        },
        //转到指定页
        page: function (page, options) {
            var _this = this;
            var d = $.Deferred();
            if (!this.isPageValid(page = parseInt(page,10))) {
                return d.rejectWith(_this,[page]);
            }

            var perPage = this.info("perPage"), records;
            if (this.info('useStaticData')) {
                var staticData = this.info("staticData") || [];
                records = staticData.slice((page - 1) * perPage, page * perPage);
                this.info('currentPage', page);
                this.set(records);
                records = _this.toJSON();
                _this.trigger('paging', page, records);
                d.resolveWith(_this, [page, records]);
            } else {
                var data = this.parseData(page, perPage);
                options = options || {};
                if (options.data) {
                    $.extend(data, options);
                }
                options = $.extend(options, {data: data});
                this.fetch(options).done(function () {
                    records = _this.toJSON();
                    _this.info('currentPage', page);
                    _this.trigger('paging', page, records);
                    d.resolveWith(_this, [page, records]);
                })
            }
            return d.promise();
        },
        //转到首页
        first: function (options) {
            var firstPage = this.info('firstPage');
            return this.page(firstPage, options);
        },
        //转到尾页
        last: function (options) {
            var lastPage = this.info('lastPage');
            return this.page(lastPage, options);
        },
        //转到上页
        prev: function (options) {
            var currentPage = this.info('currentPage');
            var firstPage = this.info('firstPage');
            var page = currentPage - 1;
            if (page >= firstPage) {
                return this.page(page, options);
            }
        },
        //转到下页
        next: function (options) {
            var currentPage = this.info('currentPage');
            var lastPage = this.info('lastPage');
            var page = currentPage + 1;
            if (page <= lastPage) {
                return this.page(page, options);
            }
        }
    });

    return Base.View.extend({
        template: _.template(tpl),
        events: {
            'click .js-page': 'pageOnEvent',
            'click .js-first': 'first',
            'click .js-last': 'last',
            'click .js-prev': 'prev',
            'click .js-next': 'next'
        },
        initialize: function () {
            var info = this.options.pagingInfo || {};
            this.template = this.options.template || this.template;
            this.collection = new (PagingCollection.extend({url: info.url}))(info);
            this.listenTo(this.collection, 'paging', this.render);
        },
        pageOnEvent: function (e) {
            var page = $(e.currentTarget).data('page');
            return this.page(page);
        },
        page: function (page, options) {
            return this.collection.page(page, options);
        },
        first: function (options) {
            if (options instanceof $.Event) {
                options = {};
            }
            return this.collection.first(options);
        },
        last: function (options) {
            if (options instanceof $.Event) {
                options = {};
            }
            return this.collection.last(options);
        },
        prev: function (options) {
            if (options instanceof $.Event) {
                options = {};
            }
            return this.collection.prev(options);
        },
        next: function (options) {
            if (options instanceof $.Event) {
                options = {};
            }
            return this.collection.next(options);
        },
        reload: function () {
            var page = this.collection.info('currentPage');
            return this.collection.page(page);
        },
        info: function () {
            return this.collection.info.apply(this.collection, arguments);
        }
    });
});