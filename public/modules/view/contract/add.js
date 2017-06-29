define(function (require) {
    var Base = require("base");
    var tpl = require("tpl/contract/add.html");
    var upItemTpl = require("tpl/upload/uploadItem.html");
    var M = require("model/contract/add");

    var com = require("lang/common")
    require("ui/selectbox");
    require("ui/combobox");
    require("ui/datepicker");
    require("ui/validator");
    require("ui/dialog");
    require("ui/notice");

    require("upload/jquery.uploadify.min");

    var View = Base.View.extend({
        template: tpl,
        initialize: function (pars) {
            var t = this;
            t.model = new M(pars.model);
            t.model.on("sync",function(){
                t.render();
                t.afterRender();
            });
        },
        render:function(){
            var t=this;
            var data = t.model.toJSON();
            t.$el.show().html(_.template(t.template, {data:data.data}));
        },
        afterRender: function () {
            var fileSize = 0;

            var len = $(".file_uplodad_txt").children().length ;

            if( fileSize  != len ){
                fileSize  = len
            }

            $('#file_upload').uploadify({
                    'fileSizeLimit':'2MB',
                    'fileTypeDesc':"*.xls;*.xlsx;*.doc;*.docx;*.txt;*.pdf;*.jpg;*.rar;*.zip",
                    'fileTypeExts':"*.xls;*.xlsx;*.doc;*.docx;*.txt;*.pdf;*.jpg;*.rar;*.zip",
                    'buttonClass' : 'my-upload-class',
                    'buttonText' : '上传附件',
                    'queueSizeLimit' : 5,
                    'simUploadLimit':5,
                    'multi':true,
                    'swf'      : '/modules/upload/uploadify.swf',
                    'uploader' : ST.ACTION.contractUpload,
                    'formData':{'XSRF-TOKEN': document.cookie.split("=")[1]},
                    "overrideEvents":['onSelectError','onDialogClose'],
                    onInit:function(){
                        if (fileSize == 5) {
                            setTimeout(function(){
                                $("#file_upload").uploadify("disable", true);
                            },100)
                        }
                    },
                    onSelectError:function(file, errorCode, errorMsg){
                        if(errorCode == -100){
                            $.notice({ content: "文件队列最大只能上传5个" })
                        }
                    },
                    onDialogClose:function(filesSelected){

                        if((filesSelected.queueLength+fileSize)>5){
                            $('#file_upload').uploadify('cancel', '*')
                            $.notice({ content: "你还能上传"+(5-fileSize)+"个文件，请重新上传！" })

                        }

                    },
                    onUploadSuccess : function(file,data,response) {
                        data=JSON.parse(data);
                        if(data.status=="success"&&data.data.uploaded){
                            fileSize ++;

                            if(fileSize<=5){

                                $(".file_uplodad_txt").append(_.template(upItemTpl, {
                                    name:data.data.uploaded,
                                    path:data.data.url
                                }));

                            }

                            if(fileSize == 5){
                                $("#file_upload").uploadify("disable", true);
                            }

                        }else{
                            $.notice({ content: data.info });
                        }
                    }
                });


            $("body").on("off",".js-del-upItem")
            $("body").on("click",".js-del-upItem",function(e){

                var teamTpl = require("tpl/dialog/affirmDialog.html");
                var d = $.dialog("delItem", {
                    title: "请确认操作：",
                    content: _.template(teamTpl, {
                        content: "将要删除文件!"
                    })
                });
                d.open(true);
                $(".js-close").click(function() {
                    $.dialog.destroy("delItem");
                });
                $(".js-submit").click(function() {
                    $.dialog.destroy("delItem");
                        var ele = $(e.currentTarget);
                        var fileId = ele.data("id");
                        var parent = ele.parents(".uploadify-queue");
                        var contractid = $("input[name=id]").val();
                        if( fileId ){
                            $.ajax({
                                url: ST.ACTION.delFile,
                                type: "post",
                                dataType: "json",
                                data: {
                                    contractid:contractid,
                                    fileid:fileId
                                },
                            }).done(function(res) {
                                if(res.status == "error"){
                                    $.notice({ content: res.info });
                                }else{
                                    fileSize--;
                                    if( fileSize <= 5){
                                        $("#file_upload").uploadify("disable", false);
                                    }
                                    parent.remove();
                                }
                            }).fail(function(res) {
                                $.notice({ content: "500，服务器在休息。" });
                            })
                        }else{
                            fileSize--;
                            if( fileSize <= 5){
                                $("#file_upload").uploadify("disable", false);
                            }
                            parent.remove();
                        }

                })


            })

            checkContractShow();
            com.com();
            $("input[data-group='contract_type']").on("click",checkContractShow);

            /*合同类型切换*/
            function checkContractShow(){
                var val = $("input[data-group='contract_type']:checked").val();
                if(val==1){
                    $("[data-name='framework_contract_ckey']").hide();
                    $("#framework_contract_ckey").attr("data-rules","");
                    $("[data-name='business_keys']").hide();
                    $("#business_keys").attr("data-rules","");
                }
                if(val==2){
                    $("[data-name='framework_contract_ckey']").show();
                    $("#framework_contract_ckey").attr("data-rules","required");
                    $("[data-name='business_keys']").show();
                    $("#business_keys").attr("data-rules","required");
                }
                if(val==3){
                    $("[data-name='framework_contract_ckey']").hide();
                    $("#framework_contract_ckey").attr("data-rules","");
                    $("[data-name='business_keys']").show();
                    $("#business_keys").attr("data-rules","");
                }
                com.formValidate();
            }
        }
    });

    return function (options) {
        return new View(options);
    }
});
