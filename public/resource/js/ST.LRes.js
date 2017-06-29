/*
 静态语言资源包
 注意表单使用全角
 */
$.extend(ST.LRes, (function (k) {
  return{
    //扩展表单错误验证
    Require:{'zh-CHS':'必填'}[k],
    FormErrorCommon: {'zh-CHS': '验证失败!'}[k],
    FormErrorMaxLength: {'zh-CHS': '长度{0}到{1}之间！'}[k],
    FormErrorRange: {'zh-CHS': '范围{0}到{1}之间！'}[k],
    FormErrorNumber: {'zh-CHS': '请输入数值！'}[k],
    FormErrorTagNumber:{'zh-CHS':'最多{0}个标签！'}[k],
    FormErrorTagLength:{'zh-CHS':'单个标签不能超过{0}个字符！'}[k],
    FormErrorRadio:{'zh-CHS':'请至少选择其中一项！'}[k],
    FormErrorChecked:{'zh-CHS':'未选中！'}[k],
    FormErrorLeast:{'zh-CHS':'至少填写{0}项！'}[k],
    isVerify:{'zh-CHS':'正在远程验证中...'}[k]
  }
}(ST.Language)));