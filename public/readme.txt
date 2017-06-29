=========项目信息========== 
项目名称：ABMP业务后台
当前版本：
产品负责人：吕桂莹
设计负责人：
重构负责人：尹传武
前端负责人：陆玉婷
后端负责人：刘侃，杨威
运维负责人：

=========访问地址==========
【本地环境】
http://abmp.tnt.com.loc/htmls/
【联调环境】

【测试环境】

【线上环境】


=========SVN=============
【权限开通】刘侃
【SVN地址】svn://192.168.5.2/ABMP

=========hosts=============
#广告业务后台本地host
127.0.0.1  abmp.tnt.com.loc

=========httpd-vhosts.conf=============
<VirtualHost *:80>
DocumentRoot "E:/ST/project/ABMP/trunk/public/"
ServerName abmp.tnt.com.loc
</VirtualHost>

=========目录结构==========

htmls                               静态页面目录（设计稿→高保真页面，由 重构工程师 提供）

resource                            前端资源目录
    css
        - style.css                 项目样式文件（由 重构工程师 提供）
        - style.fix.css             项目样式补充文件（由 前端工程师 在项目开发中使用）
    images
    js
        - jquery.js                 jquery库
        - ST.Config.js              项目配置文件
    jsData                          本地数据（用于在页面中直接引用的本地数据）
        - xxx.js

modules                             前端模块目录
    lib                             基础库
        - seajs
        - jquery
        - underscore
        - backbone
        - moment
        - ST
    lang                            工具包目录
        - lres                      语言包
        - regs                      验证规则
        - cfg                       装饰方法
        - interface                 程序接口封装（仅程序内嵌页面使用）
    action                          本地数据接口
        - xxx.json
    base                            基础类目录
        - base.js                   基础类
    coms                            backbone组件
    ui                              beeui插件 （jq插件，将逐渐替换成coms里的backbone组件）
    model                           模型/集合
    view                            视图
        - mod1                      模块1
            - page1.js              页面1视图
            - page                  页面1子视图
                - xxx.js
    tpl                             html模板
        - mod1                      模块1
            - page1                 页面1子视图模板
                - xxx.html
            - page1.html            页面1视图模板
    app.js                          单页应用入口文件（非单页应用可忽视此文件）


