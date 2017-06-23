<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    ST.ACTION = {
        //上传文件地址
        UPLOAD        : '/upload-file',
        //获取投放厂商
        VENDOR        : "/authed/common-api/business/factory-select",
        //获取对应游戏
        GAME          : "/authed/common-api/business/game-select",
        //获取所属业务
        BUSINESS      : "/authed/common-api/business/order-select",
        //获取地区
        AREA          : "/authed/common-api/area",
        //获取广告模板
        ADTEMPLATE    : "/authed/ad/api/get-usable-ad-template-select",
        //获取广告定向位置
        ADSPACE       : "/authed/ad/api/get-grouped-third-ad-spaces",
        MATERIALDELETE: "/authed/ad/material/delete",
        ADDELETE:"/authed/ad/delete",
        ADPUBLISH:"/authed/ad/sheet/publish",
        MATERIALCOPY:"/authed/ad/material/copy",
        ADPUBDETAIL:"/authed/ad/api/get-published-info",
        MATERIALPUBDETAIL:"/authed/ad/material/api/get-published-info",
        ADSPACEBUESINESS:"/authed/ad/ad-space-business-json",

    };
</script>