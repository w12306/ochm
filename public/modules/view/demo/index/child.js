define(function (require) {

    var Base = require('base');
    var M = require('model/demo/index/child');
    var tpl = require('tpl/demo/index/child.html');

    var View = Base.View.extend({
        template: _.template(tpl),
        initialize: function (options) {
            var t=this;
            t.model = new M({pars:options.params});
            this.render();
        }
    });
    return View;
});