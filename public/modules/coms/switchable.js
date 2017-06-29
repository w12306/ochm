/*
 可切换组件
 * */
define(function (require) {
    var _ = require('underscore');
    var B = require('backbone');
    var Base = require('base');

    var Model = Base.Model.extend({
        defaults: {
            current: -1,
            triggerOn: 'mouseover',
            container: null,
            itemEl: '.js-switchable-item',
            itemCls: 'current',
            ctrlEl: '.js-switchable-ctrl',
            ctrlCls: 'current',
            autoPlay: false,
            autoTime: 2000,
            animation: false
        }
    });

    return Base.View.extend({
        initialize: function () {
            var model = new Model(this.options.config);
            var current = model.get('current');
            var triggerOn = model.get('triggerOn');
            var ctrlEl = model.get('ctrlEl');
            //定义model
            this.model = model;
            //定义目标容器
            this.$container = $(model.get('container') || this.el);
            //事件初始化
            this.$el.on(triggerOn, ctrlEl, $.proxy(this.switchItemOnEvent, this));
            if (model.get('autoPlay')) {
                this.$el.on('mouseenter', $.proxy(this.stop, this))
                    .on('mouseleave', $.proxy(this.start, this));
                this.play();
            }
        },
        switchItemOnEvent: function (e) {
            var $el = $(e.currentTarget);
            var i = $el.data('index') || 0;
            this.switchItem(i);
        },
        switchItem: function (i) {
            if (this.isCurrent(i = parseInt(i, 10))) {
                return;
            }
            var _this = this;
            var old = this.model.get('current');
            var newInfo = this.getInfoByIndex(i);
            var oldInfo = this.getInfoByIndex(old);
            this.model.set('current', i);
            this.activateCtrl(i);
            this.activateItem(i);
            this.animate(newInfo.item, oldInfo.item, function () {
                _this.trigger('switch:item', newInfo, oldInfo);
            });

        },
        animate: function (newItem, oldItem, callback) {
            var animation = this.model.get('animation');
            if (typeof animation === 'function') {
                animation.apply(this, newItem, oldItem);
            } else {
                var $new = $(newItem);
                var $old = $(oldItem);
                var current = this.model.get('current');
                switch (animation) {
                    case 'fade':
                        $old.stop().animate({opacity: 0});
                        $new.css({opacity: 0}).stop().animate({opacity: 1}, function () {
                            $.isFunction(callback) && callback();
                        });
                        break;
                    case 'slide':
                        //todo
                        break;
                    default :
                        $old.hide();
                        $new.show();
                        $.isFunction(callback) && callback();
                        break;
                }
            }
        },
        activateItem: function (i) {
            var $items = this.$items();
            var $item = $items.eq(i);
            var itemCls = this.model.get('itemCls');
            if ($item.hasClass(itemCls)) {
                return;
            }
            $items.removeClass(itemCls).eq(i).addClass(itemCls);
        },
        activateCtrl: function (i) {
            var $ctrls = this.$ctrls();
            var $ctrl = $ctrls.eq(i);
            var ctrlCls = this.model.get('ctrlCls');
            if ($ctrl.hasClass(ctrlCls)) {
                return;
            }
            $ctrls.removeClass(ctrlCls).eq(i).addClass(ctrlCls);
        },
        play: function () {
            if (this.__isStop) {
                return;
            }
            var _this = this;
            var time = this.model.get('autoTime');
            var i = this.model.get('current') || 0;
            var len = this.$items().length;
            this.__timer = setTimeout(function () {
                i++;
                if (i >= len) {
                    i = 0;
                }
                _this.switchItem(i);
                _this.play();
            }, time);
        },
        start: function () {
            this.__isStop = false;
            this.play();
        },
        stop: function () {
            clearTimeout(this.__timer);
            this.__isStop = true;
        },
        isCurrent: function (i) {
            return i == this.model.get('current');
        },
        $items: function () {
            var itemEl = this.model.get('itemEl');
            return this.$container.find(itemEl);
        },
        $ctrls: function () {
            var ctrlEl = this.model.get('ctrlEl');
            return this.$(ctrlEl);
        },
        getInfoByIndex: function (i) {
            return {i: i, ctrl: this.$ctrls().eq(i), item: this.$items().eq(i)};
        }
    });
});