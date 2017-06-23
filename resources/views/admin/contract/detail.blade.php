{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <link rel="stylesheet" type="text/css" href="/resource/css/uploadify.css">

    <script>
        $.extend(ST.ACTION, {
            //contractDeatil 获取详情
            //editContract 表单提交
            contractUpload: "{{ route('admin.upload.file') }}", //上传接口
        });
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
                <h2>合同管理>合同内容详细</h2>
        </div>
    </div>

    <div id="js-container">
	 <table class="table table-listing">
        <tbody>
            <tr>
               <td class="span4">合同类型：</td>
               <td>{{$data->type_text}}</td>
            </tr>
			<tr>
               <td class="span4">签约时间：</td>
               <td>{{$data->signtime}}</td>
            </tr> 
			<tr>
               <td class="span4">所属框架合同：</td>
               <td>{{$parent}}</td>
            </tr> 
			<tr>
               <td class="span4">业务编号：</td>
               <td>
			  		 @if ($data->businesses->count() == 0)
						 --
	 				 @else
            			@foreach ($data->businesses as $d)	
						<a href="{{ route('admin.business.business-detail' , ['id' => $d->id]) }}">{{$d->business_key}}</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						@endforeach
					@endif	
			   </td>
            </tr> 
			<tr>
               <td class="span4">合同编号：</td>
               <td>{{$data->ckey}}</td>
            </tr> 
			<tr>
               <td class="span4">合同有效期：</td>
               <td>{{$data->btime}}-{{$data->etime}}</td>
            </tr> 
			<tr>
               <td class="span4">合同金额：</td>
               <td>{{$data->amount}}</td>
            </tr>  
			<tr>
               <td class="span4" style=" font-weight:" >电子版合同附件：</td>
               <td>
			   @if ($data->contractfiles->count() == 0)
						--
	 			@else
            		@foreach ($data->contractfiles as $d)	
						<a href="{{ route('admin.contract.download', ['id' => $d->id]) }}" target="_blank">{{$d->filepath}}(点击下载)</a> <br>
					@endforeach
				@endif	
			   
			   </td>
            </tr>    
			<tr>
               <td class="span4">合同补充：</td>
               <td>{{$data->remark}}</td>
            </tr> 
			<tr>
               <td class="span4">合同状态：</td>
               <td>{{$data->status_text}}</td>
            </tr>   
			<tr>
               <td  style="text-align:center" colspan="2"><a class="btn btn-blue js-bus-check" href="{{ route('admin.contract.edit', ['id' => $data->id]) }}" data-type="1">编辑</a></td>
            </tr>      
		</tbody>
    </table>
	
	</div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        seajs.use('view/contract/add', function (app) {
            app({
                el: '#js-container'
            });
        });
    </script>

@endsection