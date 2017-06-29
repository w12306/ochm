/*
 通用异步通信方法
 * */
define(function (require) {
    var _ = require('underscore');
    var B = require('backbone');
    var lang = require('lang/lres');
    require('ui/notice');

    //返回域
    var fields = ['status', 'info', 'data'];
    //成功状态码
    var successCode = ['success', 'tip_success'];
    //失败状态码
    var errorCode = ['error', 'nologin', 'noauth', 'tip_error'];

    //判断是否返回成功状态
    function isSuccess(code) {
        return _.indexOf(successCode, code) > -1;
    }

    //转译通信成功返回结果
    function parseSuccess(response, textStatus, jqXHR) {
        response = _.extend({}, response);

        var status = _.toRealString(response[fields[0]]).toLowerCase(),
            statusCode = _.toArray(_.union(successCode, errorCode)),
            resp = _.extend({}, response);

        if (_.indexOf(statusCode, status) == -1) {
            status = statusCode[0];
        }
        resp[fields[0]] = status;
        resp[fields[1]] = _.toRealString(response[fields[1]]);
        resp[fields[2]] = response[fields[2]];
        return resp;
    }

    //转译通信失败返回结果
    function parseError(jqXHR, textStatus, errorThrown) {
        var info, resp = {};
        textStatus = textStatus || 'unknown';
        info = lang.ajax[textStatus];
        resp[fields[0]] = textStatus;
        resp[fields[1]] = info;
        resp[fields[2]] = null;
        return resp;
    }

    //ajax请求完成后执行的方法
    function ajaxDone(response, callback) {
        var status = response[fields[0]],
            info = response[fields[1]],
            data = response[fields[2]];
        callback = _.bind(_.isFunction(callback) ? callback : $.noop, this);
        switch (status) {
            case 'nologin':
                info = info || lang.ajax.nologin;
                if ($.notice) {
                    $.notice(info, 'error');
                } else {
                    alert(info);
                }
                setTimeout(function(){
                    location.href = ST.URL.login;
                },1500);
                break;
            case 'noauth':
                info = info || lang.ajax.noauth;
                if ($.notice) {
                    $.notice(info, 'error');
                } else {
                    alert(info);
                }
                break;
            case 'tip_success':
                info = info || lang.ajax.success;
                if ($.notice) {
                    $.notice(info, 'success');
                } else {
                    alert(info);
                }
                callback(response);
                break;
            case 'tip_error':
                info = info || lang.ajax.error;
                if ($.notice) {
                    $.notice(info, 'error');
                } else {
                    alert(info);
                }
                callback(response);
                break;
            default:
                callback(response);
                break;
        }
    }

    //发送ajax请求
    function ajax(options) {
        var params = {
            type: 'get',
            dataType: 'json',
            contentType: 'application/x-www-form-urlencoded;charset=utf-8'
        };
        _.extend(params, options);

        params.success = null;
        params.error = null;

        var xhr = options.xhr = $.ajax(params)
            .done(function () {
                var result = parseSuccess.apply(this, arguments),
                    status = result[fields[0]],
                    callback = options[isSuccess(status) ? 'success' : 'error'];
                ajaxDone.call(this, result, callback);
            })
            .fail(function () {
                var result = parseError.apply(this, arguments);
                ajaxDone.call(this, result, options.error);
            });
        return xhr;
    }

    return {
        fields:fields.slice(0),
        successCode:successCode.slice(0),
        errorCode:errorCode.slice(0),
        isSuccess: isSuccess,
        parseSuccess: parseSuccess,
        parseError: parseError,
        ajaxDone: ajaxDone,
        ajax: ajax
    }

});