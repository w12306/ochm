/*
 静态语言资源包
 * */
define(function(require) {
    var language = ST.Language = "zh-CHS";
    var LRes = ST.LRes = (function(k){
        return{
            //辅助提示
            postSucc:{'zh-CHS':'提交成功'}[k],
            reqStop:{'zh-CHS':'已取消请求'}[k],
            reqTmout:{'zh-CHS':'请求超时 请稍后再试'}[k],
            e404:{'zh-CHS':'网络已断开'}[k],
            e500:{'zh-CHS':'服务器错误'}[k],
            tip:{'zh-CHS':'提示'}[k],
            ok:{'zh-CHS':'确定'}[k],
            cancle:{'zh-CHS':'取消'}[k],
            delAsk:{'zh-CHS':'您确认删除吗'}[k],
            subAsk:{'zh-CHS':'您确定提交吗？'}[k],
            delTip:{'zh-CHS':'操作确认'}[k],
            opTip:{'zh-CHS':'操作确认'}[k],
            opFailed:{'zh-CHS':'操作失败'}[k],
            opSuccess:{'zh-CHS':'操作成功'}[k],
            logOut:{'zh-CHS':'登出成功'}[k],
            serverBusy:{'zh-CHS':'服务器忙 请稍后再试'}[k],
            loadingTip:{'zh-CHS':'正在努力的加载...'}[k],
            //资源错误文本
            NeedTemp:{'zh-CHS':'需要引入通用模板文件！'}[k],
            RequireFail:{'zh-CHS':'依赖文件加载失败！'}[k],
            //扩展表单错误验证
            FormErrorCommon: {'zh-CHS': '验证失败!'}[k],
            FormErrorMaxLength: {'zh-CHS': '长度{0}到{1}之间！'}[k],
            FormErrorRange: {'zh-CHS': '范围{0}到{1}之间！'}[k],
            FormErrorNumber: {'zh-CHS': '请输入数值！'}[k],
            FormErrorTagNumber:{'zh-CHS':'最多{0}个标签！'}[k],
            FormErrorTagLength:{'zh-CHS':'单个标签不能超过{0}个字符！'}[k],
            FormErrorRadio:{'zh-CHS':'请至少选择其中一项！'}[k],
            FormErrorChecked:{'zh-CHS':'未选中！'}[k],
            FormErrorLeast:{'zh-CHS':'至少填写{0}项！'}[k],
            //常用静态文本
            Require:{'zh-CHS':'必须'}[k],
            Equal:{'zh-CHS':'等于'}[k],
            LessOrEqual:{'zh-CHS':'小于等于'}[k],
            Less:{'zh-CHS':'小于'}[k],
            GreaterOrEqual: {'zh-CHS':'大于等于'}[k],
            Greater: {'zh-CHS':'大于'}[k],
            NotEqual:{'zh-CHS':'不等于'}[k],
            IsVerify:{'zh-CHS':'正在进行验证，请稍候！'}[k],
            IsSubmit:{'zh-CHS':'正在提交，请稍候！'}[k],
            VerifyFail:{'zh-CHS':'验证失败！'}[k],
            loadingData:{'zh-CHS':'正在努力的读取数据中...'}[k],
            //登陆提示文本
            LoginErro:{'zh-CHS':{
                "1":"登陆成功",
                "0":"登录失败",
                "-1":"验证码错误",
                "-2":"用户名不存在",
                "-3":"账户被删除",
                "-4":"账户被锁定",
                "-5":"输入密码错误超过5次,账户被锁定",
                "-6":"密码错误"
            }}[k],
            commonTip:{'zh-CHS':{
                "1":"保存成功",
                "0":"保存失败"
            }}[k],
            //ajax提示
            ajax: {
                'success': '请求成功',
                'error': '请求出错',
                'nologin': '请先登录！',
                'noauth': '没有权限！',
                'timeout': '请求超时',
                'abort': '请求中断',
                'parsererror': '编译错误',
                'unknown': '未知错误'
            },
            //用于beeui验证插件
            Validator:{'zh-CHS':{
                required: "必填",
                either: "同时填写或同时不填写",
                least: "至少{0}项",
                most: "至多{0}项",
                number: "请输入数字",
                chinese: "请输入汉字",
                letter: "请输入字母",
                email: "请输入正确的邮箱地址",
                integer: ["请输入{0}", {
                    "*": "整数",
                    "0": "0",
                    "+": "正整数",
                    "+0": "正整数或0",
                    "-": "负整数",
                    "-0": "负整数或0"
                }],
                decimal: ["请输入{0},保留{1}位小数", {
                    "*": "小数",
                    "+": "正小数",
                    "-": "负小数"
                }],
                length: "请输入{0}~{1}个字符",
                range: ["请输入数值", "范围{0}{1},{2}{3}"],
                equal: "请输入等于{0}的值",
                nequal: "请输入不等于{0}的值",
                match: "验证失败",
                remote: ["远程验证失败", {
                    loading: "正在验证中...",
                    ok: ""
                }]
            }}[k]
        }
    }(ST.Language));

    return LRes;
});