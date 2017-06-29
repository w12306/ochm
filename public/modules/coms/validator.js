/*
 验证组件
 封装自beeui/validator
 * */
define(function (require) {
    var regs = require('lang/regs');
    var lang = require('lang/lres');
    var Validator = require('ui/validator');

    //自定义语言包
    Validator.setLang(lang.Validator);
    //自定义正则
    Validator.setRegexp(regs.Validator);
    //自定义验证规则
    Validator.setRules({

    });
    
    return Validator;
});