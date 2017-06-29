define(function (require) {

    var Base = require('base');
    var tpl = require('tpl/demo/index.html');
    var M = require('model/demo/index');
    var Child= require('view/demo/index/child');

    var View = Base.View.extend({
        template: _.template(tpl),
        ui: {
            'child': '[data-view="child"]'
        },
        initialize: function (options) {
            var t=this;
            t.model = new M({pars:options.params});
            this.render();
        },
        afterRender: function () {
            var child = new Child({
                el: this.$ui.child
            });
            this.addChildView(child, 'child');
        }
    });
    return function (options) {
        return new View(options);
    }
});