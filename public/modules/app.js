//单页应用入口
define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');
    var Popup = require('ui/popup');
    var lang = require('lang/lres');

    require('lib/ST');

    var Router = Backbone.Router.extend({
        //路由规则参考backboneAPI
        routes: {
            '': 'index',	   //默认到首页
            ':module(/:action)(/*condition)': 'loadModule',
            'base': 'error',
            '404': "error",
            "*error": "error"
        },
        /*错误模块,404等*/
        error: function (action) {
            if (!action) action = 'index';
            this.loadModule('error', action);
            return false;
        },
        /*初始化,预留做登录用户检测*/
        initialize: function () {
            //内容区域
            this.$container = $('#js-container');
        },
        /*首页模块*/
        index: function () {
            this.loadModule('index', 'index');
        },
        pars:"",//页面参数
        /*其他模块*/
        loadModule: function (module, action, condition) {
            var _this = this, options = {}, view, path,pars="", $page;

            !module && (module = "index");
            !action && (action = "index");
            !condition && (condition = '');
            path = [module, action].join('/');

            //配置选项
            options = {"module": module, action: action, condition: condition, params: {}};

            //参数获取转换   将参数字符串'a:123/b:456'转换为json对象{a:123, b:456}
            if (typeof condition == 'string' && /\:/g.test(condition), condition = _.trim(condition)) {
                var arr = condition.split('/');
                var result = {};
                _.each(arr, function (i) {
                    var temp = i.split(':');
                    result[_.trim(temp[0])] = _.trim(temp[1]);
                });
                options.params = result;
            } else {
                //参数格式hash 或者直接为json对象
                options.params = condition || {};
            }

            pars = condition;

            //过滤当前页面
            if (App.currentPath == path) {
                //参数对比
                if(_this.pars != pars){
                    //记录当前页面参数
                    _this.pars = pars;
                    //通知视图参数变化
                    App.currentView.changePars && App.currentView.changePars(options.params);
                }
                return;
            }

            //销毁所有弹出层
            Popup.destroyAll();
            //显示当前页面
            $page = this.showPage(path);
            //记录当前页面id
            App.currentPath = path;
            //记录当前页面参数
            _this.pars = pars;
            //设置容器
            options.el = $page;


            //加载模块
            if (!App.ViewCreators[path]) {
                require.async('view/' + path, function (callback) {
                    if (callback) {
                        //单页模式下,视图切换后需回到顶部
                        window.scrollTo(0, 0);
                        App.ViewCreators[path] = callback;
                        _this.createCurrentView(options);
                    } else {
                        //404
                        _this.error('404');
                    }
                });
            } else {
                this.createCurrentView(options);
            }

        },
        //创建页面View
        createCurrentView: function (options) {
            var path = App.currentPath;
            var creator = App.ViewCreators[path];
            App.currentView = _.isFunction(creator) ? creator(options) : creator;
        },
        //显示当前页面
        showPage: function (path) {
            var id, $page;
            if(ST.singlePage){
                id = 'js-page';
                //销毁当前view
                this.destroyView(); 
            }else{
                id = path.replace(/\//g,'_');
                //隐藏其他view
                this.$container.children().hide();
            }
            $page = $('#' + id);
            //创建/显示当前页面
            if ($page.length == 0) {
                $page = $('<div/>').attr({
                    'data-role': 'page',
                    'id': id
                }).html(lang.loadingTip).appendTo(this.$container);
            }else{
                $page.show();
            }
            return $page;
        },
        //销毁当前view
        destroyView: function () {
            var view = App.currentView;
            //若当前view存在则销毁之（保证每个页面是全新的）
            if (view) {
                if (_.isFunction(view.close)) {
                    //扩展自Base.View的view
                    view.close();
                } else {
                    //普通Backbone view
                    view.remove();
                }
            }
        }
    });


    //定义全局变量App
    window.App = {
        //当前页面视图路径
        currentPath: null,
        //当前页面视图
        currentView: null,
        //视图创建器
        ViewCreators: {},
        //视图
        Views: {},
        //模型
        Models: {},
        //集合
        Collections: {},
        //初始化方法
        initialize: function () {
            if (!window.App.Router) {
                window.App.Router = new Router();
                Backbone.history.start();
            }
        }
    };

    exports.init = App.initialize;
});