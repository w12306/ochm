/*
盛天单点登录
* */
define(function (require) {
    var _ = require('underscore');
    var Base = require('base');
    var A = require('coms/async');

    return Base.Model.extend({
        defaults: {
            isLogin: false,
            enterInfo: '',
            token: {},
            userInfo:{}
        },
        //检测登录
        checkLog: function () {
            var _this = this;
            var enterInfo = this.get('enterInfo');
            A.ajax({
                url: ST.ACTION.GETENTERINFO,
                success: function (resp) {
                    _this.set('enterInfo', resp.data);
                    _this._checkStatus();
                }
            });
        },
        //检测登录状态
        _checkStatus: function () {
            var _this = this;
            var enterInfo = this.get('enterInfo');
            if (!enterInfo) {
                return;
            }
            A.ajax({
                url: ST.ACTION.SSOLOGSTATUS,
                data: enterInfo,
                dataType: "jsonp",
                jsonp: "stcallback",
                success: function (resp) {
                    _this.set('isLogin', true);
                    _this.set('token', resp.data);
                    _this._verifyToken();
                },
                error: function () {
                    _this.set('isLogin', false);
                    _this.logout();
                }
            });
        },
        //检测站点token
        _verifyToken: function () {
            var _this = this;
            var token = this.get('token');
            if (!token.UID) {
                return;
            }
            A.ajax({
                url: ST.ACTION.VERIFYTOKEN,
                data: _.toParamString(token).toLowerCase(),
                success: function (resp) {
                    _this.set('userInfo',resp.data);
                    _this.trigger('login:success', resp);
                },
                error: function (resp) {
                    _this.trigger('login:error', resp);
                }
            });
        },
        //登录
        login: function (userInfo) {
            var _this = this;
            var enterInfo = this.get('enterInfo');
            userInfo = _.toParamString(userInfo);
            A.ajax({
                url: ST.ACTION.SSOLOGIN,
                dataType:'jsonp',
                jsonp: 'stcallback',
                data: [userInfo, enterInfo].join('&'),
                success: function (resp) {
                    _this.set('isLogin', true);
                    _this.set('token', resp.data);
                    _this._verifyToken();
                },
                error: function (resp) {
                    _this.trigger('login:error', resp);
                }
            })
        },
        //登出
        logout: function () {
            var _this = this;
            var result = {};
            $.when(A.ajax({
                url: ST.ACTION.LOGOUT,
                success: function (resp) {
                    result.site = resp;
                },
                error: function(resp) {
                    result.site = resp;
                }
            }), A.ajax({
                url: ST.ACTION.SSOLOGOUT,
                dataType:'jsonp',
                jsonp: 'stcallback',
                success: function (resp) {
                    result.sso = resp;
                },
                error: function(resp) {
                    result.sso = resp;
                }
            })).done(function () {
                _this.trigger('logout:success', result);
            }).fail(function (j) {
                _this.trigger('logout:error', result);
            });
        }
    });
});