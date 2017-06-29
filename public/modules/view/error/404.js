define(function (require) {
    var _ = require('underscore');
    var B = require('backbone');
    var Base = require('base');
    var tpl = require('tpl/error/404.html');
    var View = Base.View.extend({
        template: _.template(tpl),
        initialize: function () {
            this.render();
        }
    });
    return function (options) {
        return new View(options);
    }
});