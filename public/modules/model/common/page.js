define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            "total":"0",
            "perpage":"20",
            "curpage":"1",
            "totalpage":"0"
        },
        initialize: function(data) {
            console.log(data)
        },
        parse:function(res){
            return res;
        }
    });
});