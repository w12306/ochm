define(function (require) {

    var Base = require('base');
    var tpl = require('tpl/index/index.html');

    var View = Base.View.extend({
        template: _.template(tpl),
        initialize: function () {
            this.render();
        },
        afterRender: function () {

        }
    });
    return function (options) {
        return new View(options);
    }
});