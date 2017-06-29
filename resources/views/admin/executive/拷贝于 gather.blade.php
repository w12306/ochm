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
	<div class="container">
				<div class="heading clearfix">
					<div class="pull-left">
						<h2>排期管理>大排期</h2>
					</div>
				</div>
	 <table class="table table-listing mt20">
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

                    <tr>
                        <td class="span4">月份：</td>
                        <td>
                            <input type="text" class="js-month" value="<?php echo date('Y-m',time());?>" onFocus="WdatePicker({dateFmt:'yyyy-MM',isShowClear:false,readOnly:true})">
                        </td>
                    </tr>                
                </tbody>
            </table>

            <a title="获取大排期" href="javascript:;" class="btn mr5 mt20 js-get-schedule">获取大排期</a>  
			<div class="s-menu-wrap mt20">
                <ul class="f-right">
                    <li>
                        <span>购买</span> <span class="color-red"></span>
                    </li>

                    <li>
                        <span>配送</span> <span class="color-green"></span>
                    </li>

                    <li>
                        <span>框架</span> <span class="color-purple"></span>
                    </li>

                    <li>
                        <span>额外支持</span> <span class="color-blue"></span>
                    </li>

                </ul>
            </div>

            <div id="js-container" class="mt20"></div>
	</div>
 

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

 <script>
     seajs.use('view/execution/schedule', function (app) {
        app({
            el: '#js-container'
        });
    })
</script>

@endsection