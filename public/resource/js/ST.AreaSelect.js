
ST.AreaSelect= function (id, ops) {
    //默认配置
    var _config = {
        dataSource: "",                        //数据源
        idkey: 'id',                                    //标识键名
        template: 'common_areaselect'                  //模板(为空则为页面输出)
    };
    return {
        /*
         初始化
         */
        init: function (id, ops) {
            var t = this;
            t.config = _config;
            $.extend(t.config, ops);
            t.Jid = $('#' + id);
            t._setup();
            delete t.init;
            return t;
        },
        /*
         初始化设置
         */
        _setup: function () {
            var t = this, c = t.config;
            if(window.CityData){
                t._renderUI();
            }else{
                ST.getJs("ST.CityData", function () {
                    t._renderUI();
                });

            };
        },
        /*
        *  构建展示界面
        * */
        _renderUI:function(){
            var t = this, c = t.config;
            t.Jid.html(ST.JTE.fetch(c.template).getFilled({data: c.dataSource || window.CityData}));
            t._initEvents();
        },
        /*
         初始化事件
         */
        _initEvents: function () {
            var t = this;
            var _evHandler = function (e) {
                var $this = $(this), cmd = $this.data('cmd');
                if (!cmd) return;
                var pars = $this.data('pars');
                t[cmd] && t[cmd].call(t, e, pars);
            };
            t.Jid.on('click', '[data-cmd]', _evHandler);
            return t;
        },
        /*
         全选/取消全选
         @pars
         e 事件参数
         */
        checkAll: function (e) {
            var t = this, element = e.target;
            if (!element) return t;
            var $this = $(element);
            t._checkAll($this, !!$this.attr('checked'));
            return t;
        },
        _checkAll: function (o, flag) {
            var t = this;
            //目前写死
            var dd= o.closest("dl").find(".regionalList-dd");
            dd.find("input[type='checkbox']").each(function () {
                var $this = $(this);
                $this.attr('checked', flag);
            });
            return t;
        },
        getSelected:function(){
            var t = this,vals=[];
            //仅选取具备名称的checkbox
            t.Jid.find("input[type='checkbox']").each(function(){
                if($(this).attr("name") && $(this).attr("checked")) vals.push(this.value);
            });
            return vals.join(",");
        }
    }.init(id, ops)
};