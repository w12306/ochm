<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>业务管理平台-ABMP</title>
    <link rel="shortcut icon" href="resource/images/favicon.ico">
    <link href="resource/css/ST_admin.css" type="text/css" rel="stylesheet"/>
    <link href="resource/css/login_style.css" type="text/css" rel="stylesheet"/>
    <script src="resource/js/jquery-min.js"></script>
    <script src="resource/js/ST.Config.js"></script>
    <script src="resource/js/ST.js"></script>
    <script>
        $.extend(ST.PATH, {
            VCODE: "/VerifyCodeServlet",
            JSTMP: "resource/jsTemplate/"
        });
    </script>
</head>
<body class="body_login">
    <div class="mod_login">
        <div class="login_bd">
            <div class="login_form">
                <div class="lf_hd">业务管理平台-ABMP</div>
                <div class="lf_bd">
                    {!! Form::open([
                        'url' => action('Admin\\AuthController@postLogin'),
                        'login_form' => 'login_form',
                        'name' => 'login_form',
                        'method' => 'POST',
                        'ajaxpost' => 'true',
                        'stverify' => 'true',
                        'onsubmit' => 'return false;',
                        'errtar' => 'js-err-tip',

                    ]) !!}
                    <ul class="lf_fm">
                        <li>
                            <div class="lf_div">
                                <label class="lf_yhm">用户名</label>
                                <input type="text" name="username" placeholder="用户名" class="lf_ipt" opt="rq ml" ml="1-32" maxlength="32" byteml="true" emsg="请输入用户名"/>
                            </div>
                        </li>
                        <li>
                            <div class="lf_div">
                                <label class="lf_mm">密码</label>
                                <input type="password" onpaste="return false" onselectstart="return false" name="password" placeholder="密码" class="lf_ipt" opt="rq ml" ml="1-16" maxlength="16" byteml="true" emsg="请输入密码"/>
                            </div>
                        </li>
                        <li class="lf_error_show" id="js-err-tip"></li>
                    </ul>
                    <button type="submit" href="javascript:;" class="login_btn txt_hide">登录</button>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="login_info" style="display: none!important">
                <h2>携手如易算 您将获得:</h2>
                <ul>
                    <li class="li_1">“简单”、“智能”、“安全”的优秀计费产品</li>
                    <li class="li_2">全面技术支持：7*16小时“乐+”服务</li>
                    <li class="li_3">良好的沟通互动，提供地区用户差异化订制与服务</li>
                    <li class="li_4">完整的产品宣传资料，新产品试用权限</li>
                    <li class="li_5">为合作伙伴提供网站的宣传与推广空间</li>
                </ul>
            </div>
            <div class="login_rys">
                <div class="rys_ico">
                    <!-- <div class="rys_1"><i></i><p>好用</p><span></span></div> -->
                    <div class="rys_2"><i></i>

                        <p>共赢</p><span></span></div>
                    <div class="rys_3"><i></i>

                        <p>极致</p><span></span></div>
                    <div class="rys_4"><i></i>

                        <p>开放</p><span></span></div>
                    <!-- <div class="rys_5"><i></i><p>无广告</p><span></span></div> -->
                </div>
                <div class="rys_bd">
                    <div class="rys_logo"></div>
                </div>
                <div class="rys_bg"></div>
            </div>
        </div>
    </div>
    <div class="footer login_footer">
        <div class="footer_bd">&copy;湖北盛天网络技术股份有限公司版权所有 鄂B2-20110110</div>
    </div>
    <script src="resource/js/ST.LRes.js"></script>
    <script src="resource/js/ST.Regs.js"></script>
    <script src="resource/js/ST.Vcode.js"></script>
    <script src="resource/js/ST.Verify.js"></script>
    <script>
        $.extend(ST, {
            jsTemplates: ['common'],
            todoList   : function () {
                var t      = this;
                ST.CurVode = new ST.Vcode({
                    controlID: 'js-form-code',
                    displayID: 'js-form-vcode',
                    params   : {
                        action: "login"
                    }
                });

                //ie8 清除帐号密码记录
                $("form").each(function () {
                    this.reset();
                });

                //修正 ie placeholder属性闪现消失bug，不支持密码域
                if ($.browser.msie) {
                    var b = $("#login_form").find("*[name]");
                    b.each(function (i, v) {
                        var a = $(this);
                        if (a.attr("placeholder")) {
                            if (a.val() == "") a.addClass("text-gray").val(a.attr("placeholder"));
                            a.unbind('focus.ie').bind('focus.ie', function () {
                                if (a.val().t() == a.attr("placeholder"))
                                    a.val("").removeClass("text-gray");
                            }).unbind('blur.ie').bind('blur.ie', function () {
                                if (a.val().t() == "")
                                    a.val(a.attr("placeholder")).addClass("text-gray");
                            })
                        }
                    })
                }

                $("[name='password']").on("blur", function () {
                    if ($.Lang.Browser.isIE && $("[name='password']").val() == "密码") {
                        $("[name='password']").val("");
                    }
                });

                if ($.Lang.Browser.isIE) {
                    $("[name='password']").val("");
                }
            }
        });

        ST.init();
    </script>
</body>
</html>
