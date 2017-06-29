{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            <h2>新建广告</h2>
        </div>
    </div>

    <form id="addForm" action="{{ route('admin.ad.store') }}" onsubmit="return false" stverify="true" erroappend="true" ajaxpost="true" SubFun="_SubFun" errorfun= '_errorFun'>
        <table class="table table-listing table-hovered">
            <tbody>
            <tr>
                <td class="span4"><em class="text-red">* </em>广告位类型</td>
                <td>
                    @foreach ($secondAdSpaceBusiness as $adSpaceBusiness)
                        <label>
                            <input type="radio" name="ad_space_type_id" opt="nrq" value="{{ $adSpaceBusiness->id }}">
                            <span class="pr10">{{ $adSpaceBusiness->name }}</span>
                        </label>
                    @endforeach
                </td>
            </tr>

            <tr>
                <td class="span4"><em class="text-red">* </em>广告位名称</td>
                <td id="ad_space_con">
                </td>
            </tr>

            <tr>
                <td class="span4"><em class="text-red">* </em>区域定向投放位置</td>
                <td>
                    <label>
                        <input type="radio" name="target_type" value="1" opt="nrq"><span class="pr10">全国</span>
                    </label>

                    <label>
                        <input type="radio" name="target_type" value="2" opt="nrq"><span class="pr10">指定地域</span>
                    </label>

                    <label>
                        <input type="radio" name="target_type" value="3" opt="radio"><span class="pr10">指定GID</span>
                    </label>
                    <br>

                    <div id="_cityselect" style="margin-top:10px;display:block"></div>
                    <input id="_pvs" name="prov" type="hidden" value=""/> <!--存放省份选择值-->
                    <input id="_city" name="area_string" type="hidden" value=""/> <!--存放城市选择值-->

                    <textarea class="fn_postcond_val input-largest hidden" name="gid_string" id="netbar" rows="5" onkeyup="ST.todo('splitBycomma',this)" onpaste="ST.todo('splitBycomma',this,true)"></textarea>
                </td>
            </tr>

            <tr>
                <td class="span4"><em class="text-red">* </em>投放时间</td>
                <td>
					<span>
						<input class="input input-small" type="text" id="_calender1" name="start_time" opt="rq" placeholder="请输入开始时间" value="" readonly="readonly">
					</span>&nbsp;-&nbsp;
					<span>
						<input class="input input-small" type="text" id="_calender2" name="end_time" opt="rq" placeholder="请输入结束时间" value="" readonly="readonly">
					</span>
                </td>
            </tr>

            <tr id="trTmp">
                <td class="span4">
                    <em class="text-red">* </em>物料模版
                </td>
                <td>
                    <div class="dropdown" id="_ddlist_5">
                        <a class="dropdown-toggle" href="javascript:;">
                            <span class="dropdown-label" id="_ddlist_5_lab">请选择</span>
                            <span class="dropdown-arrow"><i class="icon i-chevron-down"></i></span>
                        </a>
                    </div>
                    <input type="hidden" id="_ddlist_5_val" opt="rq" name="ad_template_id" value="">
                </td>
            </tr>

            <tr>
                <td class="span4">广告名称</td>
                <td>
                    <input type="text" name="name" class="input"/>
                </td>
            </tr>

            <tr>
                <td class="span4"></td>
                <td>
                    <input class="btn btn-blue" type="submit" value="创建">
                    <a class="btn" href="{{ route('admin.ad.list.all-list') }}">取消</a>
                </td>
            </tr>
            </tbody>
        </table>
    </form>

    <div class="pop-con hidden" id="ad-pop-con">
        <div class="notice"> 
            <span class="notice-inner"> 
                <span class="progress"><i data-w="0"></i> 正在进行冲突判断，请耐心等待.... </span> 
            </span>
        </div>
    </div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        var progressConfig={
            timer:null,
            step:0,
            fn:null,
            min:2,
            max:25
        };
        $.extend(ST, {
            //页面初始化方法写在此处
            pageInit: function () {
                var _ddlist;
                //时间选择
                var this_year = new Date().getFullYear();

                var cal1         = new ST.Calender("_calender1", "", this_year - 1, this_year + 5);
                cal1.onselected  = function (c) {
                };
                cal1.timePicker  = true;
                cal1.showSeconds = true;

                var cal2         = new ST.Calender("_calender2", "", this_year - 1, this_year + 5);
                cal2.onselected  = function (c) {
                };
                cal2.timePicker  = true;
                cal2.showSeconds = true;
                cal2.curHour = 23, cal2.curMinute = 59, cal2.curSecond = 59;

                //广告位，物料模板，广告位定向联动
                $("input[name='ad_space_type_id']").click(function () {
                    var val = this.value;
                    $("#ad_space_con").html("");
                    //物料模板选择
                    ST.getJSON(ST.ACTION.ADTEMPLATE + "/" + val, '', function (j) {
                        if (! j || ! j.data) {
                            return;
                        }
                        var selectData = j.data;
                        if (! _ddlist) {
                            _ddlist = ST.ddList('_ddlist_5', selectData, function (o) {
                            });
                            _ddlist.selByValue($('#_ddlist_5_val').val());
                        }
                        else {
                            //添加请选择项
                            if (selectData[0].value != "") {
                                selectData.unshift({
                                    "value": "",
                                    "text" : "\u8bf7\u9009\u62e9"
                                })
                            }
                            _ddlist.changeData(selectData);
                            _ddlist.selByValue(0);
                        }
                    }, '', 'GET');


                    //广告定向位置
                    ST.getJSON(ST.ACTION.ADSPACE + "/" + val, '', function (j) {
                        if (! j || ! j.data) {
                            return;
                        }
                        ST.JTE.fetch('ad_space_list').toFill('ad_space_con', { data: j.data });
                    }, '', 'GET');
                });

                //区域定向
                $("input[name='target_type']").click(function () {
                    var value = $("input[name='target_type']:checked").val();
                    if (value == 2) {
                        $("#netbar").hide();
                        $("#_cityselect").show();
                        //获取服务端数据
                        ST.getJSON(ST.ACTION.AREA, '', function (j) {
                            if (! j || ! j.data) return;
                            var CityData = j.data;
                            /*获得已选值*/
                            var $cityIpt = $('#_city'), $pvsIpt = $('#_pvs'),
                                pvs      = $pvsIpt.val(), cities = $cityIpt.val();
                            var selector = new ST.CityDataSelector({
                                dataSource: CityData,
                                idkey     : 'id'
                            });
                            pvs          = pvs ? pvs.split(',') : [];
                            cities       = cities ? cities.split(',') : [];
                            $.each(pvs, function (i, v) {
                                var clist = selector.getClistByPid(v);
                                clist && $.each(clist, function (j, vv) {
                                    cities.push(vv[selector.idkey]);
                                });
                            });
                            /*初始化插件*/
                            var cityselect = new ST.CitySelect('_cityselect', {
                                dataSource: CityData,
                                selected  : cities
                            });
                            /*定义接口*/
                            cityselect.onChange = function () {
                                var that  = this, cityValue;
                                cityValue = that.getSelected();
                                /*将省市值输出到相应的INPUT*/
                                $cityIpt.val(cityValue.join(','));
                            };
                        }, '', 'GET');
                    } else if (value == 3) {
                        $("#_cityselect").hide();
                        $("#netbar").show();
                    } else if (value == 1) {
                        $("#_cityselect").hide();
                        $("#netbar").hide();
                    }
                });
            },
            _errorFun: function (e) {
                if(e.info){
                    ST.tipMsg(e.info);
                }else{
                    ST.tipMsg("提交失败，请重试!");
                }
            },
            _SubFun:function(){
                var tips = $("#ad-pop-con");
                tips.find(".progress i").css("width","0");
                tips.find(".progress i").attr("data-w","0");
                progressConfig.fn=null;
                progressConfig.step=0;
                tips.show();

                progressConfig.step=progressConfig.min;
                progressConfig.timer = setInterval(function(){
                    var obj = tips.find(".progress i"),w;
                    w=obj.attr("data-w");
                    w=parseInt(w)+progressConfig.step;
                    if(progressConfig.step==progressConfig.min&&w<=85){
                        obj.css("width",w+"%");
                        obj.attr("data-w",w);
                    }
                    if(progressConfig.step==progressConfig.max){
                        if(w<100){
                            obj.css("width",w+"%");
                        }else{
                            obj.css("width","100%");
                            clearInterval(progressConfig.timer);
                            tips.hide();
                            $(".pop-mask").hide();
                            if(progressConfig.fn){
                                progressConfig.fn();
                            }else{
                                location.href = progressConfig.url;
                            }
                        }
                        obj.attr("data-w",w);
                    }
                },500);

                ST.postForm({
                    f: document.getElementById("addForm"),
                    succ: function (j) {
                        ST._afterPostForm(j);
                    },
                    error: function (e) {
                        ST._afterPostForm(e);
                    }
                });
                return false;
            },
            _afterPostForm:function(j){
                progressConfig.step=progressConfig.max;

                if(j.status=="success"){
                    progressConfig.url=j.data.url;
                }else{
                    if(j.data.choices&&j.data.choices.length>0){
                        progressConfig.fn=function(){
                            ST.msgbox({
                                title:'',
                                content:"<span style='display:inline-block;font-size:14px;padding:5px'>"+j.info+"</span>"
                            },[{text:j.data.choices[0].info,fun:function(a,e){
                                window.location.href=j.data.choices[0].url;
                            }},{text:'修改当前广告',function(){}}],500,180);
                        }
                    }else{
                        progressConfig.fn=function(){
                            ST.tipMsg(j.info)
                        }
                    }
                    
                }

            }
        });
    </script>

@endsection