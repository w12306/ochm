{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

@endsection
	
{{-- 内容 --}}
@section('content')
	<script>
        $.extend(ST.ACTION,{
            scheduleDetail:"{{ route('admin.executive.api.get-gather')}}", //获取执行单数据
            adToop:"{{ route('admin.executive.api.get-gather-toop')}}" //获取执行单数据
        });
    </script>
	<link  rel="stylesheet" media="screen" href="/modules/lib/handsontable/handsontable.css">
    <script  src="/modules/lib/handsontable/pikaday/pikaday.js"></script>
    <script  src="/modules/lib/handsontable/moment/moment.js"></script>
    <script  src="/modules/lib/handsontable/zeroclipboard/ZeroClipboard.js"></script>
    <script  src="/modules/lib/handsontable/handsontable.js"></script>
    <script  src="/modules/lib/WdatePicker/WdatePicker.js"></script>
	<script>
	function checkselect(obj){
		if(!obj.checked){
			document.getElementById('all_ad_type').checked=false;
			return ;
		}
	}
	function selectall(obj){
		if(obj.checked){
			$("input[name='ad_type[]']").each(function(i){
				this.checked=true;
		    });
		}else{
			$("input[name='ad_type[]']").each(function(i){
				this.checked=false;
		    });
		}
		
	}
	</script>	
            <div class="s-menu-wrap ">
                <div class="s-menu-container">
                    <div class="pull-left" style="margin-right:30px;">
                        <h2>大排期</h2>
                    </div>
                    <div class="month-search">
                        <span>月份</span>
                        <input type="text" class="js-month" onFocus="WdatePicker({dateFmt:'yyyy-MM',isShowClear:false,readOnly:true})" value="{{$month}}">
                    </div>
                    <a title="获取大排期" href="javascript:;" class="btn mr5 js-get-schedule">获取大排期</a>
                    <a title="高级筛选" href="javascript:;" class="js-filter">高级筛选</a>
                    <ul class="f-right" style="margin-right: 60px;">
                        <li>
                            <span class="gather-color-red">购买</span>
                        </li>

                        <li>
                            <span class="gather-color-green">配送</span>
                        </li>

                        <li>
                            <span class="gather-color-purple">框架</span>
                        </li>

                        <li>
                            <span class="gather-color-blue">额外支持</span>
                        </li>
						<li>
							<span  style="margin-left:1px; ">
							
                            <input type="text" class="js-month" name="e_btime" id="e_btime" style="width:90px" onFocus="WdatePicker({dateFmt:'yyyy-MM-dd',isShowClear:false,readOnly:true})">
							</span>
                        </li>
						<!--<li>
							<span  style="margin-left:20px;">
                            <input type="text" class="js-month" name="e_etime" id="e_etime" style="width:90px" onFocus="WdatePicker({dateFmt:'yyyy-MM-dd',isShowClear:false,readOnly:true})">
							</span>
                        </li>-->

                        <li style="width:130px;">
                            <a title="导出排期明细数据" href="javascript:;" class="btn mr5" style="float: right;margin-right: -48px;"  onclick="to_excel('{{ route('admin.executive.gather-excel') }}')"  id="export-btn">导出排期明细数据</a>
                        </li>
                    </ul>
                </div>
            </div>

            <table class="table table-listing js-filter-container" style="display:none;">
                <tbody>
                    <tr>
                        <td class="span4">广告位：</td>
                        <td>
                            
							<label class="pr10">
                                <input type="checkbox" name="all_ad_type" id="all_ad_type" class="js-ad-type" value="0" opt="nrq" onclick="selectall(this)">全部
                            </label>

							@foreach ($advertis as $d)
                            <label class="pr10">
                                <input type="checkbox" name="ad_type[]" class="js-ad-type" value="{{$d->id}}" opt="nrq" onclick="checkselect(this)">{{$d->name}}
                            </label>
							@endforeach

                        </td>
                    </tr>
                    <tr>
                        <td class="span4">执行状态：</td>
                        <td>
                           <label class="pr10">
                                <input type="radio" name="status" class="js-status" value="0" opt="nrq"  checked="checked">全部
                            </label>
                            <label class="pr10">
                                <input type="radio" name="status" class="js-status" value="2" opt="nrq">占坑
                            </label>

                            <label class="pr10">
                                <input type="radio" name="status" class="js-status" value="3" opt="nrq">下单
                            </label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="js-container" class="mt20">

            </div>


@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

 <script>
     seajs.use('view/execution/schedule', function (app) {
        app({
            el: '#js-container'
        });
    });
	
	function to_excel(url){
		var btime=$("#e_btime").val();
		if(btime=="" ){
			alert("请选择一个导出日期");
			return false;
		}
		window.open ( url+'?&btime='+btime , "_blank" ) ;
	}
</script>

@endsection